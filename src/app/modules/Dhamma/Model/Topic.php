<?php
namespace Dhamma\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * Dhamma Model.
 *
 * @category  OLLI Dhamma Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @setConnectionService('dbDhamma');
 * @Source('fly_topic');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class Topic extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="t_id")
    */
    public $id;

    /**
    * @Column(type="string", nullable=true, column="t_name")
    */
    public $name;

    /**
    * @Column(type="string", nullable=true, column="t_description")
    */
    public $description;

    /**
    * @Column(type="integer", nullable=true, column="t_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="t_display_order")
    */
    public $displayorder;

    /**
    * @Column(type="integer", nullable=true, column="t_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="t_date_modified")
    */
    public $datemodified;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 3;

    /**
     * Initialize model
     */
    public function initialize()
    {
    }

    /**
     * Form field validation
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add('name', new PresenceOf([
            'message' => 'message-name-notempty'
        ]));

        $validator->add('status', new PresenceOf([
            'message' => 'message-status-notempty'
        ]));

        return $this->validate($validator);
    }

    public function getStatusName(): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($this->status) {
            case self::STATUS_ENABLED:
                $name = $lang->_('label-status-enabled');
                break;
            case self::STATUS_DISABLED:
                $name = $lang->_('label-status-disabled');
                break;
        }

        return $name;
    }

    public static function getStatusList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-status-enabled'),
                'value' => (string) self::STATUS_ENABLED
            ],
            [
                'label' => $lang->_('label-status-disabled'),
                'value' => (string) self::STATUS_DISABLED
            ],
        ];
    }

    public function getStatusStyle(): string
    {
        $class = '';
        switch ($this->status) {
            case self::STATUS_ENABLED:
                $class = 'primary';
                break;
            case self::STATUS_DISABLED:
                $class = 'danger';
                break;
        }

        return $class;
    }
}
