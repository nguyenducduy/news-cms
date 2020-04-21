<?php
namespace News\Helper;

use Engine\Helper as EnHelper;

class Parse extends EnHelper
{
    public $siteName = '';

    public static function getContent($siteName, $link)
    {
        // Check if subdomain of site name
        $mySiteArr = explode('_', $siteName);

        if (count($mySiteArr) > 1) {
            $myDomClassName = '\News\Helper\Dom\\' . ucfirst($mySiteArr[0]) . ucfirst($mySiteArr[1]);
        } else {
            $myDomClassName = '\News\Helper\Dom\\' . ucfirst($siteName);
        }

        $mySource = new $myDomClassName($link);

        return $mySource;
    }
}
