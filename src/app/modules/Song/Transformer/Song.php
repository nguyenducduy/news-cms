<?php
namespace Song\Transformer;

use League\Fractal\TransformerAbstract;
use Song\Model\Song as SongModel;
use Phalcon\Di;

/**
 * Song Transformer.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 */
class Song extends TransformerAbstract
{
    /**
     * List of resources possible to include
     *
     * @var array
     */
    protected $availableIncludes = [
    ];

    /**
     * Turn this resource object into a generic array
     *
     * @return array
     */
    public function transform(SongModel $song)
    {
        $humandatecreated = new \Moment\Moment($song->datecreated);

        return [
            'id' => (string) $song->id,
            'myid' => (string) $song->myid,
            'nctkey' => (string) $song->nctkey,
            'name' => (string) $song->name,
            'title' => (string) $song->title,
            'artist' => (string) $song->artist,
            'genre' => (string) $song->genre,
            'length' => (string) $song->length,
            'cbr' => (string) $song->cbr,
            'channel' => (string) $song->channel,
            'size' => (string) $song->size,
            'countlisten' => (string) $song->countlisten,
            'status' =>  [
                'label' => (string) $song->getStatusName(),
                'value' => (string) $song->status,
                'style' => (string) $song->getStatusStyle()
            ],
            'downloadstatus' =>  [
                'label' => (string) $song->getDownloadStatusName(),
                'value' => (string) $song->downloadstatus,
                'style' => (string) $song->getDownloadStatusStyle()
            ],
            'downloadlink' => (string) $song->downloadlink,
            'listenlink' => (string) $song->getFileUrl(),
            'datecreated' => (string) $song->datecreated,
            'humandatecreated' => (string) $humandatecreated->format('d M Y, H:i')
        ];
    }
}
