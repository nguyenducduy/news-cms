<?php
namespace Song\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Song\Model\Song as SongModel;
use Song\Transformer\Song as SongTransformer;
use Core\Helper\Utils as Helper;
use SphinxSearch\Indexer;
use SphinxSearch\Db\Sql\Select;

/**
 * Song api.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @RoutePrefix("/v1/songs")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 30;
    private $allowedFormat = ['mp3', 'audio/mp3'];

    /**
     * Get all
     *
     * @Route("/", methods={"GET"})
     */
    public function listAction()
    {
        $page = (int) $this->request->getQuery('page', null, 1);
        $formData = [];
        $hasMore = true;

        // Search keyword in specified field model
        $searchKeywordInData = [
            'id',
            'name',
            'title',
            'artist',
            'nctkey',
            'album',
            'genre'
        ];
        $page = (int) $this->request->getQuery('page', null, 1);
        $orderBy = (string) $this->request->getQuery('orderby', null, 'id');
        $orderType = (string) $this->request->getQuery('ordertype', null, 'desc');
        $keyword = (string) $this->request->getQuery('keyword', null, '');

        // optional Filter
        $status = (int) $this->request->getQuery('status', null, 0);
        $downloadstatus = (int) $this->request->getQuery('downloadstatus', null, 0);

        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => $keyword,
            'searchKeywordIn' => $searchKeywordInData,
            'filterBy' => [
                'status' => $status,
                'downloadstatus' => $downloadstatus
            ]
        ];
        $formData['orderBy'] = $orderBy;
        $formData['orderType'] = $orderType;

        $mySongs = SongModel::paginate($formData, $this->recordPerPage, $page);

        if ($mySongs->total_pages > 0) {
            if ($page == $mySongs->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $mySongs->items,
                new SongTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $mySongs->total_items,
                        'orderBy' => $orderBy,
                        'orderType' => $orderType,
                        'page' => $page
                    ]
                ]
            );
        } else {
            return $this->respondWithArray([], 'records');
        }
    }

    /**
     * Return select source support for create/edit/index filter page form
     *
     * @Route("/formsource", methods={"GET"})
     */
    public function formsourceAction()
    {
        return $this->respondWithArray([
            'statusList' => SongModel::getStatusList(),
            'downloadStatusList' => SongModel::getDownloadStatusList()
        ], 'records');
    }

    /**
     * Update single field
     *
     * @Route("/{id:[0-9]+}/field", methods={"PUT"})
     */
    public function updatefieldAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();

        $mySong = SongModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$mySong) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $mySong->{$formData['field']} = $formData['value'];

        if (!$mySong->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        // Update idv3 tag
        if (in_array($formData['field'], [
            'name',
            'artist',
            'genre',
            'album',
            'track'
        ])) {
            $mySong->updateTag([
                $formData['field'] => $formData['value']
            ]);
        }

        // Update sphinx search
        $mySong->updateSphinx();

        return $this->createItem(
            $mySong,
            new SongTransformer,
            'response'
        );
    }

    /**
     * Import from files
     *
     * @return void
     *
     * @Route("/import/{type:[a-z]{1,10}}", methods={"POST"})
     */
    public function importAction($type)
    {
        $myImportModel = '\Song\Model\\' . ucfirst($type);
        $myModel = new $myImportModel();

        $recordsImported = $myModel->parse();

        return $this->respondWithArray([
            'recordsImported' => $recordsImported
        ], 'records');
    }

    /**
     * Upload from files
     *
     * @return void
     *
     * @Route("/upload", methods={"POST"})
     */
    public function uploadAction()
    {
        $countSuccess = 0;
        $error = [];
        $requestService = $this->getDI()->get('request');
        $queueService = $this->getDI()->get('queue');
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');

        if ($requestService->hasFiles(true)) {
            foreach ($requestService->getUploadedFiles() as $file) {
                $key = $file->getKey();
                $type = $file->getType();

                if (!$this->checkFileType($file)) {
                    throw new UserException(ErrorCode::FILE_UPLOAD_ERR_ALLOWED_FORMAT);
                }

                // Find namepart and extension part
                $pos = strrpos($file->getName(), '.');
                if ($pos === false) {
                    $pos = strlen($file->getName());
                }

                $namePart = Helper::slug(substr($file->getName(), 0, $pos));

                // Check overwrite
                if (isset($this->isOverwrite) && $this->isOverwrite === true) {
                    $fileName = $namePart . '.' . $file->getExtension();
                } else {
                    $fileName = $namePart . '-' . time() . '.' . $file->getExtension();
                }

                $targetUploadPath = $this->config->default->songs->directory . Helper::getCurrentDateDirName(). $fileName;

                $result = $this->file->put($targetUploadPath, file_get_contents($file->getTempName()));

                if (!$result) {
                    throw new UserException(ErrorCode::FILE_UPLOAD_ERR);
                }

                $mySong = new SongModel();

                // Set field path in model
                preg_match('/(\d{4})\/(.*)\/(\d{1,2})(.*)/', $targetUploadPath, $modelFilePath);
                $mySong->filepath = $modelFilePath[0];
                $mySong->myid = (string) Helper::unique_id();
                $mySong->status = (int) SongModel::STATUS_ENABLE;
                $mySong->downloadstatus = (int) SongModel::DOWNLOAD_STATUS_COMPLETED;
                $mySong->parseTag();
                $mySong->title = (string) ($mySong->name . ' - ' . $mySong->artist);
                $mySong->size = $file->getSize();

                if ($mySong->create()) {
                    $countSuccess++;

                    $search = new \SphinxSearch\Search($sphinxAdapter);
                    $search->setQueryMode('execute');
                    $rowset =  $search->search('olli_songs', function(\SphinxSearch\Db\Sql\Select $select) use ($mySong) {
                        $select->where(['id = '. (int) $mySong->id .'']);
                    });

                    $result = $rowset->toArray();

                    if(count($result) > 0) {
                        $mySong->updateSphinx();
                    } else {
                        $mySong->insertSphinx();
                    }
                } else {
                    $error[] = 'Error upload song: ' . $mySong->title;
                }
            }
        }

        return $this->respondWithArray([
            'recordsUploaded' => $countSuccess,
            'error' => $error
        ], 'records');
    }

    /**
     * Download song link to server
     *
     * @return void
     *
     * @Route("/downloadtoserver", methods={"POST"})
     */
    public function downloadtoserverAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $mySong = SongModel::findFirst([
            'id = :id:',
            'bind' => [
                'id' => (int) $formData['id']
            ]
        ]);

        if (!$mySong) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // Push to Beanstalk Queue
        $queue = $this->getDI()->get('queue');
        $queue->choose('song.downloadtoserver');
        $addedToQueue = $queue->put([
            [
                'songId' => $mySong->id
            ],
            [
                'priority' => 1,
                'delay' => 3,
                'ttr' => 360
            ]
        ]);

        if (!$addedToQueue) {
            throw new UserException(ErrorCode::QUEUE_PUT_FAILED);
        }

        $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_DOWNLOADING;
        $mySong->save();

        return $this->createItem(
            $mySong,
            new SongTransformer,
            'response'
        );
    }

    /**
     * Bulk action
     *
     * @Route("/bulk", methods={"POST"})
     */
    public function bulkAction()
    {
        $formData = (array) $this->request->getJsonRawBody();
        $queue = $this->getDI()->get('queue');

        if (count($formData['itemSelected']) > 0 && $formData['actionSelected'] != '') {
            switch ($formData['actionSelected']) {
                case 'delete':
                    // Start a transaction
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $mySong = SongModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ])->delete();
                        // If fail stop a transaction
                        if ($mySong == false) {
                            $this->db->rollback();
                            return;
                        }
                    }
                    // Commit a transaction
                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
                case 'enable':
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $mySong = SongModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $mySong->status = SongModel::STATUS_ENABLE;

                        if (!$mySong->update()) {
                            $this->db->rollback();
                            return;
                        }
                    }

                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
                case 'disable':
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $mySong = SongModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $mySong->status = SongModel::STATUS_DISABLE;

                        if (!$mySong->update()) {
                            $this->db->rollback();
                            return;
                        }
                    }

                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;

                case 'download':
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $mySong = SongModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_DOWNLOADING;
                        $mySong->status = SongModel::STATUS_DISABLE;

                        if (!$mySong->update()) {
                            $this->db->rollback();
                            return;
                        }

                        // Push to Beanstalk Queue
                        $queue->choose('song.downloadtoserver');
                        $addedToQueue = $queue->put([
                            [
                                'songId' => $mySong->id
                            ],
                            [
                                'priority' => 1,
                                'delay' => 10,
                                'ttr' => 3600
                            ]
                        ]);

                        if (!$addedToQueue) {
                            $this->db->rollback();
                            return;
                        }
                    }

                    if ($this->db->commit() == false) {
                        throw new UserException(ErrorCode::DATA_BULK_FAILED);
                    }

                    break;
            }
        }

        return $this->respondWithOK();
    }

    /**
     * Get single
     *
     * @Route("/{id:[0-9]+}", methods={"GET"})
     */
    public function getAction(int $id = 0)
    {
        $mySong = SongModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$mySong) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->createItem(
            $mySong,
            new SongTransformer,
            'response'
        );
    }

    /**
     * Update single
     *
     * @Route("/{id:[0-9]+}", methods={"PUT"})
     */
    public function updateAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');

        $mySong = SongModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$mySong) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $mySong->name = (string) $formData['name'];
        $mySong->title = (string) $formData['title'];
        $mySong->artist = (string) $formData['artist'];
        $mySong->genre = (string) $formData['genre'];
        $mySong->status = (int) $formData['status'];
        $mySong->downloadstatus = (int) $formData['downloadstatus'];
        $mySong->downloadlink = (string) $formData['downloadlink'];
        if (!$mySong->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        // Update idv3 tag
        $mySong->updateTag($formData);

        $search = new \SphinxSearch\Search($sphinxAdapter);
        $search->setQueryMode('execute');
        $rowset =  $search->search('olli_songs', function(\SphinxSearch\Db\Sql\Select $select) use ($mySong) {
            $select->where(['id = '. (int) $mySong->id .'']);
        });

        $result = $rowset->toArray();

        if(count($result) > 0) {
            $mySong->updateSphinx();
        } else {
            $mySong->insertSphinx();
        }

        return $this->createItem(
            $mySong,
            new SongTransformer,
            'response'
        );
    }

    /**
     * Delete
     *
     * @Route("/", methods={"DELETE"})
     */
    public function deleteAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        $mySong = SongModel::findFirst([
            'id = :id:',
            'bind' => [
                'id' => (int) $formData['id']
            ]
        ]);

        if (!$mySong) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        if (!$mySong->delete()) {
            throw new UserException(ErrorCode::DATA_DELETE_FAIL);
        }

        return $this->createItem(
            $mySong,
            new SongTransformer,
            'response'
        );
    }

    /**
     * Re Index
     *
     * @Route("/reindex", methods={"POST"})
     */
    public function reindexSphinxAction()
    {
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');
        $logger = $this->getDI()->get('slack');
        $formData = [];
        $hasMore = true;
        // Search keyword in specified field model
        $searchKeywordInData = [];

        $orderBy = 'id';
        $orderType = 'desc';
        $keyword = '';

        // optional Filter
        $status = 0;
        $downloadstatus = 0;

        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => $keyword,
            'searchKeywordIn' => $searchKeywordInData,
            'filterBy' => [
                'status' => SongModel::STATUS_ENABLE,
                'downloadstatus' => SongModel::DOWNLOAD_STATUS_COMPLETED
            ]
        ];
        $formData['orderBy'] = $orderBy;
        $formData['orderType'] = $orderType;

        $mySongs = SongModel::paginate($formData, 50, 1);

        for ($i= 1; $i <= $mySongs->total_pages; $i++) {
            $mySongs = SongModel::paginate($formData, 50, $i);

            foreach ($mySongs->items as $mySong) {
                $search = new \SphinxSearch\Search($sphinxAdapter);
                $search->setQueryMode('execute');
                $rowset =  $search->search('olli_songs', function(\SphinxSearch\Db\Sql\Select $select) use ($mySong) {
                    $select->where(['id = '. (int) $mySong->id .'']);
                });

                $result = $rowset->toArray();

                if(count($result) > 0) {
                    $mySong->updateSphinx();
                } else {
                    $mySong->insertSphinx();
                }
            }

            $logger->info('Complete re-index page: ' . $i);
        }

        return $this->respondWithOK();
    }

    private function checkFileType($file)
    {
        $pass = true;

        if (!in_array($file->getExtension(), $this->allowedFormat)) {
            $pass = false;
        }

        return $pass;
    }

    /**
     * Get all
     *
     * @Route("/test", methods={"GET"})
     */
    public function testAction()
    {
        $output = [];
        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => '',
            'searchKeywordIn' => '',
            'filterBy' => []
        ];
        $formData['orderBy'] = 'id';
        $formData['orderType'] = 'desc';
        $mySongs = SongModel::paginate($formData, 10, 1);

        foreach ($mySongs->items as $song) {
            var_dump($song);die;
        }


        die('end');
    }
}
