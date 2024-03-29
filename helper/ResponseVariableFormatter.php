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

namespace oat\taoOutcomeUi\helper;

use taoResultServer_models_classes_ResponseVariable as ResponseVariable;

/**
 * The ResponseVariableFormatter enables you to format the output of the ResultsService into an associative compatible
 * with the client code
 * Class ResponseVariableFormatter
 * @package oat\taoOutcomeUi\helper
 */
class ResponseVariableFormatter
{
    /**
     * Special trim for identifier in response variables
     * @param $identifierString
     * @return string
     */
    private static function trimIdentifier($identifierString)
    {
        return trim($identifierString, " \t\n\r\0\x0B'\"");
    }

    /**
     * Format a string into the appropriate format according to the base type
     * @param $baseType
     * @param $stringValue
     * @return array|bool|float|int|string
     * @throws \common_Exception
     */
    private static function formatStringValue($baseType, $stringValue)
    {

        switch (strtolower($baseType)) {
            case 'string':
            case 'duration':
                return $stringValue;
            case 'identifier':
                return self::trimIdentifier($stringValue);
            case 'integer':
                return intval($stringValue);
            case 'float':
                return floatval($stringValue);
            case 'boolean':
                return (trim($stringValue) === 'true' || $stringValue === true) ? true : false;
            case 'pair':
            case 'directedpair':
                $pair = explode(' ', trim($stringValue));
                if (count($pair) != 2) {
                    throw new \common_Exception('invalid pair string');
                }
                return [self::trimIdentifier($pair[0]), self::trimIdentifier($pair[1])];
            case 'point':
                $pair = explode(' ', trim($stringValue));
                if (count($pair) != 2) {
                    throw new \common_Exception('invalid point string');
                }
                return [intval($pair[0]), intval($pair[1])];
            default:
                throw new \common_exception_NotImplemented('unknown basetype');
        }
    }

    /**
     * Format a ResponseVariable into a associative array, directly usable on the client side.
     *
     * @param ResponseVariable $var
     * @return array
     * @throws \common_Exception
     */
    public static function formatVariableToPci(ResponseVariable $var)
    {
        $value = $var->getValue();
        switch ($var->getCardinality()) {
            case 'record':
            case 'single':
                if (strlen($value) === 0) {
                    $formatted = ['base' => null];
                } else {
                    try {
                        $formatted = [
                            'base' => [$var->getBaseType() => self::formatStringValue($var->getBaseType(), $value)]
                        ];
                    } catch (\common_exception_NotImplemented $e) {
                        // simply ignore unsupported data/type
                        $formatted = ['base' => null];
                    }
                }
                break;
            case 'ordered':
            case 'multiple':
                $list = [];

                if (!empty($value) && preg_match('/^\s*[\[|<](.*)[\]>]\s*$/', $value, $content)) {
                    $matches = explode(';', $content[1]);
                    foreach ($matches as $valueString) {
                        if (empty(trim($valueString))) {
                            continue;
                        }

                        try {
                            $list[] = self::formatStringValue($var->getBaseType(), trim($valueString, " '"));
                        } catch (\common_exception_NotImplemented $e) {
                            // simply ignore unsupported data/type
                        }
                    }
                }

                $formatted = ['list' => [$var->getBaseType() => $list]];
                break;
            default:
                throw new \common_Exception('unknown response cardinality');
        }
        return $formatted;
    }

    /**
     * Format the output of oat\taoOutcomeUi\model\ResultsService::getStructuredVariables() into a client usable array
     *
     * @param array $testResultVariables - the array output from
     *                                   oat\taoOutcomeUi\model\ResultsService::getStructuredVariables();
     * @param array $itemFilter = [] - the array of item uri to be included in the formatted output, all item if empty.
     * @return array
     * @throws \common_Exception
     */
    public static function formatStructuredVariablesToItemState(
        array $testResultVariables,
        array $itemFilter = []
    ): array {

        $formatted = [];
        foreach ($testResultVariables as $itemKey => $itemResult) {
            if (!isset($itemResult['uri'])) {
                continue;
            }

            if (!empty($itemFilter) && !in_array($itemResult['uri'], $itemFilter)) {
                continue;
            }

            $itemResults = [];
            foreach ($itemResult['taoResultServer_models_classes_ResponseVariable'] as $var) {
                /** @var $responseVariable ResponseVariable */
                $responseVariable = $var['var'];

                if ($responseVariable->getBaseType() === 'file') {
                    $itemResults[$responseVariable->getIdentifier()] = [
                        'response' => ['base' => ['file' => self::formatVariableFile($responseVariable)]]
                    ];
                } elseif ($responseVariable->getCardinality() === 'record') {
                    $itemResults[$responseVariable->getIdentifier()] = [
                        'response' => json_decode($responseVariable->getValue(), true)
                            ?? ['base' => '']
                    ];
                } else {
                    $itemResults[$responseVariable->getIdentifier()] = [
                        'response' => self::formatVariableToPci($responseVariable)
                    ];
                }
            }

            $formatted[$itemKey][$itemResult['attempt']] = $itemResults;
        }

        return $formatted;
    }

    private static function formatVariableFile(ResponseVariable $responseVariable): array
    {
        $file = Datatypes::decodeFile($responseVariable->getCandidateResponse());

        $file['data'] = base64_encode($file['data']);

        return $file;
    }
}
