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
require_once 'class.SymbolDescription.php';
class symbolFactory {
    private $symbolOfPatternCollection = array();

    public function  __construct() {
        $this->symbolOfPatternCollection= array();

    }
    //create the symbol
    public static function create ($symbolLetter,$patternQuery,$symbolComment='This symbol is ...'){
        $symbol = new symbolDescription($symbolLetter,$patternQuery,$symbolComment);
        return $symbol;
    }

    // add symbol the the list of symbols
    public function addSymbol(symbolDescription $symbol){
        $key = $symbol->symbolLetter;

        $this->symbolOdPattern[$key]= $symbol;
        return $this->symbolOfPatternCollection;
    }
    //get the current collection
    public function getSymbolCollection(){
        return $this->symbolOdPattern;
    }


}



?>
