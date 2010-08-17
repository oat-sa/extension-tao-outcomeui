<?php
require_once 'esmet_config.php';


class symbolDescription {

    public $symbolLetter;
    public $query;
    public $comment;

    //set symbol information
    public function __construct($symbolLetter, $patternQuery, $symbolComment='') {

        $this->symbolLetter = $symbolLetter;
        $this->query = $patternQuery;
        $this->comment = $symbolComment;
    }

    public function setSymbolDescription($symbolLetter, $patternQuery, $symbolComment='') {

        $this->$symbolLetter = $symbolLetter;
        $this->query = $patternQuery;
        $this->comment = $symbolComment;
    }

}
?>
