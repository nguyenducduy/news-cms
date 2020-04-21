<?php
namespace News\Command;

use Engine\Console\AbstractCommand;
use Engine\Interfaces\CommandInterface;
use Engine\Console\ConsoleUtil;
use News\Model\News as NewsModel;
use Core\Helper\Utils as Helper;
use News\Helper\Parse as ParseHelper;

/**
 * News command.
 *
 * @category  OLLI CMS Platform
 * @author    Nguyen Duc Duy <nguyenducduy.it@gmail.com>
 * @copyright 2014-2015
 * @license   New BSD License
 * @link      http://cms.olli.vn/
 *
 * @CommandName(['crawl'])
 * @CommandDescription('News command controller.')
 */
class Crawl extends AbstractCommand implements CommandInterface
{
    /**
     * Crawl news content from rss link
     *
     * @return void
     */
    public function rssAction()
    {
        $config = $this->getDI()->get('config');
        $logger = $this->getDI()->get('logger');
        $sphinxAdapter = $this->getDI()->get('sphinxNews')->get('sphinxql');
        $count = 0;

        foreach ($config->rss as $rssSite) {
            foreach ($rssSite->category->toArray() as $rssCat => $rssLink) {
                $myCat = self::findCategory($rssCat, $config->category->items->toArray());
                // echo "Category: [$rssCat] " . $myCat['name'] . PHP_EOL;

                try {
                    $rss = \Feed::load(
                        $rssSite->url
                        . $rssLink
                    );
                } catch (\Exception $e) {
                    $logger->error('RSS link can not load: ' . $rssSite->url . $rssLink);
                    return;
                }

                foreach ($rss->item as $item) {
                    // Check existed news crawled using link attribute
                    $myNews = NewsModel::findFirst([
                        'link = :link: AND source = :source:',
                        'bind' => [
                            'link' => (string) trim($item->link->__toString()),
                            'source' => (string) $rssSite->source
                        ]
                    ]);

                    if (!$myNews) {
                        if (strlen($item->link->__toString()) > 0) {
                            $context = ParseHelper::getContent($rssSite->source, $item->link->__toString());

                            // Content is almost text
                            if ($context->content != '') {
                                $context->title = trim($item->title->__toString());
                                $context->link = trim($item->link->__toString());
                                $context->pubdate = trim($item->pubDate->__toString());
                                $datepublished = $context->formatTime();

                                $myNews = new NewsModel();
                                $myNews->assign([
                                    'cid' => (int) $myCat['id'],
                                    'title' => (string) $context->title,
                                    'description' => (string) $context->description,
                                    'content' => (string) $context->content,
                                    'keywords' => (string) $context->keywords,
                                    'link' => (string) $context->link,
                                    'status' => (int) NewsModel::STATUS_DRAFT,
                                    'datepublished' => (int) $datepublished
                                ]);
                                $myNews->source = (string) $rssSite->source;

                                if ($myNews->create()) {
                                    $count++;
                                }
                            }
                        }
                    }
                }
            }
        }

        echo "Total $count link crawled.";
    }

    private static function findCategory($rssCat, $findIn) {
        foreach ($findIn as $item) {
            if (in_array($rssCat, $item['slugs'])) {
                return $item;
            }
        }
    }
}
