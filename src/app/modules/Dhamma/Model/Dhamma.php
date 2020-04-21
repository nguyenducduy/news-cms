<?php
namespace Dhamma\Model;

use Engine\Db\AbstractModel;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Engine\Behavior\Model\Fileable;
use Core\Helper\Utils as Helper;

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
 * @Source('fly_dhamma');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class Dhamma extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="d_id")
    */
    public $id;

    /**
    * @Column(type="string", nullable=true, column="d_title")
    */
    public $title;

    /**
    * @Column(type="string", nullable=true, column="d_author")
    */
    public $author;

    /**
    * @Column(type="string", nullable=true, column="d_file_path")
    */
    public $filepath;

    /**
    * @Column(type="string", nullable=true, column="d_seo_keyword")
    */
    public $seokeyword;

    /**
    * @Column(type="string", nullable=true, column="d_seo_description")
    */
    public $seodescription;

    /**
    * @Column(type="integer", nullable=true, column="d_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="d_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="d_date_modified")
    */
    public $datemodified;

    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 3;

    /**
     * Initialize model
     */
    public function initialize()
    {
        $config = $this->getDI()->get('config');

        if (!$this->getDI()->get('app')->isConsole()) {
            $configBehavior = [
                'field' => 'filepath',
                'uploadPath' => $config->default->dhammas->directory,
                'allowedFormats' => $config->default->dhammas->mimes->toArray(),
                'allowedMaximumSize' => $config->default->dhammas->maxsize,
                'allowedMinimumSize' => $config->default->dhammas->minsize,
                'isOverwrite' => $config->default->dhammas->isoverwrite
            ];

            $this->addBehavior(new Fileable([
                'beforeCreate' => $configBehavior,
                'beforeDelete' => $configBehavior,
                'beforeUpdate' => $configBehavior
            ]));
        }
    }

    /**
     * Form field validation
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add('title', new PresenceOf([
            'message' => 'message-title-notempty'
        ]));

        $validator->add('author', new PresenceOf([
            'message' => 'message-author-notempty'
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

    public function getFilePath()
    {
        $config = $this->getDI()->get('config');
        $url = $this->getDI()->get('url');

        return Helper::getFileUrl(
            $url->getBaseUri(),
            $config->default->dhammas->directory,
            $this->filepath
        );
    }
}
