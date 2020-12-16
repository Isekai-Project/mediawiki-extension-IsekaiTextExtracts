<?php
namespace TextExtracts;

use DOMDocument;
use DOMXPath;

class IntroSectionSearcher {
    private $headerTag = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    private $ignoreTag = ['#comment'];
    /** @var DOMDocument $dom */
    private $dom;

    /** @var DOMDocument $introDom */
    private $introDom;

    public function __construct($html){
        $this->dom = new DomDocument('1.0', 'UTF-8');
        @$this->dom->loadHtml('<meta charset="UTF-8"/>' . $html);
        $this->introDom = new DOMDocument('1.0', 'UTF-8');
        $this->makeIntroHtml();
    }

    public function makeIntroHtml(){
        $inContent = false;
        //搜索dom的body
        $xpath = new DOMXPath($this->dom);
        $source = $xpath->query('body');
        if(!$source->length){
            return;
        }
        /** @var \DOMNode $body */
        $body = $source->item(0);
        /** @var \DOMNode $childNode */
        foreach($body->childNodes as $childNode){
            if(in_array($childNode->nodeName, $this->headerTag)){
                if($inContent){
                    //如果有内容，遇到下一个标题就退出
                    return;
                }
            } elseif(!in_array($childNode->nodeName, $this->ignoreTag)) {
                if(!empty(trim($childNode->textContent))) {
                    $this->introDom->appendChild($this->introDom->importNode($childNode, true));
                    if(!$inContent) $inContent = true;
                } elseif($inContent) {
                    $this->introDom->appendChild($this->introDom->importNode($childNode));
                }
            }
        }
    }

    public function getIntroHtml(){
        $node = $this->introDom;
        return trim($this->introDom->saveHTML($node));
    }
}