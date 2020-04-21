<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Vietnamnet
{
    public $title = '';
    public $description = '';
    public $link = '';
    public $content = '';
    public $pubdate = 0;
    public $keywords = '';

    public function __construct(string $link = '')
    {
        $dom = new Dom();
        try {
            $dom->loadFromUrl(trim($link));
        } catch (\Exception $e) {
            return;
        }


        // remove image block
        $removeItems = $dom->find('.ImageBox');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // remove ads block
        $removeItems = $dom->find('.box-taitro');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // remove more small news block
        $removeItems = $dom->find('.inner-article');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // remove more small news relate
        $removeItems = $dom->find('.article-relate');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };


        // remove author and other content is not useful
        foreach ($dom->find('p') as $paragraph) {
            $authorContext = $paragraph->find('.bold');

            if (count($authorContext) > 0) {
                $authorContext->delete();
                unset($authorContext);
            }
        }

        // get text contain
        $content = $dom->find('.ArticleContent');
        if (count($content) > 0) {
            $this->content = trim(Helper::plaintext($content->outerHtml));
        }

        // remove article href more
        $this->content = str_replace('Xem đầy đủ tại đây', ' ', $this->content);

        // get keywords
        $metaTags = $dom->find('meta');
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('name') == 'keywords') {
                if ($this->keywords == '') {
                    $this->keywords = Helper::plaintext($meta->content);
                }
            }
        }

        // get description
        $metaTags = $dom->find('meta');
        foreach ($metaTags as $meta) {
            if ($meta->getAttribute('name') == 'description') {
                if ($this->description == '') {
                    $this->description = Helper::plaintext($meta->content);
                }
            }
        }
    }

    public function formatTime()
    {
        $articleTime = trim(str_replace(' (GMT+7)', '', $this->pubdate));

        $gmtTimezone = new \DateTimeZone('GMT+7');
        return \DateTime::createFromFormat('d/m/Y', $articleTime, $gmtTimezone)->getTimestamp();
    }
}
