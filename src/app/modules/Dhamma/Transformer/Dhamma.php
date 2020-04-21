<?php
namespace Dhamma\Transformer;

use League\Fractal\TransformerAbstract;
use Dhamma\Model\Dhamma as DhammaModel;
use Phalcon\Di;

/**
 * Dhamma Transformer.
 *
 * @category  OLLI Event Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Dhamma extends TransformerAbstract
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
    public function transform(DhammaModel $dhamma)
    {
        $humandatecreated = new \Moment\Moment($dhamma->datecreated);

        return [
            'id' => (string) $dhamma->id,
            'title' => (string) $dhamma->title,
            'author' => (string) $dhamma->author,
            'seokeyword' => (string) $dhamma->seokeyword,
            'seodescription' => (string) $dhamma->seodescription,
            'status' =>  [
                'label' => (string) $dhamma->getStatusName(),
                'value' => (string) $dhamma->status,
                'style' => (string) $dhamma->getStatusStyle()
            ],
            'filepath' => (string) $dhamma->getFilePath(),
            'datecreated' => (string) $dhamma->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('d M Y, H:i')
        ];
    }
}
