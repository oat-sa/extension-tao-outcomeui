<?php

//require_once 'esmetConfig.php';
require_once 'class.EventsServices.php';

define('ROOT', 'EVENT_ROOT');
define('EVENT_NODE', 'TAO_EVENT');
define('EVENT_NUMBER','EVENT_NUMBER');
define('EVENT_SYMBOL',"EVENT_SYMBOL");//the symbol attribute

class eventsFactory extends eventsServices {

    //prepare the document according to the tao event
    public function __construct($taoEvents) {
        //pepare the ebvent consists of adding symbol numeration attribute and
        $this->incEventNumber();
        $this->addSymbolAttribute();
     
    }
// add an attribute with increment number from 1
    private function incEventNumber(){
        $node = new SimpleXMLElement();
        $usedXml = $this->currentXml;
        $xmlDoc = simplexml_load_string($usedXml);
        $inc = 1;
        foreach($xmlDoc as $node){
            $node->addAttribute(EVENT_NUMBER, $inc);
        }
        $this->currentXml = $usedXml;
        
    }
    //add symbol attribute
    private function addSymbolAttribute(){
        $this->addAttributeForAllNodes(EVENT_SYMBOL);
    }
}
?>
