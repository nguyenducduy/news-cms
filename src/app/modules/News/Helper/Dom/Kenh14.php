<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Kenh14
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

        // remove author and other content is not useful
        foreach ($dom->find('p') as $paragraph) {
            if (
                $paragraph->getAttribute('style') == 'text-align:right;'
                || $paragraph->getAttribute('style') == 'text-align:right'
                || $paragraph->getAttribute('style') == 'text-align: right;'
                || $paragraph->getAttribute('style') == 'text-align: right'
                || $paragraph->getAttribute('style') == 'text-align:center;'
                || $paragraph->getAttribute('style') == 'text-align:center'
                || $paragraph->getAttribute('style') == 'text-align: center;'
                || $paragraph->getAttribute('style') == 'text-align: center'
            ) {
                $paragraph->delete();
            }
            unset($paragraph);
        }
        // remove author and other content is not useful
        foreach ($dom->find('div') as $paragraph) {
            if (
                $paragraph->getAttribute('style') == 'text-align:right;'
                || $paragraph->getAttribute('style') == 'text-align:right'
                || $paragraph->getAttribute('style') == 'text-align: right;'
                || $paragraph->getAttribute('style') == 'text-align: right'
                || $paragraph->getAttribute('style') == 'text-align:center;'
                || $paragraph->getAttribute('style') == 'text-align:center'
                || $paragraph->getAttribute('style') == 'text-align: center;'
                || $paragraph->getAttribute('style') == 'text-align: center'
            ) {
                $paragraph->delete();
            }
            unset($paragraph);
        }

        // remove table/video/image block
        $removeItems = $dom->find('.VCSortableInPreviewMode');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // get text contain
        $content = $dom->find('.knc-content');
        if (count($content) > 0) {
            $this->content = trim(Helper::plaintext($content->outerHtml));
        }

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
        return strtotime($this->pubdate);
    }
}
