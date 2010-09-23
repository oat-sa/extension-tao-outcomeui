<?php

require_once 'esmet_config.php';
/*
 * converts an iputed trace to event TAO Event Model
 *
 */

/**
 * Description of class
 *
 * @author djaghloul
 */
class taoEventModelConverter {

    public $currentTrace = '';

    public function __construct($xml) {
        $this->currentTrace = $xml;
    }

    //convert a trace done by HAWAI item to Tao Event Model
    public function importFromHAWAI($xml='') {
        $unifiedArray = array();
        $usedXml = $xml;
        if ($xml == '') {
            $usedXml = $this->currentTrace;
        }

        $hawaiTrace = new DOMDocument();
        $hawaiTrace->loadXML($usedXml);
//create the unified array by parsing attribute and string
        $listEvent = $hawaiTrace->getElementsByTagName('taoEvent');

        $unifiedArray = $this->getTracesAsArray($listEvent);
        //create the Xml
        $es = new eventsServices();
        $finalXml = $es->simpleArrayToXml($unifiedArray, ROOT, EVENT_NODE);
        return $finalXml;
    }

    private function getTracesAsArray($domTrace=null) {

        $tracesArray = array();
        $taoEventList = $domTrace;


        //$tracesArray=0;
        //************************************************
        foreach ($taoEventList as $taoEvent) {
            //On commence par extraire les attributs de la trace de l'evenement

            $TE_Name = $taoEvent->getAttribute('Name');
            $TE_Type = $taoEvent->getAttribute('Type');
            $TE_Time = $taoEvent->getAttribute('Time');

            //Decoder le XML ou voir la section CDATA,
            $value = $taoEvent->nodeValue;
            //echo $value;
            $T_Values = array();
            $T_Values = $this->parseEventTraceValue($value);

            //Création du tableau des traces, il englobe toutes les variables ( tableau peyload + tableaux des attributs fixes)
            //$trace = $T_Values;

            $trace['name'] = $TE_Name;
            $trace['type'] = $TE_Type;
            $trace['time'] = $TE_Time;

            //Merge the tow arrays ( attribute + node values )
            $tout = array_merge($trace, $T_Values);
            $tracesArray[] = $tout;
        }

        return $tracesArray;
    }

    //******************************************************
    //Cette methode reçoit la valeur du noeud itemValue comme string, et la decode en variable en retournant le type d'evenement
    private function parseEventTraceValue($itemTraceValue) {

        $tabVarFinal = array();
        if ($itemTraceValue != '') {

            $itv = $itemTraceValue; //recuperer le string de la valeur;
            $tokenVar = array();
            $tabPart = explode(sep, $itv);
            $tabVarFinal = array();
            foreach ($tabPart as $part) {
                //parse_str($part, $tokenVar); //in  each element of the array we have this: var=value, we need to have $tokenVar[var]=value
                //we remplace pers_str with exploe because it ascapes directely some symbols
                $varValue = explode('=', $part);
                $tokenVar[$varValue[0]] = $varValue[1];
                $tabVarFinal = array_merge($tabVarFinal, $tokenVar);
            }
        }
        
        return $tabVarFinal;
    }

}

//test the converion



//file_put_contents('taoHawai.xml', $import);
?>
