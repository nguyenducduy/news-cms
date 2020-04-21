<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Vnexpress
{
    public $title = '';
    public $description = '';
    public $link = '';
    public $content = '';
    public $pubdate = 0;
    public $keywords = '';

    public function __construct(string $link = '')
    {
        // if link is photo link, ignore
        if (preg_match('/https:\/\/.*.vnexpress.net\/photo\/.*/', trim($link))) {
            return;
        };

        $dom = new Dom();
        try {
            $dom->loadFromUrl(trim($link));
        } catch (\Exception $e) {
            return;
        }

        // remove image
        $images = $dom->find('.Image');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };
        $images = $dom->find('.tplCaption');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };
        $images = $dom->find('.item_slide_show');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };

        // remove video if exist
        $videos = $dom->find('.box_embed_video_parent');
        if (count($videos) > 0) {
            foreach ($videos as $video) {
                $video->delete();
                unset($video);
            }
        }

        // remove ads box
        $videos = $dom->find('.box_quangcao');
        if (count($videos) > 0) {
            foreach ($videos as $video) {
                $video->delete();
                unset($video);
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
        // remove author
        $authors = $dom->find('.author_mail');
        if (count($authors) > 0) {
            foreach ($authors as $author) {
                $author->delete();
                unset($author);
            }
        };

        // get text contain
        $content = $dom->find('article');
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
