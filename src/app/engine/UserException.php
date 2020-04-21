<?php
namespace Engine;

use Phalcon\DI;
use Phalcon\Exception as PhException;

class UserException extends \Exception
{
    public function __construct($code = 0, $args = [], $message = "", \Exception $previous = null)
    {
        parent::__construct(vsprintf($message, $args), $code, $previous);
    }
}
