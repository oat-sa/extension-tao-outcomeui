<?php
/*  
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
 * Copyright (c) 2002-2008 (original work) Public Research Centre Henri Tudor & University of Luxembourg (under the project TAO & TAO2);
 *               2008-2010 (update and modification) Deutsche Institut für Internationale Pädagogische Forschung (under the project TAO-TRANSFER);
 *               2009-2012 (update and modification) Public Research Centre Henri Tudor (under the project TAO-SUSTAIN & TAO-DEV);
 * 
 */
?>
<?php
/**
 *
 * @author CRP Henri Tudor - TAO Team - {@link http://www.tao.lu}
 * @license GPLv2  http://www.opensource.org/licenses/gpl-2.0.php
 *
 */
$todefine = array(

    //used to get information on result
    'TAO_DELIVERY_RESULT'			=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#DeliveryResult',
    'PROPERTY_RESULT_OF_SUBJECT'	=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfSubject',
    'PROPERTY_RESULT_OF_DELIVERY'	=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#resultOfDelivery',

    //used in data provider
    'TAO_RESULT_VARIABLE'			=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#Variable',

    //used in resultService / template
	'PROPERTY_VARIABLE_EPOCH'	=>'http://www.tao.lu/Ontologies/TAOResult.rdf#variableEpoch',
    'PROPERTY_IDENTIFIER'=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#Identifier',
    'PROPERTY_RELATED_ITEM_RESULT' =>  'http://www.tao.lu/Ontologies/TAOResult.rdf#relatedItemResult',
    'PROPERTY_VARIABLE_CARDINALITY' => 'http://www.tao.lu/Ontologies/TAOResult.rdf#cardinality',
    'PROPERTY_VARIABLE_BASETYPE'  => 'http://www.tao.lu/Ontologies/TAOResult.rdf#baseType',
    'PROPERTY_RESPONSE_VARIABLE_CORRECTRESPONSE'  =>'http://www.tao.lu/Ontologies/TAOResult.rdf#correctResponse',
    'PROPERTY_RESPONSE_VARIABLE_CANDIDATERESPONSE'  =>'http://www.tao.lu/Ontologies/TAOResult.rdf#candidateResponse',

    //used to get the variable type
    'CLASS_OUTCOME_VARIABLE'=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#OutcomeVariable',
    'CLASS_RESPONSE_VARIABLE'=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#ResponseVariable',
    'CLASS_TRACE_VARIABLE'=> 'http://www.tao.lu/Ontologies/TAOResult.rdf#TraceVariable'


);
?>