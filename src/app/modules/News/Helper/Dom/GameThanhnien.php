<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class GameThanhnien
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


        // remove image
        $images = $dom->find('.full-img');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };

        // remove inline story block
        $stories = $dom->find('.related-story');
        if (count($stories) > 0) {
            foreach ($stories as $story) {
                $story->delete();
                unset($story);
            }
        }
        $stories = $dom->find('.small-news');
        if (count($stories) > 0) {
            foreach ($stories as $story) {
                $story->delete();
                unset($story);
            }
        }

        // Remove intro box
        $introboxs = $dom->find('.introbox');
        if (count($introboxs) > 0) {
            foreach ($introboxs as $box) {
                $box->delete();
                unset($box);
            }
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

        // get text contain
        $content = $dom->find('.details-content');
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
