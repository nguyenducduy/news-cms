<?php
namespace News\Helper\Dom;

use PHPHtmlParser\Dom;
use Core\Helper\Utils as Helper;

class Vneconomy
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


        // get text contain
        $content = $dom->find('.contentdetail');
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
