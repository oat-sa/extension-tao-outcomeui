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
 * Copyright (c) 2018 Open Assessment Technologies S.A.
 */

namespace oat\taoOutcomeUi\test\unit\model\fixture;


trait ResultServiceTestFixtureTrait
{
    private $resultIdentifierFixture = 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375';

    private $epochAFixture = '0.96196000 1542124217';
    private $epochAFloatFixture = 1542124217.96196000;
    private $epochBFixture = '0.67674700 1542124722';
    private $epochBFloatFixture = 1542124722.67674700;

    protected function getItemVariableFirstAttemptAttempt()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'MQ==',
                'identifier' => 'numAttempts',
                'cardinality' => 'single',
                'baseType' => 'integer',
                'epoch' => '0.96196000 1542124217',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableSecondAttemptAttempt()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'Mg==',
                'identifier' => 'numAttempts',
                'cardinality' => 'single',
                'baseType' => 'integer',
                'epoch' => '0.67674700 1542124722',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableThirdAttemptAttempt()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'Mw==',
                'identifier' => 'numAttempts',
                'cardinality' => 'single',
                'baseType' => 'integer',
                'epoch' => '0.17111300 1542124889',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableFirstAttemptResponse()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'RCBQ',
                'identifier' => 'RESPONSE',
                'cardinality' => 'single',
                'baseType' => 'directedPair',
                'epoch' => '0.16842800 1542124218',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableSecondAttemptResponse()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'RCBQ',
                'identifier' => 'RESPONSE',
                'cardinality' => 'single',
                'baseType' => 'directedPair',
                'epoch' => '0.89667500 1542124722',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableThirdAttemptResponse()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getResponseVariable([
                'correctResponse' => null,
                'candidateResponse' => 'RCBQ',
                'identifier' => 'RESPONSE',
                'cardinality' => 'single',
                'baseType' => 'directedPair',
                'epoch' => '0.33639000 1542124889',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_ResponseVariable',
        ];
    }

    protected function getItemVariableAttemptAttemptsOrdered()
    {
        return [
            $this->getItemVariableFirstAttemptAttempt(),
            $this->getItemVariableSecondAttemptAttempt(),
            $this->getItemVariableThirdAttemptAttempt(),
        ];
    }

    protected function getItemVariableAttemptResponsesOrdered()
    {
        return [
            $this->getItemVariableFirstAttemptResponse(),
            $this->getItemVariableSecondAttemptResponse(),
            $this->getItemVariableThirdAttemptResponse(),
        ];
    }

    protected function getItemVariableStructureOrdered()
    {
        return [
            $this->getItemVariableAttemptAttemptsOrdered(),
            $this->getItemVariableAttemptResponsesOrdered(),
        ];
    }

    protected function getItemVariableStructureShuffled()
    {
        return [
            [
                $this->getItemVariableThirdAttemptAttempt(),
                $this->getItemVariableFirstAttemptAttempt(),
                $this->getItemVariableSecondAttemptAttempt(),
            ],
            [
                $this->getItemVariableSecondAttemptResponse(),
                $this->getItemVariableThirdAttemptResponse(),
                $this->getItemVariableFirstAttemptResponse(),
            ],
        ];
    }

    /**
     * Returns a configured response variable.
     *
     * @param $correctResponse
     *
     * @return \taoResultServer_models_classes_ResponseVariable
     */
    protected function getResponseVariableWithCorrectResponse($correctResponse)
    {
        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setCorrectResponse($correctResponse);

        return $variable;
    }

    /**
     * Returns a configured response variable.
     *
     * @param $epoch
     *
     * @return \taoResultServer_models_classes_ResponseVariable
     */
    protected function getResponseVariableWithEpoch($epoch)
    {
        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setEpoch($epoch);

        return $variable;
    }

    /**
     * Returns a configured response variable.
     *
     * @param $variable
     * @param $correctResponse
     * @param $candidateResponse
     * @param $identifier
     * @param $cardinality
     * @param $baseType
     * @param $epoch
     *
     * @return \taoResultServer_models_classes_ResponseVariable
     *
     * @throws \common_exception_InvalidArgumentType
     */
    protected function getResponseVariable($variable = [], $correctResponse = '', $candidateResponse= '', $identifier = '', $cardinality = '', $baseType = '', $epoch = '')
    {
        if (!empty($variable)) {
            extract($variable);
        }

        $variable = new \taoResultServer_models_classes_ResponseVariable();
        $variable->setCorrectResponse($correctResponse);
        $variable->setCandidateResponse($candidateResponse);
        $variable->setIdentifier($identifier);
        $variable->setCardinality($cardinality);
        $variable->setBaseType($baseType);
        $variable->setEpoch($epoch);

        return $variable;
    }
}
