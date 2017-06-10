<?php
/**
 * User: lincanbin
 * Date: 2017/6/9
 * Time: 11:37
 */

namespace lincanbin;


class WhiteHTMLFilterConfig
{
    public $WhiteListTag = array(
        "#cdata-section" => array(),
        "#comment" => array(),
        "#text" => array(),
        "img" => array("alt", "src", "height", "width"),
        "a" => array("href", "rel", "target", "download", "type"),
        "div" => array(),
        "table" => array("sortable", "width"),
        "tbody" => array(),
        "caption" => array(),
        "tr" => array(),
        "td" => array("valign", "colspan", "rowspan"),
        "th" => array("width"),
        "br" => array(),
        "p" => array(),
        "b" => array(),
        "strong" => array(),
        "i" => array(),
        "u" => array(),
        "em" => array(),
        "span" => array(),
        "ol" => array(),
        "ul" => array(),
        "li" => array("value"),
        "blockquote" => array("cite"),
        //"object" => array(),
        //"param" => array(),
        "embed" => array("type", "pluginspage", "src", "width", "height", "wmode", "play", "loop", "menu", "allowscriptaccess", "allowfullscreen"),
        "pre" => array(),
        "hr" => array(),
        "h1" => array(),
        "h2" => array(),
        "h3" => array(),
        "h4" => array(),
        "h5" => array(),
        "h6" => array(),
        "video" => array("autoplay", "controls", "height", "loop", "muted", "poster", "preload", "src", "width"),
        "source" => array("src", "srcset", "media", "sizes", "type"),
        "audio" => array("autoplay", "controls", "loop", "muted", "preload", "src")
    );

    public $WhiteListHtmlGlobalAttributes = array(
        "class", "style", "title", "data-*"
    );
    public $WhiteListStyle = array();

    /**
     * Get attributes whitelist of some tag
     * @param string $tagName Tag name
     * @return array Attributes whitelist of some tag
     */
    public function getWhiteListAttr($tagName)
    {
        if (!empty($this->WhiteListTag[$tagName])) {
            return $this->WhiteListTag[$tagName];
        }
        return array();
    }

    public function removeAllAllowTag()
    {
        $this->WhiteListTag = array();
    }

    public function removeFromTagWhiteList($tagName)
    {
        if (is_string($tagName)) {
            $tagName = array($tagName);
        }
        foreach ($tagName as $val) {
            if (array_key_exists($val, $tagName)) {
                unset($tagName[$val]);
            }

        }
    }

    public function modifyTagWhiteList(array $config)
    {
        foreach ($config as $tagName => $val) {
            if (is_array($val)) {
                $this->WhiteListTag[strtolower(trim($tagName))] = $val;
            }
        }
    }
}