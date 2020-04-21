<?php
namespace Song;

use Phalcon\DI;
use Phalcon\DiInterface;
use Phalcon\Events\Manager as PhEventsManager;
use Engine\Bootstrap as EnBootstrap;

/**
 * Song Bootstrap.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Bootstrap extends EnBootstrap
{
    /**
     * Current module name.
     *
     * @var string
     */
    protected $_moduleName = 'Song';

    /**
     * Bootstrap construction.
     *
     * @param DiInterface $di Dependency injection.
     * @param Manager     $em Events manager object.
     */
    public function __construct(DI $di, PhEventsManager $em)
    {
        parent::__construct($di, $em);
    }
}
