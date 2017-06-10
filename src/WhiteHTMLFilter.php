<?php
/**
 * User: lincanbin
 * Date: 2017/6/9
 * Time: 11:35
 */

namespace lincanbin;

use \DOMDocument;
use \DOMElement;
use \Exception;


/**
 * @property WhiteHTMLFilterConfig config
 * @property DOMDocument dom
 */
class WhiteHTMLFilter
{
    public $config;
    private $dom = NULL;

    public function __construct()
    {
        $this->config = new WhiteHTMLFilterConfig();

        if (!$this->dom) {
            $this->dom = new DOMDocument();
        }
        $this->dom->preserveWhiteSpace = false;
        $this->dom->formatOutput = true;
        $this->dom->encoding = 'UTF-8';
        //Disable libxml errors
        libxml_use_internal_errors(true);
    }

    /**
     * Get current tag whitelist
     * @return array
     */
    public function getWhiteListTags()
    {
        return ($this->config->WhiteListTag);
    }

    /**
     * Load document markup into the class for cleaning
     * @param string $html The markup to clean
     * @return bool
     */
    public function loadHTML($html)
    {
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8");
        $html = str_replace(chr(13), '', $html);
        return $this->dom->loadHTML($html, LIBXML_HTML_NODEFDTD);
    }

    public function outputHtml()
    {
        $result = '';
        if (!is_null($this->dom)) {
            $GenerateTag = function ($tagName) {
                return '<' . $tagName . '>';
            };
            $allowTagsString = implode('', array_map($GenerateTag, array_keys($this->config->WhiteListTag)));
            $result = trim($this->dom->saveHTML());
            $result = mb_convert_encoding($result, "UTF-8", 'HTML-ENTITIES');
            $result = strip_tags($result, $allowTagsString);
        }
        return $result;
    }

    private function cleanAttrs(DOMElement $elem)
    {
        $tagName = strtolower($elem->nodeName);
        $attrs = $elem->attributes;
        $index = $attrs->length;
        $attrsWhiteList = array_merge($this->config->getWhiteListAttr($tagName), $this->config->WhiteListHtmlGlobalAttributes);
        $allowDataAttribute = in_array("data-*", $attrsWhiteList);
        while (--$index >= 0) {
            /* @var $domAttr \DOMAttr */
            $domAttr = $attrs->item($index);
            $attrName = strtolower($domAttr->name);
            // 如果不在白名单attr中，而且允许data-*，且不是data-*，则删除
            if (!in_array($attrName, $attrsWhiteList) && $allowDataAttribute && (stripos($attrName, "data-") !== 0)) {
                $elem->removeAttribute($attrName);
            }
        }
    }

    /**
     * Recursivly remove elements from the DOM that aren't whitelisted
     * @param DOMElement $elem
     * @param boolean $isFirstNode
     * @return array List of elements removed from the DOM
     * @throws Exception If removal of a node failed than an exception is thrown
     */
    private function cleanNodes(DOMElement $elem, $isFirstNode = false)
    {
        //var_dump($elem->nodeName);
        $removed = array();
        if ($isFirstNode || array_key_exists(strtolower($elem->nodeName), $this->config->WhiteListTag)) {
            if ($elem->hasAttributes()) {
                $this->cleanAttrs($elem);
            }
            /*
             * Iterate over the element's children. The reason we go backwards is because
             * going forwards will cause indexes to change when elements get removed
             */
            if ($elem->hasChildNodes()) {
                $children = $elem->childNodes;
                $index = $children->length;
                while (--$index >= 0) {
                    $cleanNode = $children->item($index);// DOMElement or DOMText
                    if ($cleanNode instanceof DOMElement) {
                        $removed = array_merge($removed, $this->cleanNodes($cleanNode));
                    }

                }
            }
        } else {
            if ($elem->parentNode->removeChild($elem)) {
                $removed [] = $elem;
            } else {
                throw new Exception('Failed to remove node from DOM');
            }
        }
        return ($removed);
    }

    /**
     * Perform the cleaning of the document
     */
    public function clean()
    {
        $removed = $this->cleanNodes($this->dom->getElementsByTagName('body')->item(0), true);
        return ($removed);
    }
}