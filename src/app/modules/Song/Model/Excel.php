<?php
namespace Song\Model;

use Engine\Db\AbstractModel;
use Song\Model\Song as SongModel;
use Core\Helper\Utils as Helper;
use Engine\UserException;
use Engine\Constants\ErrorCode;

/**
 * Song Excel Model.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2016-2017
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 */
class Excel extends AbstractModel
{
    private $allowedFormat = ['xlsx', 'xls'];

    public function parse()
    {
        $countSuccess = 0;
        $requestService = $this->getDI()->get('request');
        $queueService = $this->getDI()->get('queue');

        if ($requestService->hasFiles(true)) {
            foreach ($requestService->getUploadedFiles() as $file) {
                if (!$this->checkFileType($file)) {
                    throw new UserException(ErrorCode::FILE_UPLOAD_ERR_ALLOWED_FORMAT);
                }

                $objReader = \PHPExcel_IOFactory::load($file->getTempName());
                $objWorksheet = $objReader->getActiveSheet();

                $lastColumn = $objWorksheet->getHighestColumn();
                foreach ($objWorksheet->getRowIterator() as $rowIndex => $row) {
                    if ($rowIndex > 1) {
                        $arr = $objWorksheet->rangeToArray('A' . $rowIndex . ':' . $lastColumn . $rowIndex);
                        if (isset($arr[0])) {
                            $mySongInfo = $arr[0];

                            $mySong = SongModel::findFirst([
                                'nctkey = :nctkey:',
                                'bind' => [
                                    'nctkey' => (string) $mySongInfo[1]
                                ]
                            ]);

                            if (!$mySong) {
                                $mySong = new SongModel();
                                $mySong->assign([
                                    'myid' => (string) Helper::unique_id(),
                                    'status' => (int) SongModel::STATUS_DISABLE,
                                    'downloadstatus' => (int) SongModel::DOWNLOAD_STATUS_PENDING,
                                    'nctkey' => (string) $mySongInfo[1],
                                    'name' => (string) $mySongInfo[2],
                                    'artist' => (string) $mySongInfo[3],
                                    'downloadlink' => (string) $mySongInfo[4],
                                    'title' => (string) ($mySongInfo[2] . ' - ' . $mySongInfo[3])
                                ]);

                                if ($mySong->create()) {
                                    // Added to download queue
                                    $queueService->choose('song.downloadtoserver');
                                    $addedToQueue = $queueService->put([
                                        [
                                            'songId' => $mySong->id
                                        ],
                                        [
                                            'priority' => 1,
                                            'delay' => 3,
                                            'ttr' => 360
                                        ]
                                    ]);

                                    if (!$addedToQueue) {
                                        throw new UserException(ErrorCode::QUEUE_PUT_FAILED);
                                    }

                                    $mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_DOWNLOADING;
                                    $mySong->save();

                                    $countSuccess++;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $countSuccess;
    }

    private function checkFileType($file)
    {
        $pass = true;

        if (!in_array($file->getExtension(), $this->allowedFormat)) {
            $pass = false;
        }

        return $pass;
    }
}
