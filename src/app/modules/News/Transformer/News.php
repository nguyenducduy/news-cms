<?php
namespace News\Transformer;

use League\Fractal\TransformerAbstract;
use News\Model\News as NewsModel;
use News\Model\NewsRevision as NewsRevisionModel;
use News\Model\NewsReview as NewsReviewModel;
use News\Model\NewsHistory as NewsHistoryModel;
use Phalcon\Di;
use News\Transformer\Content as ContentTransformer;
use News\Transformer\Revision as NewsRevisionTransformer;
use News\Transformer\Review as NewsReviewTransformer;
use News\Transformer\History as NewsHistoryTransformer;

/**
 * News Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class News extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
        'content',
        'revision',
        'review',
        'history'
    ];

    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(NewsModel $news)
    {
        $humandatecreated = new \Moment\Moment($news->datecreated);
        $humandatepublished = new \Moment\Moment($news->datepublished);

        return [
            'id' => (string) $news->id,
            'title' => (string) $news->title,
            'description' => (string) $news->description,
            'keywords' => (string) $news->keywords,
            'link' => (string) $news->link,
            'source' => (string) $news->source,
            'currev' => (string) $news->currev,
            'status' =>  [
                'label' => (string) $news->getStatusName(),
                'value' => (string) $news->status,
                'style' => (string) $news->getStatusStyle()
            ],
            'category' => [
                'label' => (string) $news->getCategory()['name'],
                'value' => (string) $news->getCategory()['id']
            ],
            'datecreated' => (string) $news->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('d M Y, H:i'),
            'humandatepublished' => (string) $humandatepublished->format('d M Y, H:i')
        ];
    }

    public function includeContent(NewsModel $news) {
        return $this->item($news->content, new ContentTransformer);
    }

    public function includeRevision(NewsModel $news) {
        $myNewsRevisions = NewsRevisionModel::find([
            'nid = :nid:',
            'bind' => [
                'nid' => (int) $news->id
            ],
            'order' => 'datecreated DESC'
        ]);

        return $this->collection($myNewsRevisions, new NewsRevisionTransformer());
    }

    public function includeReview(NewsModel $news) {
        $myNewsReviews = NewsReviewModel::find([
            'nid = :nid:',
            'bind' => [
                'nid' => (int) $news->id
            ],
            'order' => 'datecreated DESC'
        ]);

        return $this->collection($myNewsReviews, new NewsReviewTransformer());
    }

    public function includeHistory(NewsModel $news) {
        $myNewsHistorys = NewsHistoryModel::find([
            'nid = :nid:',
            'bind' => [
                'nid' => (int) $news->id
            ],
            'order' => 'datecreated DESC'
        ]);

        return $this->collection($myNewsHistorys, new NewsHistoryTransformer());
    }
}
