<?php
require_once 'esmet_config.php';


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
        $this->symbolOfPatternCollection[$key]= $symbol;
        return $this->symbolOfPatternCollection;
    }
    //get the current collection
    public function getSymbolCollection(){
        return $this->symbolOfPatternCollection;
    }

}



?>
