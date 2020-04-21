<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Tinhte
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
        $images = $dom->find('.attachedFiles');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };

        // remove link expander
        $images = $dom->find('.LinkExpander');
        if (count($images) > 0) {
            foreach ($images as $image) {
                $image->delete();
                unset($image);
            }
        };

        // get text contain
        $content = $dom->find('article');
        if (count($content) > 0) {
            $this->content = trim(Helper::plaintext($content->outerHtml));
        }

        // // get keywords
        // $metaTags = $dom->find('meta');
        // foreach ($metaTags as $meta) {
        //     if ($meta->getAttribute('name') == 'keywords') {
        //         if ($this->keywords == '') {
        //             $this->keywords = Helper::plaintext($meta->content);
        //         }
        //     }
        // }
    }

    public function formatTime()
    {
        return strtotime($this->pubdate);
    }
}
