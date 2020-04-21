<?php
namespace News\Transformer;

use League\Fractal\TransformerAbstract;
use News\Model\NewsRevision as NewsRevisionModel;
use Phalcon\Di;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;

/**
 * News Revision Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Revision extends TransformerAbstract
{
    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(NewsRevisionModel $revision)
    {
        $di = Di::getDefault();
        $lang = $di->get('lang');

        $humandatecreated = new \Moment\Moment($revision->datecreated);
        $diffContent = [];

        $old = json_decode($revision->before);
        $new = json_decode($revision->after);

        // Category diff
        $builder = new UnifiedDiffOutputBuilder(
            "--- {$lang->_('label-diff-category')}\n+++ _\n"
        );
        $differ = new Differ($builder);
        $diffContent['category'] = $differ->diff($old->category, $new->category);

        // Title diff
        $builder = new UnifiedDiffOutputBuilder(
            "--- {$lang->_('label-diff-title')}\n+++ _\n"
        );
        $differ = new Differ($builder);
        $diffContent['title'] = $differ->diff($old->title, $new->title);

        // Description diff
        $builder = new UnifiedDiffOutputBuilder(
            "--- {$lang->_('label-diff-description')}\n+++ _\n"
        );
        $differ = new Differ($builder);
        $diffContent['description'] = $differ->diff($old->description, $new->description);

        // Keywords diff
        $builder = new UnifiedDiffOutputBuilder(
            "--- {$lang->_('label-diff-keywords')}\n+++ _\n"
        );
        $differ = new Differ($builder);
        $diffContent['keywords'] = $differ->diff($old->keywords, $new->keywords);

        return [
            'id' => (string) $revision->id,
            'uid' => (string) $revision->uid,
            'uname' => (string) $revision->uname,
            'uavatar' => (string) $revision->uavatar,
            'nid' => (string) $revision->nid,
            'num' => (string) $revision->num,
            'datecreated' => (string) $revision->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('H:i, d M Y'),
            'diffcontent' => $diffContent
        ];
    }
}
