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

use oat\tao\test\TaoPhpUnitTestRunner;
use oat\taoOutcomeUi\helper\ResponseVariableFormatter;
use \taoResultServer_models_classes_ResponseVariable as ResponseVariable;

class ResponseVariableFormatterTest extends TaoPhpUnitTestRunner
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
        $value = " Pablo Ruiz y Picasso, also known as Pablo Picasso (Spanish: [ˈpaβlo piˈkaso]; 25 October 1881 – 8 April 1973), was a Spanish painter, sculptor, printmaker, ceramicist, stage designer, poet and playwright who spent most of his adult life in France. As one of the greatest and most influential artists of the 20th<br /> ";
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

        $expected = ['list' => ['pair' => [['choice_1', 'choice_3'], ['choice_2', 'choice_4'], ['choice_1', 'choice_5'], ['choice_2', 'choice_6']]]];

        $this->assertEquals($expected, ResponseVariableFormatter::formatVariableToPci($var));
    }
}