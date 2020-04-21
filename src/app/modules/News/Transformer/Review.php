<?php
namespace News\Transformer;

use League\Fractal\TransformerAbstract;
use News\Model\NewsReview as NewsReviewModel;
use Phalcon\Di;

/**
 * News Review Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Review extends TransformerAbstract
{
    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(NewsReviewModel $review)
    {
        $humandatecreated = new \Moment\Moment($review->datecreated);

        return [
            'id' => (string) $review->id,
            'uid' => (string) $review->uid,
            'uname' => (string) $review->uname,
            'uavatar' => (string) $review->uavatar,
            'nid' => (string) $review->nid,
            'num' => (string) $review->num,
            'datecreated' => (string) $review->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('H:i, d M Y')
        ];
    }
}
