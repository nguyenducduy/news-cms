<?php
namespace Dhamma\Transformer;

use League\Fractal\TransformerAbstract;
use Dhamma\Model\Topic as TopicModel;
use Phalcon\Di;

/**
 * Topic Transformer.
 *
 * @category  OLLI Event Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Topic extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(TopicModel $topic)
    {
        $humandatecreated = new \Moment\Moment($topic->datecreated);

        return [
            'id' => (string) $topic->id,
            'name' => (string) $topic->name,
            'description' => (string) $topic->description,
            'displayorder' => (string) $topic->displayorder,
            'status' =>  [
                'label' => (string) $topic->getStatusName(),
                'value' => (string) $topic->status,
                'style' => (string) $topic->getStatusStyle()
            ],
            'datecreated' => (string) $topic->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('d M Y, H:i')
        ];
    }
}
