<?php
namespace Song\Model;

use Engine\Db\AbstractModel;
use Engine\Behavior\Model\Imageable;
use Phalcon\Validation;
use Phalcon\Validation\Validator\PresenceOf;
use Phalcon\Validation\Validator\Uniqueness;
use Engine\Behavior\Model\Fileable;
use Core\Helper\Utils as Helper;
use SphinxSearch\Search as SphinxSearch;
use SphinxSearch\Db\Sql\Update as UpdateSearch;

/**
 * Song Model.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @Source('fly_song');
 * @Behavior('\Engine\Behavior\Model\Timestampable');
 */
class Song extends AbstractModel
{
    /**
    * @Primary
    * @Identity
    * @Column(type="integer", nullable=false, column="s_id")
    */
    public $id;

    /**
    * @Column(type="integer", nullable=true, column="ar_id")
    */
    public $arid;

    /**
    * @Column(type="string", nullable=true, column="s_myid")
    */
    public $myid;

    /**
    * @Column(type="string", nullable=true, column="s_nct_key")
    */
    public $nctkey;

    /**
    * @Column(type="string", nullable=true, column="s_name")
    */
    public $name;

    /**
    * @Column(type="string", nullable=true, column="s_title")
    */
    public $title;

    /**
    * @Column(type="string", nullable=true, column="s_artist")
    */
    public $artist;

    /**
    * @Column(type="string", nullable=true, column="s_album")
    */
    public $album;

    /**
    * @Column(type="string", nullable=true, column="s_genre")
    */
    public $genre;

    /**
    * @Column(type="integer", nullable=true, column="s_track")
    */
    public $track;

    /**
    * @Column(type="string", nullable=true, column="s_composer")
    */
    public $composer;

    /**
    * @Column(type="string", nullable=true, column="s_comment")
    */
    public $comment;

    /**
    * @Column(type="string", nullable=true, column="s_type")
    */
    public $type;

    /**
    * @Column(type="integer", nullable=true, column="s_length")
    */
    public $length;

    /**
    * @Column(type="integer", nullable=true, column="s_cbr")
    */
    public $cbr;

    /**
    * @Column(type="integer", nullable=true, column="s_channel")
    */
    public $channel;

    /**
    * @Column(type="integer", nullable=true, column="s_size")
    */
    public $size;

    /**
    * @Column(type="string", nullable=true, column="s_file_path")
    */
    public $filepath;

    /**
    * @Column(type="string", nullable=true, column="s_lyric_path")
    */
    public $lyricpath;

    /**
    * @Column(type="string", nullable=true, column="s_cover_path")
    */
    public $coverpath;

    /**
    * @Column(type="integer", nullable=true, column="s_status")
    */
    public $status;

    /**
    * @Column(type="integer", nullable=true, column="s_download_status")
    */
    public $downloadstatus;

    /**
    * @Column(type="string", nullable=true, column="s_download_link")
    */
    public $downloadlink;

    /**
    * @Column(type="integer", nullable=true, column="s_count_listen")
    */
    public $countlisten;

    /**
    * @Column(type="integer", nullable=true, column="s_priority")
    */
    public $priority;

    /**
    * @Column(type="integer", nullable=true, column="s_date_created")
    */
    public $datecreated;

    /**
    * @Column(type="integer", nullable=true, column="s_date_modified")
    */
    public $datemodified;

    const STATUS_ENABLE = 1;
    const STATUS_DISABLE = 3;
    const DOWNLOAD_STATUS_PENDING = 1;
    const DOWNLOAD_STATUS_DOWNLOADING = 3;
    const DOWNLOAD_STATUS_COMPLETED = 5;
    const DOWNLOAD_STATUS_ERROR = 7;
    const DOWNLOAD_STATUS_FETCH_FAIL = 9;

    /**
     * Initialize model
     */
    public function initialize()
    {
        $config = $this->getDI()->get('config');

        if (!$this->getDI()->get('app')->isConsole()) {
            $configBehavior = [
                'field' => 'filepath',
                'uploadPath' => $config->default->songs->directory,
                'allowedFormats' => $config->default->songs->mimes->toArray(),
                'allowedMaximumSize' => $config->default->songs->maxsize,
                'allowedMinimumSize' => $config->default->songs->minsize,
                'isOverwrite' => $config->default->songs->isoverwrite
            ];

            $this->addBehavior(new Fileable([
                'beforeDelete' => $configBehavior
            ]));
        }
    }

