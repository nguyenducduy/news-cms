<?php
namespace News\Transformer;

use League\Fractal\TransformerAbstract;
use Phalcon\Di;
use News\Model\NewsHistory as NewsHistoryModel;
use News\Model\NewsRevision as NewsRevisionModel;
use News\Model\NewsReview as NewsReviewModel;

/**
 * News History Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class History extends TransformerAbstract
{
    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(NewsHistoryModel $history)
    {
        $humandatecreated = new \Moment\Moment($history->datecreated);
        $di = Di::getDefault();
        $lang = $di->get('lang');

        $myObject = '';
        switch ($history->type) {
            case NewsHistoryModel::TYPE_EDIT:
                $myRevision = NewsRevisionModel::findFirstById($history->objectid);

                if ($myRevision) {
                    $myObject = $lang->_('label-on-rev') . ' ' . $myRevision->num;
                }
                break;
            case NewsHistoryModel::TYPE_REVIEW:
                $myReview = NewsReviewModel::findFirstById($history->objectid);

                if ($myReview) {
                    $reviewStatus = explode(',', $myReview->status);
                    $reviewStatusNameList = [];
                    foreach ($reviewStatus as $key => $value) {
                        $reviewStatusNameList[] = $myReview::getStatusVal($value);
                    }

                    $myObject = $lang->_('label-with-status') . ' ' . implode(', ', $reviewStatusNameList) . ' ' . $lang->_('label-for-rev') . ' ' . $myReview->num . '  ---@' . $myReview->comment;
                }
                break;
        }

        return [
            'id' => (string) $history->id,
            'uid' => (string) $history->uid,
            'uname' => (string) $history->uname,
            'uavatar' => (string) $history->uavatar,
            'nid' => (string) $history->nid,
            'type' =>  [
                'label' => (string) $history->getTypeName(),
                'value' => (string) $history->type,
                'style' => (string) $history->getTypeStyle()
            ],
            'content' => $myObject,
            'datecreated' => (string) $history->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('H:i, d M Y')
        ];
    }
}
