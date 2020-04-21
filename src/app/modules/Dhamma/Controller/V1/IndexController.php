<?php
namespace Dhamma\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Core\Helper\Utils as Helper;
use Dhamma\Model\Dhamma as DhammaModel;
use Dhamma\Transformer\Dhamma as DhammaTransformer;

/**
 * Dhamma api.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @RoutePrefix("/v1/dhammas")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 50;

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
            'title',
            'author'
        ];
        $page = (int) $this->request->getQuery('page', null, 1);
        $orderBy = (string) $this->request->getQuery('orderby', null, 'id');
        $orderType = (string) $this->request->getQuery('ordertype', null, 'desc');
        $keyword = (string) $this->request->getQuery('keyword', null, '');

        // optional Filter
        $status = (int) $this->request->getQuery('status', null, 0);

        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => $keyword,
            'searchKeywordIn' => $searchKeywordInData,
            'filterBy' => [
                'status' => $status
            ]
        ];
        $formData['orderBy'] = $orderBy;
        $formData['orderType'] = $orderType;

        $myDhammas = DhammaModel::paginate($formData, $this->recordPerPage, $page);

        if ($myDhammas->total_pages > 0) {
            if ($page == $myDhammas->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $myDhammas->items,
                new DhammaTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $myDhammas->total_items,
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
     * Create
     *
     * @Route("/", methods={"POST"})
     */
    public function createAction()
    {
        $formData = (array) $this->request->getPost();

        $myDhamma = new DhammaModel();
        $myDhamma->title = (string) $formData['title'];
        $myDhamma->author = (string) $formData['author'];
        $myDhamma->seokeyword = (string) $formData['seokeyword'];
        $myDhamma->seodescription = (string) $formData['seodescription'];
        $myDhamma->status = (int) $formData['status'];

        if (!$myDhamma->create()) {
            throw new UserException(ErrorCode::DATA_CREATE_FAIL);
        }

        return $this->createItem(
            $myDhamma,
            new DhammaTransformer,
            'response'
        );
    }

    /**
     * Update single field
     *
     * @Route("/{id:[0-9]+}/field", methods={"PUT"})
     */
    public function updatefieldAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myDhamma = DhammaModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myDhamma) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myDhamma->{$formData['field']} = $formData['value'];

        if (!$myDhamma->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        return $this->createItem(
            $myDhamma,
            new DhammaTransformer,
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
                        $myDhamma = DhammaModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ])->delete();
                        // If fail stop a transaction
                        if ($myDhamma == false) {
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
                        $myDhamma = DhammaModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myDhamma->status = DhammaModel::STATUS_ENABLE;

                        if (!$myDhamma->update()) {
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
                        $myDhamma = DhammaModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myDhamma->status = DhammaModel::STATUS_DISABLE;

                        if (!$myDhamma->update()) {
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
     * Update single
     *
     * @Route("/{id:[0-9]+}", methods={"PUT"})
     */
    public function updateAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myDhamma = DhammaModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myDhamma) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myDhamma->title = (string) $formData['title'];
        $myDhamma->author = (string) $formData['author'];
        $myDhamma->seokeyword = (string) $formData['seokeyword'];
        $myDhamma->seodescription = (string) $formData['seodescription'];
        $myDhamma->status = (int) $formData['status'];

        if (!$myDhamma->update()) {
            throw new UserException(ErrorCode::USER_UPDATE_FAIL);
        }

        return $this->createItem(
            $myDhamma,
            new DhammaTransformer,
            'response'
        );
    }

    /**
     * Get single
     *
     * @Route("/{id:[0-9]+}", methods={"GET"})
     */
    public function getAction(int $id = 0)
    {
        $myDhamma = DhammaModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myDhamma) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->createItem(
            $myDhamma,
            new DhammaTransformer,
            'response'
        );
    }

    /**
     * Return select source support for create/edit/index filter page form
     *
     * @Route("/formsource", methods={"GET"})
     */
    public function formsourceAction()
    {
        return $this->respondWithArray([
            'statusList' => DhammaModel::getStatusList()
        ], 'records');
    }
}
