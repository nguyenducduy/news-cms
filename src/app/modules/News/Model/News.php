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
 * @Source('fly_news');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class News extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="n_id")
    */
    public $id;

    /**
    * @Column(type="integer", nullable=true, column="n_cid")
    */
    public $cid;

    /**
    * @Column(type="string", nullable=true, column="n_title")
    */
    public $title;

    /**
    * @Column(type="string", nullable=true, column="n_description")
    */
    public $description;

    /**
    * @Column(type="string", nullable=true, column="n_content")
    */
    public $content;

    /**
    * @Column(type="string", nullable=true, column="n_keywords")
    */
    public $keywords;

    /**
    * @Column(type="string", nullable=true, column="n_link")
    */
    public $link;

    /**
    * @Column(type="string", nullable=true, column="n_source")
    */
    public $source;

    /**
    * @Column(type="integer", nullable=true, column="n_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="n_cur_rev")
    */
    public $currev;

    /**
    * @Column(type="integer", nullable=true, column="n_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="n_date_modified")
    */
    public $datemodified;

    /**
    * @Column(type="string", nullable=true, column="n_date_published")
    */
    public $datepublished;

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 3;
    const STATUS_DRAFT = 5;

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
        $lang = self::getStaticDi()->get('lang');

        switch ($this->status) {
            case self::STATUS_ENABLE:
                $name = $lang->_('label-status-enable');
                break;
            case self::STATUS_DISABLE:
                $name = $lang->_('label-status-disable');
                break;
            case self::STATUS_DRAFT:
                $name = $lang->_('label-status-draft');
                break;
        }

        return $name;
    }

    public static function getStatusList(): array
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-status-enable'),
                'value' => (string) self::STATUS_ENABLE
            ],
            [
                'label' => $lang->_('label-status-disable'),
                'value' => (string) self::STATUS_DISABLE
            ],
            [
                'label' => $lang->_('label-status-draft'),
                'value' => (string) self::STATUS_DRAFT
            ]
        ];
    }

    /**
     * Get label style for status
     */
    public function getStatusStyle(): string
    {
        $class = '';
        switch ($this->status) {
            case self::STATUS_ENABLE:
                $class = 'primary';
                break;
            case self::STATUS_DISABLE:
                $class = 'danger';
                break;
            case self::STATUS_DRAFT:
                $class = 'info';
                break;
        }

        return $class;
    }

    public function getCategory(): array
    {
        $category = $this->getDI()->get('config')->category->items->toArray();
        foreach ($category as $item) {
            if ($item['id'] == $this->cid) {
                return $item;
            }
        }
    }

    public static function getCategoryByName($categoryName): array
    {
        $di = self::getStaticDi();
        $category = $di->get('config')->category->items->toArray();

        foreach ($category as $item) {
            if ($item['name'] == $categoryName) {
                return $item;
            }
        }
    }

    public static function getCategoryList(): array
    {
        $output = [];

        $di = self::getStaticDi();
        $category = $di->get('config')->category->items->toArray();

        foreach ($category as $item) {
            $output[] = [
                'label' => (string) $item['name'],
                'value' => (string) $item['id']
            ];
        }

        return $output;
    }

    public static function getSourceList(): array
    {
        $output = [];
        $di = self::getStaticDi();
        $rss = $di->get('config')->rss->toArray();

        foreach (array_keys($rss) as $source) {
            $output[] = [
                'label' => (string) $source,
                'value' => (string) $source
            ];
        }

        return $output;
    }

    public function addToSphinx()
    {
        $logger = $this->getDI()->get('slack');
        $sphinxAdapter = $this->getDI()->get('sphinxNews')->get('sphinxql');
        $search = new \SphinxSearch\Search($sphinxAdapter);
        $search->setQueryMode('execute');
        $rowset =  $search->search('olli_news', function(\SphinxSearch\Db\Sql\Select $select) {
            $select->where(['id = '. (int) $this->id .'']);
        });

        $result = $rowset->toArray();

        $indexer = new \SphinxSearch\Indexer($sphinxAdapter);
        $indexer->setQueryMode('execute');

        if(count($result) > 0) {
            $indexer->delete(
                'olli_news',
                ['id = '. $this->id .'']
            );
        }

        try {
            $indexer->insert(
                'olli_news',
                [
                    'id' => (int) $this->id,
                    'nid' => (int) $this->id,
                    'cid' => (int) $this->cid,
                    'title' => (string) $this->title,
                    'description' => (string) $this->description,
                    'keywords' => (string) $this->keywords,
                    'datepublished' => (int) $this->datepublished,
                    'source' => (string) $this->source
                ]
            );
        } catch (\Exception $e) {
            $logger->error('News: ' . $this->name . ' can not update to Sphinxsearch', $e);
        }
    }
}
