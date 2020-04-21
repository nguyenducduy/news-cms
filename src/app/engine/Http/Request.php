<?php
namespace Engine\Http;

use Phalcon\Http\Request as PhRequest;

/**
 * Engine Api HTTP Request.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Request extends PhRequest
{
    protected function removeBearer($string)
    {
        return preg_replace('/.*\s/', '', $string);
    }

    public function getToken()
    {
        $authHeader = $this->getHeader('Authorization');
        $authQuery = $this->getQuery('token');

        return ($authQuery ? $authQuery : $this->removeBearer($authHeader));
    }
}
