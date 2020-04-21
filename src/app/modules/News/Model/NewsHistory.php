<?php
namespace News\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * News History Model.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @setConnectionService('dbNews');
 * @Source('fly_news_history');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class NewsHistory extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="nh_id")
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
    * @Column(type="integer", nullable=true, column="nh_type")
    */
    public $type;

    /**
    * @Column(type="integer", nullable=true, column="nh_objectid")
    */
    public $objectid;

    /**
    * @Column(type="integer", nullable=true, column="nh_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="nh_date_modified")
    */
    public $datemodified;

    const TYPE_EDIT = 1; // Objectid is revision id
    const TYPE_REVIEW = 3; // Object id is review id

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

    public function getTypeName(): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($this->type) {
            case self::TYPE_EDIT:
                $name = $lang->_('label-type-edit');
                break;
            case self::TYPE_REVIEW:
                $name = $lang->_('label-type-review');
                break;
        }

        return $name;
    }

    public static function getTypeList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-type-edit'),
                'value' => (string) self::TYPE_EDIT
            ],
            [
                'label' => $lang->_('label-type-review'),
                'value' => (string) self::TYPE_REVIEW
            ]
        ];
    }

    public function getTypeStyle(): string
    {
        $name = '';

        switch ($this->type) {
            case self::TYPE_EDIT:
                $name = 'primary';
                break;
            case self::TYPE_REVIEW:
                $name = 'warning';
                break;
        }

        return $name;
    }
}
