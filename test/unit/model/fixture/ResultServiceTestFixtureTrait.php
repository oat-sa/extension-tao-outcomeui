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
                'correctResponse' => false,
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
                'correctResponse' => false,
                'candidateResponse' => 'RCBD',
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
                'correctResponse' => false,
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

    protected function getItemVariableFirstAttemptScore()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getOutcomeVariable([
                'normalMaximum' => null,
                'normalMinimum' => null,
                'value' => 'MA==',
                'identifier' => 'SCORE',
                'cardinality' => 'single',
                'baseType' => 'float',
                'epoch' => '0.10640200 1542124218',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
    }

    protected function getItemVariableSecondAttemptScore()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getOutcomeVariable([
                'normalMaximum' => null,
                'normalMinimum' => null,
                'value' => 'MA==',
                'identifier' => 'SCORE',
                'cardinality' => 'single',
                'baseType' => 'float',
                'epoch' => '0.84401000 1542124722',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
        ];
    }

    protected function getItemVariableThirdAttemptScore()
    {
        return (object)[
            'deliveryResultIdentifier' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375',
            'test' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1542124156831368-',
            'item' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
            'variable' => $this->getOutcomeVariable([
                'normalMaximum' => null,
                'normalMinimum' => null,
                'value' => 'MA==',
                'identifier' => 'SCORE',
                'cardinality' => 'single',
                'baseType' => 'float',
                'epoch' => '0.29765300 1542124889',
            ]),
            'callIdItem' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0',
            'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
            'callIdTest' => null,
            'class' => 'taoResultServer_models_classes_OutcomeVariable',
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

    protected function getItemVariableAttemptScoresOrdered()
    {
        return [
            $this->getItemVariableFirstAttemptScore(),
            $this->getItemVariableSecondAttemptScore(),
            $this->getItemVariableThirdAttemptScore(),
        ];
    }

    protected function getItemVariableStructureOrdered()
    {
        return [
            $this->getItemVariableAttemptAttemptsOrdered(),
            $this->getItemVariableAttemptScoresOrdered(),
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
                $this->getItemVariableSecondAttemptScore(),
                $this->getItemVariableThirdAttemptScore(),
                $this->getItemVariableFirstAttemptScore(),
            ],
            [
                $this->getItemVariableSecondAttemptResponse(),
                $this->getItemVariableThirdAttemptResponse(),
                $this->getItemVariableFirstAttemptResponse(),
            ],
        ];
    }

    protected function getStructuredItemVariableStructureOrdered()
    {
        return [
            '0.96196000 1542124217' => [
                'itemModel' => '---',
                'label' => 'Associate Things',
                'uri' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
                'internalIdentifier' => 'item-1',
                'taoResultServer_models_classes_ResponseVariable' => [
                    'numAttempts' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => null,
                            'candidateResponse' => 'MQ==',
                            'identifier' => 'numAttempts',
                            'cardinality' => 'single',
                            'baseType' => 'integer',
                            'epoch' => '0.96196000 1542124217',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                    'RESPONSE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => false,
                            'candidateResponse' => 'RCBQ',
                            'identifier' => 'RESPONSE',
                            'cardinality' => 'single',
                            'baseType' => 'directedPair',
                            'epoch' => '0.16842800 1542124218',
                        ]),
                        'isCorrect' => 'incorrect',
                    ],
                ],
                'taoResultServer_models_classes_OutcomeVariable' => [
                    'SCORE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
                        'var' => $this->getOutcomeVariable([
                            'normalMaximum' => null,
                            'normalMinimum' => null,
                            'value' => 'MA==',
                            'identifier' => 'SCORE',
                            'cardinality' => 'single',
                            'baseType' => 'float',
                            'epoch' => '0.10640200 1542124218',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                ],
            ],
            '0.67674700 1542124722' => [
                'itemModel' => '---',
                'label' => 'Associate Things',
                'uri' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
                'internalIdentifier' => 'item-1',
                'taoResultServer_models_classes_ResponseVariable' => [
                    'numAttempts' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => null,
                            'candidateResponse' => 'Mg==',
                            'identifier' => 'numAttempts',
                            'cardinality' => 'single',
                            'baseType' => 'integer',
                            'epoch' => '0.67674700 1542124722',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                    'RESPONSE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => false,
                            'candidateResponse' => 'RCBD',
                            'identifier' => 'RESPONSE',
                            'cardinality' => 'single',
                            'baseType' => 'directedPair',
                            'epoch' => '0.89667500 1542124722',
                        ]),
                        'isCorrect' => 'incorrect',
                    ],
                ],
                'taoResultServer_models_classes_OutcomeVariable' => [
                    'SCORE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
                        'var' => $this->getOutcomeVariable([
                            'normalMaximum' => null,
                            'normalMinimum' => null,
                            'value' => 'MA==',
                            'identifier' => 'SCORE',
                            'cardinality' => 'single',
                            'baseType' => 'float',
                            'epoch' => '0.84401000 1542124722',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                ],
            ],
            '0.17111300 1542124889' => [
                'itemModel' => '---',
                'label' => 'Associate Things',
                'uri' => 'http://www.taotesting.com/ontologies/ionut.rdf#i1532536164849216',
                'internalIdentifier' => 'item-1',
                'taoResultServer_models_classes_ResponseVariable' => [
                    'numAttempts' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_numAttempts',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => null,
                            'candidateResponse' => 'Mw==',
                            'identifier' => 'numAttempts',
                            'cardinality' => 'single',
                            'baseType' => 'integer',
                            'epoch' => '0.17111300 1542124889',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                    'RESPONSE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_RESPONSE',
                        'var' => $this->getResponseVariable([
                            'correctResponse' => false,
                            'candidateResponse' => 'RCBQ',
                            'identifier' => 'RESPONSE',
                            'cardinality' => 'single',
                            'baseType' => 'directedPair',
                            'epoch' => '0.33639000 1542124889',
                        ]),
                        'isCorrect' => 'incorrect',
                    ],
                ],
                'taoResultServer_models_classes_OutcomeVariable' => [
                    'SCORE' => [
                        'uri' => 'kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375kve_de_http://www.taotesting.com/ontologies/ionut.rdf#i15421242071985375.item-1.0_prop_SCORE',
                        'var' => $this->getOutcomeVariable([
                            'normalMaximum' => null,
                            'normalMinimum' => null,
                            'value' => 'MA==',
                            'identifier' => 'SCORE',
                            'cardinality' => 'single',
                            'baseType' => 'float',
                            'epoch' => '0.29765300 1542124889',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns a configured response variable.
     *
     * @param $correctResponse
     *
     * @return \taoResultServer_models_classes_ResponseVariable
     *
     * @throws \common_exception_InvalidArgumentType
     */
    protected function getResponseVariableWithCorrectResponse($correctResponse)
    {
        return $this->getResponseVariable(['correctResponse' => $correctResponse]);
    }

    /**
     * Returns a configured response variable.
     *
     * @param $epoch
     *
     * @return \taoResultServer_models_classes_ResponseVariable
     *
     * @throws \common_exception_InvalidArgumentType
     */
    protected function getResponseVariableWithEpoch($epoch)
    {
        return $this->getResponseVariable(['epoch' => $epoch]);
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
    protected function getResponseVariable($variable = [], $correctResponse = '', $candidateResponse= '', $identifier = '', $cardinality = \taoResultServer_models_classes_Variable::CARDINALITY_SINGLE, $baseType = '', $epoch = '')
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

    protected function getOutcomeVariable($variable = [], $identifier = '', $maximum = 0, $minimum = 0, $value = '', $cardinality = \taoResultServer_models_classes_Variable::CARDINALITY_SINGLE, $baseType = '', $epoch = '')
    {
        if (!empty($variable)) {
            extract($variable);
        }

        $variable = new \taoResultServer_models_classes_OutcomeVariable();
        $variable->setIdentifier($identifier);
        $variable->setNormalMaximum($maximum);
        $variable->setNormalMinimum($minimum);
        $variable->setValue(base64_decode($value));
        $variable->setCardinality($cardinality);
        $variable->setBaseType($baseType);
        $variable->setEpoch($epoch);

        return $variable;
    }
}
