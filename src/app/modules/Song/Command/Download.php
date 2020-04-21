<?php
namespace Song\Command;

use Engine\Console\AbstractCommand;
use Engine\Interfaces\CommandInterface;
use Engine\Console\ConsoleUtil;
use Song\Model\Song as SongModel;
use Core\Helper\Utils as Helper;

/**
 * Song command.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <nguyenducduy.it@gmail.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @CommandName(['download'])
 * @CommandDescription('Download command controller.')
 */
class Download extends AbstractCommand implements CommandInterface
{
    /**
     * Download song from link to server
     *
     * @return void
     */
    public function toserverAction()
    {
        $queue = $this->getDI()->get('queue');
        $logger = $this->getDI()->get('logger');
        $queue->watch('song.downloadtoserver');
        $config = $this->getDI()->get('config');
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');

        while (($job = $queue->reserve()) !== false) {
            $message = $job->getBody();
            $data = $message[0];

            $songId = (int) $data['songId'];

            $mySong = SongModel::findFirst([
                'id = :id: AND downloadstatus = :downloadstatus: AND status = :status:',
                'bind' => [
                    'id' => $songId,
                    'downloadstatus' => SongModel::DOWNLOAD_STATUS_DOWNLOADING,
                    'status' => SongModel::STATUS_DISABLE
                ]
            ]);

            if ($mySong) {
                $headers = Helper::getHeaders($mySong->downloadlink);

                if ($headers['http_code'] === 200 && (int) $headers['download_content_length'] > 0) {
                    $dirName = ROOT_PATH
                        . $config->default->songs->directory
                        . Helper::getCurrentDateDirName();

                    if (!is_dir($dirName)) {
                        //Directory does not exist, so lets create it.
                        mkdir($dirName, 0755, true);
                    }

                    $fileName = Helper::slug($mySong->name . ' ' . $mySong->artist) . '-' . $mySong->myid . '.mp3';
                    $path = $dirName . $fileName;

                    if (Helper::downloadLargeFile($mySong->downloadlink, $path)) {
                        // Set field path in model
                        preg_match('/(\d{4})\/(.*)/', $path, $modelFilePath);

                        $mySong->status = SongModel::STATUS_ENABLE;
                        $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_COMPLETED;
                        $mySong->filepath = $modelFilePath[0];
                        $mySong->size = $headers['download_content_length'];
                        $mySong->type = $headers['content_type'];

                        // get optional tag
                        $mySong->parseTag();

                        // update song to search engine
                        if ($mySong->save()) {
                            $searchEngine = new \SphinxSearch\Search($sphinxAdapter);
                            $searchEngine->setQueryMode('execute');

                            $rowset =  $searchEngine->search('olli_songs', function(\SphinxSearch\Db\Sql\Select $select) use ($mySong) {
                                $select->where(['id = '. (int) $mySong->id .'']);
                            });

                            $result = $rowset->toArray();
                            if (count($result) == 0) {
                                $indexer = new \SphinxSearch\Indexer($sphinxAdapter);
                                $indexer->setQueryMode('execute');

                                try {
                                    $indexer->insert(
                                        'olli_songs',
                                        [
                                            'id' => (int) $mySong->id,
                                            'sid' => (int) $mySong->id,
                                            'name' => (string) $mySong->name,
                                            'artist' => (string) $mySong->artist,
                                            'title' => (string) $mySong->title,
                                            'myid' => (string) $mySong->myid,
                                            'filepath' => (string) $mySong->filepath,
                                            'album' => (string) $mySong->album,
                                            'genre' => (string) $mySong->genre,
                                            'length' => (float) $mySong->length,
                                            'cbr' => (int) $mySong->cbr,
                                            'status' => (int) $mySong->status,
                                            'countlisten' => (int) $mySong->countlisten
                                        ]
                                    );
                                } catch (\Exception $e) {
                                    $logger->error('Error add song to sphinx: '. json_encode($e));
                                }
                            }
                        } else {
                            $logger->error('Update song ID: '. $mySong->id .' failed from download task.');
                            $job->bury();
                        }
                    } else {
                        $logger->error('Download song ID: ' . $mySong->id . ' error');
                        $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_ERROR;
                        $mySong->save();
                        $job->bury();
                    }
                } else {
                    $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_FETCH_FAIL;
                    $mySong->save();
                    $job->bury();
                }
            }

            $job->delete();
        }
    }

    /**
     * Test insert to sphinx
     *
     * @return void
     */
    public function testAction()
    {
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');
        $indexer = new \SphinxSearch\Indexer($sphinxAdapter);
        $indexer->setQueryMode('execute');
        $res = $indexer->insert(
            'olli_songs',
            [
                'id' => (int) 999,
                'sid' => (int) 999,
                'name' => (string) 'test',
                'artist' => (string) 'test',
                'title' => (string) 'test',
                'myid' => (string) 'test999',
                'filepath' => (string) 'test',
                'album' => (string) 'test',
                'genre' => (string) 'test',
                'length' => (float) 'test',
                'cbr' => (int) 1,
                'status' => (int) 1,
                'countlisten' => (int) 1
            ]
        );

        var_dump($res);
    }
}
