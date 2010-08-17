<?php

//require_once 'esmetConfig.php';
require_once 'class.EventsServices.php';

define('ROOT', 'EVENT_ROOT');
define('EVENT_NODE', 'TAO_EVENT');
define('EVENT_NUMBER','EVENT_NUMBER');
define('EVENT_SYMBOL',"EVENT_SYMBOL");//the symbol attribute
define('NOISE_SYMBOL','Z');// the symbol used for noise events



class eventsFactory extends eventsServices {

    //prepare the document according to the tao event
    public function __construct($taoEvents) {
        //pepare the ebvent consists of adding symbol numeration attribute and
        parent::__construct($taoEvents);
        $this->incEventNumber();
        $this->addSymbolAttribute();
        //now we have a list with event with two new attributes; one for the incrementation,
        //the other for the symbol very importante
     
    }
// add an attribute with increment number from 1
    private function incEventNumber(){
        
        $usedXml = $this->currentXml;
        $xmlDoc = simplexml_load_string($usedXml);
        $inc = 1;
        foreach($xmlDoc as $node){
            $node->addAttribute(EVENT_NUMBER, $inc++);
        }
        $this->currentXml = $xmlDoc->asXML();
        
    }
    //add symbol attribute
    private function addSymbolAttribute(){
        //Call the parent method 
        $this->addAttributeForAllNodes(EVENT_SYMBOL,NOISE_SYMBOL);
    }

    // the symbolization method put the accurate pattern Symbole for event according to the query
    public function eventSymbolization($patternSymbol,$patternQuery){
        //for each event in after applaying the query, set the symbol value
        $query = '//'.EVENT_NODE.'['.$patternQuery.']';
        $this->setAttributesValue(EVENT_SYMBOL, $patternSymbol, $query);
    }
    //save the eventlog
    public function saveEvents($fileName="raffinedEvents.xml"){
        $xmlDoc = simplexml_load_string($this->currentXml);
        $xmlDoc->asXML($fileName);
    }


}

$xml = new DOMDocument();
$xml->load('tEventExample.xml');
$eventList = $xml->saveXML();
echo $eventList;

$p = new eventsFactory($eventList);


$p->eventSymbolization('Y', "(nom = 'younes')");


$p->saveEvents();
?>
