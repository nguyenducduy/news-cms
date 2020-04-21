<?php
namespace Youtube\Command;

use Engine\Console\AbstractCommand;
use Engine\Interfaces\CommandInterface;
use Engine\Console\ConsoleUtil;
use Song\Model\Song as SongModel;
use Core\Helper\Utils as Helper;

function downloadSong($fileName, $downloadDir , $videoId) {
	$downloadCmd = '/usr/local/bin/youtube-dl --extract-audio --audio-format mp3 -o \''. $downloadDir . $fileName . '.%(ext)s\' https://www.youtube.com/watch?v=' . $videoId;
	$downloadStatus = shell_exec($downloadCmd);
	return $downloadStatus;
}

/**
 * Youtube command.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <nguyenducduy.it@gmail.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @CommandName(['youtube'])
 * @CommandDescription('Download command controller.')
 */
class Youtube extends AbstractCommand implements CommandInterface
{
	/**
	 * Download song from link to server
	 *
	 * @return void
	 */
	public function toserverAction()
	{
		$queue = $this->getDI()->get('queue');
		$logger = $this->getDI()->get('slack');
		$queue->watch('youtube.downloadtoserver');
		$config = $this->getDI()->get('config');
		$sphinxAdapter = $this->getDI()->get('sphinx')->get('sphinxql');
		while (($job = $queue->reserve())) {

			$message = $job->getBody();
			$data = $message[0];
			
			$videoId = $data['id'];
			$videoTitle = $data['title'];
			// check if the song is available in the database
			$mySong = SongModel::findFirst([
				'title = :title:',
				'bind' => [
					'title' => $videoTitle,
				]
			]);

			if (!$mySong) {
				$mySong = new SongModel();
				$config = $this->getDI()->get('config');
				$dirName = ROOT_PATH
							. $config->default->songs->directory
							. Helper::getCurrentDateDirName();
				
				if (!is_dir($dirName)) {
					//Directory does not exist, so lets create it.
					mkdir($dirName, 0755, true);
				}

				$fileName = Helper::slug($videoTitle) . '-youtube-'.$videoId;
				$path = $dirName . $fileName . '.mp3';

				$downloadStatus = downloadSong($fileName , $dirName, $videoId);

				$mySong->title = $videoTitle;
				if($downloadStatus == NULL) {
					$mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_ERROR;
					$mySong->save();
				} else {
					$mySong->status = SongModel::STATUS_ENABLE;
					$mySong->downloadstatus = SongModel::DOWNLOAD_STATUS_COMPLETED;
					$filesize = filesize($path);
					$filesize = round($filesize / 1000 / 1000, 1);
					
					preg_match('/[0-9]+\/[A-Za-z]+\/[0-9]{1,2}\/.*/', $path, $matches);
					$mySong->filepath = $matches[0];
					$mySong->size = $filesize;
					$mySong->myid =  (string) Helper::unique_id();

					if (!$mySong->create()) {
						foreach ($mySong->getMessages() as $msg) {
							print_r($msg);
						}
					}
				}
			}

			$job->bury();
		}
	}
}
