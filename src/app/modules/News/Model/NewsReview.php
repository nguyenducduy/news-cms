<?php
namespace News\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;

/**
 * News Review Model.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @setConnectionService('dbNews');
 * @Source('fly_news_review');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class NewsReview extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="nr_id")
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
    * @Column(type="integer", nullable=true, column="nrev_num")
    */
    public $num;

    /**
    * @Column(type="string", nullable=true, column="u_name")
    */
    public $uname;

    /**
    * @Column(type="string", nullable=true, column="u_avatar")
    */
    public $uavatar;

    /**
    * @Column(type="string", nullable=true, column="nr_comment")
    */
    public $comment;

    /**
    * @Column(type="string", nullable=true, column="nr_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="nr_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="nr_date_modified")
    */
    public $datemodified;

    const STATUS_GOOD = 1;
    const STATUS_WRONG_INFO = 3;
    const STATUS_POLITICAL_STIMULUS = 5;
    const STATUS_ERROR_SPELLING = 7;
    const STATUS_COPYCAT = 9;
    const STATUS_DEBAUCHERY = 11;

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

    public function getStatusName(): string
    {
        $name = '';
        $lang = $this->getDI()->get('lang');

        switch ($this->status) {
            case self::STATUS_GOOD:
                $name = $lang->_('label-status-good');
                break;
            case self::STATUS_WRONG_INFO:
                $name = $lang->_('label-wrong-info');
                break;
            case self::STATUS_POLITICAL_STIMULUS:
                $name = $lang->_('label-political-stimus');
                break;
            case self::STATUS_ERROR_SPELLING:
                $name = $lang->_('label-error-spelling');
                break;
            case self::STATUS_COPYCAT:
                $name = $lang->_('label-copycat');
                break;
            case self::STATUS_DEBAUCHERY:
                $name = $lang->_('label-debauchery');
                break;
        }

        return $name;
    }

    public static function getStatusVal($statusValue): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($statusValue) {
            case self::STATUS_GOOD:
                $name = $lang->_('label-status-good');
                break;
            case self::STATUS_WRONG_INFO:
                $name = $lang->_('label-wrong-info');
                break;
            case self::STATUS_POLITICAL_STIMULUS:
                $name = $lang->_('label-political-stimus');
                break;
            case self::STATUS_ERROR_SPELLING:
                $name = $lang->_('label-error-spelling');
                break;
            case self::STATUS_COPYCAT:
                $name = $lang->_('label-copycat');
                break;
            case self::STATUS_DEBAUCHERY:
                $name = $lang->_('label-debauchery');
                break;
        }

        return $name;
    }

    public static function getStatusList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-status-good'),
                'value' => (string) self::STATUS_GOOD
            ],
            [
                'label' => $lang->_('label-wrong-info'),
                'value' => (string) self::STATUS_WRONG_INFO
            ],
            [
                'label' => $lang->_('label-political-stimus'),
                'value' => (string) self::STATUS_POLITICAL_STIMULUS
            ],
            [
                'label' => $lang->_('label-error-spelling'),
                'value' => (string) self::STATUS_ERROR_SPELLING
            ],
            [
                'label' => $lang->_('label-copycat'),
                'value' => (string) self::STATUS_COPYCAT
            ],
            [
                'label' => $lang->_('label-debauchery'),
                'value' => (string) self::STATUS_DEBAUCHERY
            ]
        ];
    }
}
