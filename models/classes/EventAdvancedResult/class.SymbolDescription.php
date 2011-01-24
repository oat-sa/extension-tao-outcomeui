<?php

error_reporting(E_ALL);

/**
 * TAO -
 *
 * $Id$
 *
 * This file is part of TAO.
 *
 * Automatically generated on 24.01.2011, 11:45:53 with ArgoUML PHP module 
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
 * include taoResults_models_classes_EventAdvancedResult_EventFactory
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/EventAdvancedResult/class.EventFactory.php');

/**
 * include taoResults_models_classes_EventAdvancedResult_SymbolFactory
 *
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 */
require_once('taoResults/models/classes/EventAdvancedResult/class.SymbolFactory.php');

/* user defined includes */
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B1D-includes begin
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B1D-includes end

/* user defined constants */
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B1D-constants begin
// section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B1D-constants end

/**
 * Short description of class
 *
 * @access public
 * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
 * @package taoResults
 * @subpackage models_classes_EventAdvancedResult
 */
class taoResults_models_classes_EventAdvancedResult_SymbolDescription
{
    // --- ASSOCIATIONS ---
    // generateAssociationEnd :     // generateAssociationEnd :     // generateAssociationEnd : 

    // --- ATTRIBUTES ---

    /**
     * Short description of attribute symbolLetter
     *
     * @access public
     * @var string
     */
    public $symbolLetter = '';

    /**
     * Short description of attribute query
     *
     * @access public
     * @var string
     */
    public $query = '';

    /**
     * Short description of attribute comment
     *
     * @access public
     * @var string
     */
    public $comment = '';

    // --- OPERATIONS ---

    /**
     * Short description of method __construct
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string symbolLetter
     * @param  string patternQuery
     * @param  string symbolComment
     * @return mixed
     */
    public function __construct($symbolLetter, $patternQuery, $symbolComment = '')
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B71 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B71 end
    }

    /**
     * set symbol information
     *
     * @access public
     * @author Younes Djaghloul, <younes.djaghloul@tudor.lu>
     * @param  string symbolLetter
     * @param  string patternQuery
     * @param  string symbolComment
     * @return mixed
     */
    public function setSymbolDescription($symbolLetter, $patternQuery, $symbolComment = '')
    {
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B73 begin
        // section 10-13-1--65--6ef728ed:12db72853fd:-8000:0000000000002B73 end
    }

} /* end of class taoResults_models_classes_EventAdvancedResult_SymbolDescription */

?>