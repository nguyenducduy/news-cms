<?php
namespace Dashboard\Controller\V1;

use Core\Controller\AbstractController;
use Engine\UserException;
use Engine\Constants\ErrorCode;
use Dashboard\Model\Log as LogModel;
use Dashboard\Transformer\Log as LogTransformer;
use Core\Helper\Utils as Helper;

/**
 * Dashboard api.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <duy@olli-ai.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @RoutePrefix("/v1/dashboards")
 */
class IndexController extends AbstractController
{
    protected $recordPerPage = 50;

    /**
     * Get all
     *
     * @Route("/", methods={"GET"})
     */
    public function listAction()
    {
        $page = (int) $this->request->getQuery('page', null, 1);
        $formData = [];
        $hasMore = true;

        $page = (int) $this->request->getQuery('page', null, 1);
        $field = (string) $this->request->getQuery('field', null, '');
        $keyword = (string) addslashes($this->request->getQuery('keyword', null, ''));

        $output = [];
        $dir = ROOT_PATH . '/app/storage/logs';
        if (is_dir($dir)) {
            $files = scandir($dir, SCANDIR_SORT_DESCENDING);
            foreach ($files as $file) {
                if (preg_match('/^(.*).log/', $file)) {
                    $lines = file(ROOT_PATH . '/app/storage/logs/' . $file);
                    $contentArr = explode('\n', $lines[0]);
                    krsort($contentArr);

                    if (count($contentArr) > 0) {
                        foreach ($contentArr as $line) {
                            preg_match("/\[(?P<date>[0-9]{1,2}\/[0-9]{1,2}\/[0-9]{4} [0-9]{1,2}:[0-9]{1,2} (am|pm))\] \[(?P<logger>[a-z]+)\] \[(?P<level>[A-Z]+)\] (?P<message>[^{]*) (?P<context>.*)/", $line, $matches);

                            if (count($matches) > 0) {
                                $myLog = new LogModel();
                                $myLog->date = $matches['date'];
                                $myLog->logger = $matches['logger'];
                                $myLog->level = $matches['level'];
                                $myLog->message = $matches['message'];
                                $myLog->context = json_decode($matches['context']);
                                $output[] = $myLog;
                            }
                        }
                    }
                }
            }
        }


        if (strlen($field) > 0 && strlen($keyword) > 0) {
            $output = array_filter($output, function (LogModel $myLog) use ($field, $keyword) {
                return preg_match("/\b$keyword\b/i", $myLog->$field);
            });
        }

        // Create paginator object
        $paginator = new \Phalcon\Paginator\Adapter\NativeArray([
            'data' => $output,
            'limit' => $this->recordPerPage,
            'page' => $page
        ]);

        $myLogs = $paginator->getPaginate();

        if ($myLogs->total_pages > 0) {
            if ($page == $myLogs->total_pages) {
                $hasMore = false;
            }

            return $this->createCollection(
                $myLogs->items,
                new LogTransformer,
                'records',
                [
                    'meta' => [
                        'recordPerPage' => $this->recordPerPage,
                        'hasMore' => $hasMore,
                        'totalItems' => $myLogs->total_items,
                        'page' => $page
                    ]
                ]
            );
        } else {
            return $this->respondWithArray([], 'records');
        }
    }

    /**
     * Get DB Mysql stats
     *
     * @Route("/dbstatus", methods={"GET"})
     */
    public function dbstatusAction()
    {
        $output = [];
        $myDbHost = (string) $this->request->getQuery('host', null, '');

        // Get mysql status
        $mysqlCommand = 'mysqladmin -h '. $myDbHost .' -P '. getenv('DB_PORT') .' -u '. getenv('DB_USERNAME') .' -p"'. getenv('DB_PASSWORD') .'" status';
        $mysqlOutput = shell_exec($mysqlCommand);

        $output['mysql']['host'] = $myDbHost;

        preg_match('/Uptime: (?P<uptime>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['uptime'] = Helper::convert_seconds($matches['uptime']); // The number of seconds the MySQL server has been running

        preg_match('/Open tables: (?P<opens>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['opens'] = $matches['opens']; // The number tables opened

        preg_match('/Questions: (?P<queries_count>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['queries_count'] = $matches['queries_count']; // The number of questions (queries) from clients since the server was started

        preg_match('/Threads: (?P<threads>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['threads'] = $matches['threads']; // The number of active threads

        preg_match('/Slow queries: (?P<slow_queries>[0-9]+)/', $mysqlOutput, $matches);
        $output['mysql']['slow_queries'] = $matches['slow_queries']; // The number of queries that have taken more than long_query_time seconds

        preg_match('/Queries per second avg: (?P<queries_per_second_avg>[0-9\.]+)/', $mysqlOutput, $matches);
        $output['mysql']['queries_per_second_avg'] = $matches['queries_per_second_avg'];

        echo json_encode($output);
        exit();
    }

    /**
     * Get Sphinxsearch index stats
     *
     * @Route("/searchstatus", methods={"GET"})
     */
    public function searchstatusAction()
    {
        $output = [];
        $mySearchHost = (string) $this->request->getQuery('host', null, '');

        // Get sphinx search status
        $output['sphinx']['status'] = true;
        $sphinxClient = new \SphinxClient();
        $sphinxClient->setServer($mySearchHost, 9312);

        try {
            $sphinxOutput = $sphinxClient->status();

            $output['sphinx']['host'] = $mySearchHost;
            $output['sphinx']['uptime'] = Helper::convert_seconds($sphinxOutput[0][1]); // number of second since server start
            $output['sphinx']['connections'] = $sphinxOutput[1][1]; // number of connection since server start
            $output['sphinx']['maxed_out'] = $sphinxOutput[2][1]; // limited by max_children or by system limits
            $output['sphinx']['command_search'] = $sphinxOutput[3][1]; // number of search command since server start
            $output['sphinx']['queries'] = $sphinxOutput[13][1]; // number of all query since server start
            $output['sphinx']['query_wall'] = $sphinxOutput[15][1]; // time to process query in seconds (all query)
            $output['sphinx']['avg_query_wall'] = $sphinxOutput[23][1]; // average query duration (all query)
        } catch (\Exception $e) {
            $output['sphinx']['status'] = false;
        }

        echo json_encode($output);
        exit();
    }

    /**
     * Get Beanstalkd stats
     *
     * @Route("/queuestatus", methods={"GET"})
     */
    public function queuestatusAction()
    {
        $output = [];
        $myQueueHost = (string) $this->request->getQuery('host', null, '');

        // Get Beanstalkd status
        $output['beanstalk']['status'] = true;
        try {
            $beanstalkOutput = $this->queue->stats();

            $output['beanstalk']['host'] = $myQueueHost;
            $output['beanstalk']['uptime'] = Helper::convert_seconds($beanstalkOutput['uptime']);
            $output['beanstalk']['total_jobs'] = $beanstalkOutput['total-jobs'];
            $output['beanstalk']['current_tubes'] = $beanstalkOutput['current-tubes'];
            $output['beanstalk']['current_producers'] = $beanstalkOutput['current-producers'];
            $output['beanstalk']['current_workers'] = $beanstalkOutput['current-workers'];
            $output['beanstalk']['current_waiting'] = $beanstalkOutput['current-waiting'];
            $output['beanstalk']['current_connections'] = $beanstalkOutput['current-connections'];
            $output['beanstalk']['total_connections'] = $beanstalkOutput['total-connections'];
        } catch (\Exception $e) {
            $output['beanstalk']['status'] = false;
        }

        echo json_encode($output);
        exit();
    }
}
