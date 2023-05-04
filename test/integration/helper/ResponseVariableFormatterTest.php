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
 * Copyright (c) 2017 (original work) Open Assessment Technologies SA (under the project TAO-PRODUCT);
 *
 */

namespace oat\taoOutcomeUi\test\unit\helper;

use oat\generis\test\TestCase;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use taoResultServer_models_classes_ResponseVariable as ResponseVariable;

class ResponseVariableFormatterTest extends TestCase
{
    public function testFormatSingleNull()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('single');

        $expected = ['base' => null];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatMultipleNull()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('multiple');

        $expected = ['list' => ['identifier' => []]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSingleIdentifier()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('single');
        $var->setValue('ABC');

        $expected = ['base' => ['identifier' => 'ABC']];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatRecordIdentifier()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('record');
        $var->setValue('ABC');

        $expected = ['base' => ['identifier' => 'ABC']];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatStructuredVariablesToItemState()
    {
        $result = ResponseVariableFormatter::formatStructuredVariablesToItemState([
            'key_value_id' => [
                'itemModel' => '---',
                'label' => 'Graphing Number Line',
                'uri' => 'some_uri',
                'isLocal' => true,
                'attempt' => '1',
                'internalIdentifier' => 'item-1',
                'taoResultServer_models_classes_ResponseVariable' => [
                    'numAttempts' => [
                        'uri' => 126,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'numAttempts',
                            'cardinality' => 'single',
                            'baseType' => 'integer',
                            'epoch' => '0.12392600 1666713760',
                            'type' => 'responseVariable',
                            'correctResponse' => null,
                            'candidateResponse' => 'MQ==',
                        ]),
                        'isCorrect' => ''
                    ],
                    'duration' => [
                        'uri' => 127,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'duration',
                            'cardinality' => 'single',
                            'baseType' => 'duration',
                            'epoch' => '0.12403000 1666713760',
                            'type' => 'responseVariable',
                            'correctResponse' => null,
                            'candidateResponse' => 'UFQzLjAwMjgwMFM=',
                        ]),
                    ],
                    'RESPONSE' => [
                        'uri' => 131,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'RESPONSE',
                            'cardinality' => 'record',
                            'baseType' => '',
                            'epoch' => '0.12410600 1666713760',
                            'type' => 'responseVariable',
                            'correctResponse' => false,
                            'candidateResponse' => 'eyJyZWNvcmQiOlt7Im5hbWUiOiJsaW5lVHlwZXMiLCJsaXN0Ijp7InN0cmluZyI6WyJ'
                                . 'jbG9zZWQtb3BlbiIsIm9wZW4tYXJyb3ciXX19LHsibmFtZSI6InZhbHVlcyIsImJhc2UiOnsic3RyaW5nIjo'
                                . 'iW1swLDFdLFswLG51bGxdXSJ9fV19',
                        ])
                    ],
                ],
                'taoResultServer_models_classes_OutcomeVariable' => [
                    'completionStatus' => [
                        'uri' => 128,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'completionStatus',
                            'cardinality' => 'single',
                            'baseType' => 'identifier',
                            'epoch' => '0.12405500 1666713760',
                            'type' => 'outcomeVariable',
                            'normalMinimum' => null,
                            'normalMaximum' => null,
                            'value' => 'Y29tcGxldGVk',
                        ]),
                        'isCorrect' => 'unscored',
                    ],
                    'SCORE' => [
                        'uri' => 129,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'SCORE',
                            'cardinality' => 'single',
                            'baseType' => 'float',
                            'epoch' => '0.12407500 1666713760',
                            'type' => 'outcomeVariable',
                            'normalMinimum' => null,
                            'normalMaximum' => null,
                            'value' => 'MA==',
                        ]),
                        'isCorrect' => 'unscored'
                    ],
                    'MAXSCORE' => [
                        'uri' => 130,
                        'var' => ResponseVariable::fromData([
                            'identifier' => 'MAXSCORE',
                            'cardinality' => 'single',
                            'baseType' => 'float',
                            'epoch' => '0.12409100 1666713760',
                            'type' => 'outcomeVariable',
                            'normalMinimum' => null,
                            'normalMaximum' => null,
                            'value' => 'MA==',
                        ]),
                        'isCorrect' => 'unscored'
                    ],
                ]
            ]
        ]);

        self::assertIsArray($result);
        self::assertEquals([
            'key_value_id' => [
                1 => [
                    'numAttempts' => [
                        'response' => [
                            'base' => [
                                'integer' => 1
                            ]
                        ]
                    ],
                    'duration' => [
                        'response' => [
                            'base' => [
                                'duration' => 'PT3.002800S'
                            ]
                        ]
                    ],
                    'RESPONSE' => [
                        'response' => [
                            'record' => [
                                [
                                    'name' => 'lineTypes',
                                    'list' => [
                                        'string' => ['closed-open', 'open-arrow']
                                    ]
                                ],
                                [
                                    'name' => 'values',
                                    'base' => [
                                        'string' => '[[0,1],[0,null]]'
                                    ]
                                ]
                            ]
                        ]
                    ]

                ]
            ]
        ], $result);
    }

    public function testFormatSinglePair()
    {
        $var = new ResponseVariable();
        $var->setBaseType('pair');
        $var->setCardinality('single');
        $var->setValue(' choice_2 choice_3 ');

        $expected = ['base' => ['pair' => ['choice_2', 'choice_3']]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSingleInteger()
    {
        $var = new ResponseVariable();
        $var->setBaseType('integer');
        $var->setCardinality('single');
        $var->setValue('0');

        $expected = ['base' => ['integer' => 0]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSingleFloat()
    {
        $var = new ResponseVariable();
        $var->setBaseType('float');
        $var->setCardinality('single');
        $var->setValue('3.14');

        $expected = ['base' => ['float' => 3.14]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSingleBoolean()
    {
        $var = new ResponseVariable();
        $var->setBaseType('boolean');
        $var->setCardinality('single');
        $var->setValue('true');

        $expected = ['base' => ['boolean' => true]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSinglePoint()
    {
        $var = new ResponseVariable();
        $var->setBaseType('point');
        $var->setCardinality('single');
        $var->setValue('10 90');

        $expected = ['base' => ['point' => [10, 90]]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatSingleString()
    {
        $var = new ResponseVariable();
        $var->setBaseType('string');
        $var->setCardinality('single');
        $value = " Pablo Ruiz y Picasso, also known as Pablo Picasso (Spanish: [ˈpaβlo piˈkaso]; 25 October 1881 – 8 "
            . "April 1973), was a Spanish painter, sculptor, printmaker, ceramicist, stage designer, poet and "
            . "playwright who spent most of his adult life in France. As one of the greatest and most influential "
            . "artists of the 20th<br /> ";
        $var->setValue($value);

        $expected = ['base' => ['string' => $value]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatMultipleIdentifier()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('multiple');
        $var->setValue("['hotspot_5'; 'hotspot_4'; 'hotspot_7'; 'hotspot_1']");

        $expected = ['list' => ['identifier' => ['hotspot_5', 'hotspot_4', 'hotspot_7', 'hotspot_1']]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatOrderedIdentifier()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('ordered');
        $var->setValue(" <'choice_1'; 'choice_3'; 'choice_2'> ");

        $expected = ['list' => ['identifier' => ['choice_1', 'choice_3', 'choice_2']]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testFormatMultiplePair()
    {
        $var = new ResponseVariable();
        $var->setBaseType('pair');
        $var->setCardinality('multiple');
        $var->setValue('[choice_1 choice_3; choice_2 choice_4; choice_1 choice_5; choice_2 choice_6]');

        $expected = [
            'list' => [
                'pair' => [
                    [
                        'choice_1',
                        'choice_3',
                    ],
                    [
                        'choice_2',
                        'choice_4',
                    ],
                    [
                        'choice_1',
                        'choice_5',
                    ],
                    [
                        'choice_2',
                        'choice_6',
                    ],
                ],
            ],
        ];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }

    public function testMultipleForMathEntry()
    {
        $var = new ResponseVariable();
        $var->setBaseType('identifier');
        $var->setCardinality('multiple');
        $var->setValue(" ['\sqrt{4}'; ''; '\cos'; '{1,2}'; '\sin'; ''; '\pi'; '[1]'; '<'] ");

        $expected = ['list' => ['identifier' => ['\sqrt{4}', '', '\cos', '{1,2}', '\sin', '', '\pi', '[1]', '<']]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }
}
