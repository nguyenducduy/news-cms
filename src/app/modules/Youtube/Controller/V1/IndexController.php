<?php
namespace Youtube\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Song\Model\Song as SongModel;
use Core\Helper\Utils as Helper;
use Event\Model\RelUserEvent as RelUserEventModel;
use Event\Model\Event as EventModel;



/**
 * Youtube api.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @RoutePrefix("/v1/youtube")
 */
class IndexController extends AbstractController
{
	/**
	 * Get all
	 *
	 * @Route("/", methods={"POST"})
	 */
	public function downloadAction()
	{
		$formData = (array) $this->request->getJsonRawBody();
		$logger = $this->getDI()->get('slack');
		$googleClient = new \Google_Client();
		$googleClient->setDeveloperKey(getenv('GOOGLE_API_KEY'));
		$youtubeService = new \Google_Service_YouTube($googleClient);

		$keyword = $formData['keyword'];
		$videoInfo = Helper::getYoutubeVideoId($youtubeService, $keyword);
		$videoId = $videoInfo['id'];
		$videoTitle = $videoInfo['title'];

		// Push to Beanstalk Queue
		$queue = $this->getDI()->get('queue');
		$queue->choose('youtube.downloadtoserver');
		$addedToQueue = $queue->put([
		    [
		        'id' => $videoId,
		        'title' => $videoTitle
		    ],
		    [
		        'priority' => 1,
		        'delay' => 10,
		        'ttr' => 3600
		    ]
		]);

		if (!$addedToQueue) {
		    throw new UserException(ErrorCode::QUEUE_PUT_FAILED);
		}

        return $this->respondWithOK();
	}
}
