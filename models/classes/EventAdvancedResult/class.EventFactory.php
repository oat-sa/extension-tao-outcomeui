<?php

error_reporting(E_ALL);

/**
 * TAO - taoResults\models\classes\EventAdvancedResult\class.EventFactory.php
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 24.01.2011, 11:45:52 with ArgoUML PHP module 
 * (last revised $Date: 2010-01-12 20:14:42 +0100 (Tue, 12 Jan 2010) $)
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes_EventAdvancedResult
 */

if (0 > version_compare(PHP_VERSION, '5')) {
    die('This file was generated for PHP 5');
}

/**
 * include taoResults_models_classes_EventAdvancedResult_EventsServices
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/EventAdvancedResult/class.EventsServices.php');

/**
 * include taoResults_models_classes_EventAdvancedResult_SymbolDescription
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/EventAdvancedResult/class.SymbolDescription.php');

/* user defined includes */
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF6-includes begin
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF6-includes end

/* user defined constants */
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF6-constants begin
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF6-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes_EventAdvancedResult
 */
class taoResults_models_classes_EventAdvancedResult_EventFactory
    extends taoResults_models_classes_EventAdvancedResult_EventsServices
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    // --- OPERATIONS ---

    /**
     * prepare the document according to the tao event
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string taoEvents
     * @return mixed
     */
    public function __construct($taoEvents)
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF8 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AF8 end
    }

    /**
     * add an attribute with increment number from 1
     *
     * @access private
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return mixed
     */
    private function incEventNumber()
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFB begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFB end
    }

    /**
     * add symbol attribute
     *
     * @access private
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return mixed
     */
    private function addSymbolAttribute()
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFD begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFD end
    }

    /**
     * Symbolization of all the log as input, an array of symbols
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array patternSymbolCollection
     * @return mixed
     */
    public function fullSymbolization($patternSymbolCollection)
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFF begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002AFF end
    }

    /**
     * create the symbolized event Log
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @return string
     */
    public function generateSymbolizedEvents()
    {
        $returnValue = (string) '';

        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B02 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B02 end

        return (string) $returnValue;
    }

    /**
     * the symbolization method put the accurate pattern Symbol for event
     * to the query
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  SymbolDescription symbol
     * @return mixed
     */
    public function eventSymbolization( taoResults_models_classes_EventAdvancedResult_SymbolDescription $symbol)
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B09 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B09 end
    }

    /**
     * Short description of method saveEvents
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string fileName
     * @return mixed
     */
    public function saveEvents($fileName = "raffinedEvents.xml")
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B0D begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B0D end
    }

    /**
     * Short description of method matchingPatternMatching
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  strng patternToMatch
     * @param  string symbolizedTraces
     * @return boolean
     */
    public function matchingPatternMatching( taoResults_models_classes_EventAdvancedResult_strng $patternToMatch, $symbolizedTraces)
    {
        $returnValue = (bool) false;

        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B12 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B12 end

        return (bool) $returnValue;
    }

    /**
     * Short description of method getLastSymbolFromCollection
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  array symbolCollection
     * @return array
     */
    public function getLastSymbolFromCollection($symbolCollection)
    {
        $returnValue = array();

        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B18 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B18 end

        return (array) $returnValue;
    }

} /* end of class taoResults_models_classes_EventAdvancedResult_EventFactory */

?>