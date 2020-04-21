<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Thanhnien
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
        $images = $dom->find('.imagefull');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };
        $images = $dom->find('.pswp-content__wrapimage');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };
        $images = $dom->find('.pswp-content__caption');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };
        $images = $dom->find('.thumb');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };

        // remove author
        $author = $dom->find('.details__author');
        if (count($author) > 0) {
            $author->delete();
            unset($author);
        }

        // remove story
        $stories = $dom->find('.story');
        if (count($stories) > 0) {
            foreach ($stories as $story) {
                $story->delete();
                unset($story);
            }
        };

        // remove video
        $videos = $dom->find('.video');
        if (count($videos) > 0) {
            foreach ($videos as $video) {
                $video->delete();
                unset($video);
            }
        };

        // get text contain
        $content = $dom->find('.details__content');
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
