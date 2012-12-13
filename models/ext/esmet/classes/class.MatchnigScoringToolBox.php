<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of class
 *
 * @author djaghloul
 */
require_once 'esmet_config.php';

class matchnigScoringToolBox {

    public $taoTraces;

    public function __construct($taoEvents) {
        $this->taoTraces = $taoEvents;
    }

    public function finaleStateOfEvents_CheckBoxStyle($eventCriterias, $resetPattern= "(noReset)") {
        //
        $states = array();
        foreach ($eventCriterias as $patternName => $patternQuery) {
            //create the symbol
            $nameOfVariable = $patternName;
            //get thje laste state for this pattern
            $value = $this->lastStateOfEvent_CheckBoxStyle(symbolFactory::create('Y', $patternQuery), symbolFactory::create('R', $resetPattern));
            //save the state of the proposed pattern
            $states[$nameOfVariable] = $value;
        }
        return $states;
    }

    //put your code here

    public function lastStateOfEvent_CheckBoxStyle(symbolDescription $symbol, symbolDescription $resetSymbol, $initialState = 0) {
        // according to the event criteria on create the Symbolized log
        $eFact = new eventsFactory($this->taoTraces);
        //add symbol collection
        $symbolFact = new symbolFactory();
        $symbolFact->addSymbol($symbol);
        $symbolFact->addSymbol($resetSymbol);

        $eFact->fullSymbolization($symbolFact->getSymbolCollection());

        $symbolizedTraces = $eFact->generateSymbolizedEvents();

        // the last value depends on checkBox behavior, Initial value = 0 and an occurence put 1 another occurence put 0
        $value = $initialState; // one can provides an initial state with active value
        $length = strlen($symbolizedTraces);
        $i = 0;
        $symbolChar = $symbol->symbolLetter;
        $resetChar = $resetSymbol->symbolLetter;
        while ($i < $length) {
            $s = substr($symbolizedTraces, $i, 1);
            if ($s == $symbolChar) {
                $value++;
            }
            if ($s == $resetChar) {
                $value = 0;
            }
            $i++;
        }//end loop

        $lastValue = $value % 2;

        return $lastValue;
    }

}
?>
