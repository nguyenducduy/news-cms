<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Soha
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

        // remove table/video/image block
        $removeItems = $dom->find('.VCSortableInPreviewMode');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

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

        // remove news relate
        $removeItems = $dom->find('.relationnews');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // remove news sapo
        $removeItems = $dom->find('.news-sapo');
        if (count($removeItems) > 0) {
            foreach ($removeItems as $item) {
                $item->delete();
                unset($item);
            }
        };

        // remove author
        $author = $dom->find('.bottom-info');
        if (count($author) > 0) {
            $author->delete();
            unset($author);
        }

        // get text contain
        $content = $dom->find('.detail-body');
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
