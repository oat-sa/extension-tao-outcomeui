<?php

require_once 'esmet_config.php';

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
    public function eventSymbolization(symbolDescription $symbol){
        $patternSymbol= $symbol->symbolLetter;
        $patternQuery = $symbol->query;
        //for each event in after applaying the query, set the symbol value
        $query = '//'.EVENT_NODE.'['.$patternQuery.']';
        $this->setAttributesValue(EVENT_SYMBOL, $patternSymbol, $query);
    }
    //symbolization of all the log
    //as in put, an array of symbol
    public function fullSymbolization($patternSymbolCollection){
        
        foreach( $patternSymbolCollection as $symbol){
            $this->eventSymbolization($symbol);
            

        }
    }
    //create the symbolized event Log
    public function generateSymbolizedEvents(){
        $usedEvents = $this->currentXml;
        $listEvents = simplexml_load_string($usedEvents);
        $symbolizedLog = '';
        foreach ($listEvents as $event){
            $symbolLetter = $event->attributes()->EVENT_SYMBOL;
            $symbolizedLog=$symbolizedLog.$symbolLetter;
        }
        return $symbolizedLog;

    }


    //save the eventlog
    public function saveEvents($fileName="raffinedEvents.xml"){
        $xmlDoc = simplexml_load_string($this->currentXml);
        $xmlDoc->asXML($fileName);
    }
    //match the trace
    public function matchingPatternMatchin($patternToMatch,$symbolizedTraces){
        $usedTraces = $symbolizedTraces;
        $match = false;
        $match = preg_match('/'.$patternToMatch.'/', $usedTraces);
        return $match;
 
    }
}

?>
