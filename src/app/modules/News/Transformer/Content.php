<?php
namespace News\Transformer;

use League\Fractal\TransformerAbstract;
use Phalcon\Di;

/**
 * News Content Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Content extends TransformerAbstract
{
    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(string $content)
    {
        return [
            'text' => (string) $content
        ];
    }
}