    /**
     * Form field validation
     */
    public function validation()
    {
        $validator = new Validation();

        $validator->add('myid', new PresenceOf([
            'message' => 'message-myid-notempty'
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
            case self::STATUS_ENABLE:
                $name = $lang->_('label-status-enable');
                break;
            case self::STATUS_DISABLE:
                $name = $lang->_('label-status-disable');
                break;
        }

        return $name;
    }

    public static function getStatusList()
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
        ];
    }

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
        }

        return $class;
    }

    public function getDownloadStatusName(): string
    {
        $name = '';
        $lang = self::getStaticDi()->get('lang');

        switch ($this->downloadstatus) {
            case self::DOWNLOAD_STATUS_PENDING:
                $name = $lang->_('label-download-status-pending');
                break;
            case self::DOWNLOAD_STATUS_DOWNLOADING:
                $name = $lang->_('label-download-status-downloading');
                break;
            case self::DOWNLOAD_STATUS_COMPLETED:
                $name = $lang->_('label-download-status-completed');
                break;
            case self::DOWNLOAD_STATUS_ERROR:
                $name = $lang->_('label-download-status-error');
                break;
            case self::DOWNLOAD_STATUS_FETCH_FAIL:
                $name = $lang->_('label-download-status-fetch-fail');
                break;
        }

        return $name;
    }

    public static function getDownloadStatusList()
    {
        $lang = self::getStaticDi()->get('lang');

        return $data = [
            [
                'label' => $lang->_('label-download-status-pending'),
                'value' => (string) self::DOWNLOAD_STATUS_PENDING
            ],
            [
                'label' => $lang->_('label-download-status-downloading'),
                'value' => (string) self::DOWNLOAD_STATUS_DOWNLOADING
            ],
            [
                'label' => $lang->_('label-download-status-completed'),
                'value' => (string) self::DOWNLOAD_STATUS_COMPLETED
            ],
            [
                'label' => $lang->_('label-download-status-error'),
                'value' => (string) self::DOWNLOAD_STATUS_ERROR
            ],
            [
                'label' => $lang->_('label-download-status-fetch-fail'),
                'value' => (string) self::DOWNLOAD_STATUS_FETCH_FAIL
            ],
        ];
    }

    public function getDownloadStatusStyle(): string
    {
        $class = '';
        switch ($this->downloadstatus) {
            case self::DOWNLOAD_STATUS_PENDING:
                $class = 'info';
                break;
            case self::DOWNLOAD_STATUS_DOWNLOADING:
                $class = 'warning';
                break;
            case self::DOWNLOAD_STATUS_COMPLETED:
                $class = 'success';
                break;
            case self::DOWNLOAD_STATUS_ERROR:
                $class = 'danger';
                break;
            case self::DOWNLOAD_STATUS_FETCH_FAIL:
                $class = 'danger';
                break;
        }

        return $class;
    }

    public function parseTag()
    {
        $config = $this->getDI()->get('config');
        $data = [];
        $command = $config->default->idv3->mutagenInspect
            . ' $(pwd)'
            . $config->default->songs->directory
            . $this->filepath;

        putenv('LANG=en_US.UTF-8');
        $output = shell_exec($command);

        preg_match('/, (?P<SEC>[\d.]+) seconds/', $output, $matches);
        $this->length = !empty($matches) ? $matches['SEC'] : 0;

        preg_match('/, (?P<CHAN>[\d]+) chn/', $output, $matches);
        $this->channel = !empty($matches) ? $matches['CHAN'] : 0;

        preg_match('/, (?P<CBR>[\d]+) bps/', $output, $matches);
        $this->cbr = !empty($matches) ? $matches['CBR'] : 0;

        preg_match('/TALB=(?P<TALB>.*)/', $output, $matches);
        $this->album = !empty($matches) ? $matches['TALB'] : '';

        preg_match('/TCON=(?P<TCON>.*)/', $output, $matches);
        $this->genre = !empty($matches) ? $matches['TCON'] : '';

        preg_match('/TIT2=(?P<TIT2>.*)/', $output, $matches);
        $this->name = !empty($matches) ? $matches['TIT2'] : '';

        preg_match('/TPE1=(?P<TPE1>.*)/', $output, $matches);
        $this->artist = !empty($matches) ? $matches['TPE1'] : '';

        // preg_match('/TRCK=(?P<TRCK>.*)/', $output, $matches);
        // $this->track = !empty($matches) ? $matches['TRCK'] : '';
    }

    public function updateTag($formData)
    {
        $config = $this->getDI()->get('config');

        $fieldCommand = '';
        if (isset($formData['name']) && $formData['name'] != null) {
            $fieldCommand .= ' --song' . '="' . $formData['name'] . '"';
        }

        if (isset($formData['album']) && $formData['album'] != null) {
            $fieldCommand .= ' --album' . '="' . $formData['album'] . '"';
        }

        if (isset($formData['artist']) && $formData['artist'] != null) {
            $fieldCommand .= ' --artist' . '="' . $formData['artist'] . '"';
        }

        if (isset($formData['genre']) && $formData['genre'] != null) {
            $fieldCommand .= ' --genre' . '="' . $formData['genre'] . '"';
        }

        if (isset($formData['track']) && $formData['track'] != null) {
            $fieldCommand .= ' --track' . '="' . $formData['track'] . '"';
        }

        $command = $config->default->idv3->mid3v2
            . $fieldCommand
            . ' $(pwd)'
            . $config->default->songs->directory
            . $this->filepath;

        putenv('LANG=en_US.UTF-8');
        exec($command, $output, $return);

        if (!$return) {
            return true;
        } else {
            return false;
        }
    }

    public function updateSphinx()
    {
        $logger = $this->getDI()->get('slack');
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');
        $indexer = new \SphinxSearch\Indexer($sphinxAdapter);
        $indexer->setQueryMode('execute');

        $deleted = $indexer->delete(
            'olli_songs',
            ['id = '. $this->id .'']
        );

        if ($deleted) {
            try {
                $indexer->insert(
                    'olli_songs',
                    [
                        'id' => (int) $this->id,
                        'sid' => (int) $this->id,
                        'name' => (string) $this->name,
                        'artist' => (string) $this->artist,
                        'title' => (string) $this->title,
                        'myid' => (string) $this->myid,
                        'filepath' => (string) $this->filepath,
                        'album' => (string) $this->album,
                        'genre' => (string) $this->genre,
                        'length' => (float) $this->length,
                        'cbr' => (int) $this->cbr,
                        'status' => (int) $this->status,
                        'countlisten' => (int) $this->countlisten
                    ]
                );
            } catch (\Exception $e) {
                $logger->error('Song name: ' . $this->name . ' can not update to Sphinxsearch', $e);
            }
        }
    }

    public function insertSphinx()
    {
        $logger = $this->getDI()->get('slack');
        $sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');
        $indexer = new \SphinxSearch\Indexer($sphinxAdapter);
        $indexer->setQueryMode('execute');
        try {
            $indexer->insert(
                'olli_songs',
                [
                    'id' => (int) $this->id,
                    'sid' => (int) $this->id,
                    'name' => (string) $this->name,
                    'artist' => (string) $this->artist,
                    'title' => (string) $this->title,
                    'myid' => (string) $this->myid,
                    'filepath' => (string) $this->filepath,
                    'album' => (string) $this->album,
                    'genre' => (string) $this->genre,
                    'length' => (float) $this->length,
                    'cbr' => (int) $this->cbr,
                    'status' => (int) $this->status,
                    'countlisten' => (int) $this->countlisten
                ]
            );
        } catch (\Exception $e) {
            $logger->error('Song name: ' . $this->name . ' can not insert to Sphinxsearch', $e);
        }
    }

    public function getFileUrl()
    {
        $config = $this->getDI()->get('config');
        $url = $this->getDI()->get('url');

        return Helper::getFileUrl(
            $url->getBaseUri(),
            $config->default->songs->directory,
            $this->filepath
        );
    }
}
