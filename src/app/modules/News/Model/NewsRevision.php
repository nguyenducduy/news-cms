<?php
namespace News\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * News Model.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @setConnectionService('dbNews');
 * @Source('fly_news_revision');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class NewsRevision extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="nrev_id")
    */
    public $id;

    /**
    * @Column(type="integer", nullable=true, column="n_id")
    */
    public $nid;

    /**
    * @Column(type="integer", nullable=true, column="u_id")
    */
    public $uid;

    /**
    * @Column(type="string", nullable=true, column="u_name")
    */
    public $uname;

    /**
    * @Column(type="string", nullable=true, column="u_avatar")
    */
    public $uavatar;

    /**
    * @Column(type="string", nullable=true, column="nrev_num")
    */
    public $num;

    /**
    * @Column(type="string", nullable=true, column="nrev_before")
    */
    public $before;

    /**
    * @Column(type="string", nullable=true, column="nrev_after")
    */
    public $after;

    /**
    * @Column(type="integer", nullable=true, column="nrev_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="nrev_date_modified")
    */
    public $datemodified;

    /**
     * Initialize model
     */
    public function initialize()
    {
    }

    // /**
    //  * Form field validation
    //  */
    // public function validation()
    // {
    //     $validator = new Validation();
    //
    //     return $this->validate($validator);
    // }
}
