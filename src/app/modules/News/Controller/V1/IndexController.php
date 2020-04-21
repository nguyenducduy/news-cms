<?php
namespace News\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Core\Helper\Utils as Helper;
use News\Helper\Parse as ParseHelper;
use News\Model\News as NewsModel;
use News\Model\NewsRevision as NewsRevisionModel;
use News\Model\NewsHistory as NewsHistoryModel;
use News\Model\NewsReview as NewsReviewModel;
use News\Transformer\News as NewsTransformer;

/**
 * News api.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @RoutePrefix("/v1/newss")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 30;

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
            'source'
        ];
        $page = (int) $this->request->getQuery('page', null, 1);
        $orderBy = (string) $this->request->getQuery('orderby', null, 'id');
        $orderType = (string) $this->request->getQuery('ordertype', null, 'desc');
        $keyword = (string) $this->request->getQuery('keyword', null, '');

        // optional Filter
        $status = (int) $this->request->getQuery('status', null, 0);
        $category = (int) $this->request->getQuery('category', null, 0);
        $source = (string) $this->request->getQuery('source', null, '');
        $currev = (string) $this->request->getQuery('currev', null, '');

        $formData['columns'] = '*';
        $formData['conditions'] = [
            'keyword' => $keyword,
            'searchKeywordIn' => $searchKeywordInData,
            'filterBy' => [
                'status' => $status,
                'cid' => $category,
                'source' => $source
            ]
        ];
        $formData['orderBy'] = $orderBy;
        $formData['orderType'] = $orderType;

        // Filter by current revision
        if ($currev != '' && $currev == 'true') {
            $formData['conditions']['filterBy']['!currev'] = -1; // grant -1 because 0 will be ignore (null), query will like as != 0
        }

        $myNewss = NewsModel::paginate($formData, $this->recordPerPage, $page);

        if ($myNewss->total_pages > 0) {
            if ($page == $myNewss->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $myNewss->items,
                new NewsTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $myNewss->total_items,
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
            'statusList' => NewsModel::getStatusList(),
            'reviewStatusList' => NewsReviewModel::getStatusList(),
            'categoryList' => NewsModel::getCategoryList(),
            'sourceList' => NewsModel::getSourceList()
        ], 'records');
    }

    /**
     * Bulk action
     *
     * @Route("/bulk", methods={"POST"})
     */
    public function bulkAction()
    {
        $formData = (array) $this->request->getJsonRawBody();

        if (count($formData['itemSelected']) > 0 && $formData['actionSelected'] != '') {
            switch ($formData['actionSelected']) {
                case 'delete':
                    // Start a transaction
                    $this->db->begin();
                    foreach ($formData['itemSelected'] as $item) {
                        $myNews = NewsModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ])->delete();
                        // If fail stop a transaction
                        if ($myNews == false) {
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
                        $myNews = NewsModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myNews->status = NewsModel::STATUS_ENABLE;

                        if (!$myNews->update()) {
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
                        $myNews = NewsModel::findFirst([
                            'id = :id:',
                            'bind' => ['id' => (int) $item->id]
                        ]);
                        $myNews->status = NewsModel::STATUS_DISABLE;

                        if (!$myNews->update()) {
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
        $myNews = NewsModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        return $this->createItem(
            $myNews,
            new NewsTransformer,
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
        $sphinxAdapter = $this->getDI()->get('sphinxNews')->get('sphinxql');
        $myUser = $this->getDI()->getAuth()->getUser();

        $myNews = NewsModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // Denied editor when this article has been published (affected on Editor Role)
        if ($myNews->status == NewsModel::STATUS_ENABLE && $myUser->groupid == 'editor') {
            throw new UserException(ErrorCode::DATA_NOTALLOWED);
        }

        // Old news
        $oldnews = [
            'category' => $myNews->getCategory()['name'],
            'title' => $myNews->title,
            'description' => $myNews->description,
            'keywords' => $myNews->keywords
        ];

        $myNews->cid = (int) $formData['cid'];
        $myNews->title = (string) $formData['title'];
        $myNews->description = (string) $formData['description'];
        $myNews->keywords = (string) implode(',', $formData['keywords']);
        if (!$myNews->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        // Add to news revision
        $maxNum = NewsRevisionModel::maximum([
            'column' => 'num',
            'conditions' => 'nid = :nid:',
            'bind' => [
                'nid' => (int) $myNews->id
            ]
        ]);
        $myNewsRevision = new NewsRevisionModel();
        $myNewsRevision->assign([
            'uid' => (int) $myUser->id,
            'uname' => (string) $myUser->fullname,
            'uavatar' => (string) $myUser->avatar,
            'nid' => (int) $myNews->id,
            'num' => (int) $maxNum + 1,
            'before' => (string) json_encode($oldnews, JSON_UNESCAPED_UNICODE),
            'after' => (string) json_encode([
                'category' => $myNews->getCategory()['name'],
                'title' => $myNews->title,
                'description' => $myNews->description,
                'keywords' => $myNews->keywords
            ], JSON_UNESCAPED_UNICODE)
        ]);

        if (!$myNewsRevision->create()) {
            throw new UserException(ErrorCode::DATA_CREATE_FAIL);
        }

        // Add to news history
        $myNewsHistory = new NewsHistoryModel();
        $myNewsHistory->assign([
            'uid' => (int) $myUser->id,
            'uname' => (string) $myUser->fullname,
            'uavatar' => (string) $myUser->avatar,
            'nid' => (int) $myNews->id,
            'type' => (int) NewsHistoryModel::TYPE_EDIT,
            'objectid' => (int) $myNewsRevision->id
        ]);

        if (!$myNewsHistory->create()) {
            throw new UserException(ErrorCode::DATA_CREATE_FAIL);
        }

        // Update current revision
        $myNews->currev = (int) $myNewsRevision->num;
        if (!$myNews->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        return $this->createItem(
            $myNews,
            new NewsTransformer,
            'response'
        );
    }

    /**
     * Submit a review
     *
     * @Route("/{id:[0-9]+}/review", methods={"POST"})
     */
    public function addreviewAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();
        $myUser = $this->getDI()->getAuth()->getUser();

        $myNews = NewsModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        // Check if this user has been edited this article on this revision number
        $myNewsRevision = NewsRevisionModel::findFirst([
            'uid = :uid: AND nid = :nid: AND num = :num:',
            'bind' => [
                'uid' => $myUser->id,
                'nid' => $myNews->id,
                'num' => $myNews->currev
            ]
        ]);

        if ($myNewsRevision) {
            throw new UserException(ErrorCode::DATA_NOTALLOWED);
        }

        // Check if this user has been reviewed this article on this revision number
        $myNewsReview = NewsReviewModel::findFirst([
            'uid = :uid: AND nid = :nid: AND num = :num:',
            'bind' => [
                'uid' => $myUser->id,
                'nid' => $myNews->id,
                'num' => $myNews->currev
            ]
        ]);

        if ($myNewsReview) {
            throw new UserException(ErrorCode::DATA_NOTALLOWED);
        }

        $myReview = new NewsReviewModel();
        $myReview->assign([
            'uid' => (int) $myUser->id,
            'uname' => (string) $myUser->fullname,
            'uavatar' => (string) $myUser->avatar,
            'nid' => (int) $myNews->id,
            'status' => (string) implode(',', $formData['status']),
            'comment' => (string) $formData['comment'],
            'num' => (int) $myNews->currev
        ]);

        if (!$myReview->create()) {
            throw new UserException(ErrorCode::DATA_CREATE_FAIL);
        }

        $myNewsHistory = new NewsHistoryModel();
        $myNewsHistory->assign([
            'uid' => (int) $myUser->id,
            'uname' => (string) $myUser->fullname,
            'uavatar' => (string) $myUser->avatar,
            'nid' => (int) $myNews->id,
            'type' => (int) NewsHistoryModel::TYPE_REVIEW,
            'objectid' => (int) $myReview->id
        ]);

        if (!$myNewsHistory->create()) {
            throw new UserException(ErrorCode::DATA_CREATE_FAIL);
        }

        return $this->createItem(
            $myNews,
            new NewsTransformer,
            'response'
        );
    }

    /**
     * Publish a article
     *
     * @Route("/{id:[0-9]+}/publish", methods={"POST"})
     */
    public function publishAction(int $id = 0)
    {
        $myNews = NewsModel::findFirst([
            'id = :id:',
            'bind' => ['id' => (int) $id]
        ]);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myNews->status = (int) NewsModel::STATUS_ENABLE;
        if ($myNews->update()) {
            $myNews->addToSphinx();
        }

        return $this->createItem(
            $myNews,
            new NewsTransformer,
            'response'
        );
    }

    /**
     * Use selected revision
     *
     * @Route("/{id:[0-9]+}/userev", methods={"POST"})
     */
    public function userevAction(int $id = 0)
    {
        $formData = (array) $this->request->getJsonRawBody();

        $myNewsRevision = NewsRevisionModel::findFirst([
            'id = :revisionId: AND nid = :nid:',
            'bind' => [
                'revisionId' => (int) $formData['id'],
                'nid' => (int) $id
            ]
        ]);

        if (!$myNewsRevision) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $myNews = NewsModel::findFirstById($id);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        $revisionContent = json_decode($myNewsRevision->after, JSON_UNESCAPED_UNICODE);

        $myNews->cid = (int) NewsModel::getCategoryByName($revisionContent['category'])['id'];
        $myNews->title = (string) $revisionContent['title'];
        $myNews->description = (string) $revisionContent['description'];
        $myNews->keywords = (string) $revisionContent['keywords'];
        $myNews->currev = (int) $myNewsRevision->num;

        if (!$myNews->update()) {
            throw new UserException(ErrorCode::DATA_UPDATE_FAIL);
        }

        return $this->createItem(
            $myNews,
            new NewsTransformer,
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

        $myNews = NewsModel::findFirst([
            'id = :id:',
            'bind' => [
                'id' => (int) $formData['id']
            ]
        ]);

        if (!$myNews) {
            throw new UserException(ErrorCode::DATA_NOTFOUND);
        }

        if (!$myNews->delete()) {
            throw new UserException(ErrorCode::DATA_DELETE_FAIL);
        }



        return $this->createItem(
            $myNews,
            new NewsTransformer,
            'response'
        );
    }

    /**
     * Test single rss link
     *
     * @Route("/test", methods={"GET"})
     */
    public function testAction()
    {
        var_dump(array_keys($this->config->rss->toArray()));
        var_dump($this->config->rss->toArray());
        die;
        // foreach ($this->config->rss->vnexpress->category->toArray() as $rssCat => $rssLink) {
        //     $myCat = $this->findCategory($rssCat);
        //     echo "Category: [$rssCat] " . $myCat['name'];
        //
        //     $rss = \Feed::load(
        //         $this->config->rss->vnexpress->url
        //         . $rssLink
        //     );
        //
        //     foreach ($rss->item as $item) {
        //         $context = ParseHelper::getContent('vnexpress', $item->link->__toString());
        //         $context->title = trim($item->title->__toString());
        //         $context->link = trim($item->link->__toString());
        //         $context->pubdate = trim($item->pubDate->__toString());
        //         $context->guid = trim($item->guid->__toString());
        //         $datepublished = $context->formatTime();
        //
        //         var_dump($context);
        //         // // echo $context->content;
        //         var_dump($datepublished);
        //         die;
        //     }
        // }
        //
        // die;
    }

    private function findCategory($rssCat) {
        foreach ($this->config->category->items->toArray() as $item) {
            if (in_array($rssCat, $item['slugs'])) {
                return $item;
            }
        }
    }
}
