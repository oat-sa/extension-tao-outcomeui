<?php
/**  
 * This program is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License
 * as published by the Free Software Foundation; under version 2
 * of the License (non-upgradable).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 * 
 * Copyright (c) 2009-2012 (original work) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 *               
 * 
 */

namespace oat\taoOutcomeUi\model\table;

use \tao_models_classes_table_Column;

/**
 * Short description of class oat\taoOutcomeUi\model\table\VariableColumn
 *
 * @abstract
 * @access public
 * @author Joel Bout, <joel.bout@tudor.lu>
 * @package taoOutcomeUi
 */
abstract class VariableColumn
    extends tao_models_classes_table_Column
{
    // --- ATTRIBUTES ---
    /**
     * Identifier of the variable
     *
     * @var string
     */
    public $identifier = '';

    /**
     * The identifier of the context usually correpsonds to the item URI
     *
     * @var string
     */
    public $contextIdentifier = '';

    /**
     * The label of the context usually coresponds to the item label
     *
     * @var string
     */
    public $contextLabel = '';

    /**
     * shared data providider to cache the variables
     * 
     * @var VariableDataProvider
     */
    public $dataProvider = null;

   
    // --- OPERATIONS ---

    /**
     * 
     * @param array $array
     * @return \oat\taoOutcomeUi\model\table\VariableColumn
     */
    protected static function fromArray($array)
    {
        $returnValue = null;

        
        $contextId = $array['contextId'];
        $contextLabel = $array['contextLabel'];
        $variableIdentifier =  $array['variableIdentifier'];
		$returnValue = new static($contextId, $contextLabel, $variableIdentifier);
        

        return $returnValue;
    }

    /**
     * 
     * @param string $contextIdentifier
     * @param string $contextLabel
     * @param string $identifier
     */
    public function __construct( $contextIdentifier,$contextLabel, $identifier)
    {
        parent::__construct( $contextLabel. "-" .$identifier);
        $this->identifier = $identifier;
        $this->contextIdentifier = $contextIdentifier;
        $this->contextLabel = $contextLabel;
        
    }

    public function setDataProvider(VariableDataProvider $provider)
    {
        $this->dataProvider = $provider;
    }
    
    /**
     * Short description of method getDataProvider
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return tao_models_classes_table_DataProvider
     */
    public function getDataProvider()
    {
        return $this->dataProvider;
    }

    /**
     * Short description of method getContextIdentifier
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return core_kernel_classes_Resource
     */
    public function getContextIdentifier()
    {
        return $this->contextIdentifier;
    }

    /**
     * Short description of method getIdentifier
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return string
     */
    public function getIdentifier()
    {
        return $this->identifier;
    }

    /**
     * Short description of method toArray
     *
     * @access public
     * @author Joel Bout, <joel.bout@tudor.lu>
     * @return array
     */
    public function toArray()
    {
        $returnValue = parent::toArray();
        //$returnValue['ca'] = "deprecated";
        $returnValue['contextId'] = $this->contextIdentifier;
        $returnValue['contextLabel'] = $this->contextLabel;
        $returnValue['variableIdentifier'] = $this->identifier;
        

        return (array) $returnValue;
    }
    
    /**
     * Returns the variable type of the column 
     * 
     * @return string
     */
    public abstract function getVariableType(); 

}