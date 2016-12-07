<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Types\InterfaceType;
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\StructType;

/**
 * Class Parser
 *
 * @package DCarbone\JSONToGO
 */
abstract class Parser
{
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param mixed $typeExample
     * @param string $typeName
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseType(Configuration $configuration, $typeExample, $typeName)
    {
        switch($goType = Typer::goType($configuration, $typeExample))
        {
            case 'struct':
                $type = static::parseStructType($configuration, $typeExample, $typeName);
                break;

            case 'slice':
                $type = static::parseSliceType($configuration, $typeExample, $typeName);
                break;

            case 'interface{}':
                $type = new InterfaceType($configuration, $typeName, $typeExample);
                break;

            default:
                $type = new SimpleType($configuration, $typeName, $typeExample, $goType);
        }

        return $type;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \stdClass $typeExample
     * @param string $typeName
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public static function parseStructType(Configuration $configuration, \stdClass $typeExample, $typeName)
    {
        $structType = new StructType($configuration, $typeName, $typeExample);

        foreach(get_object_vars($typeExample) as $key => $value)
        {
            $structType->addChild(static::parseType($configuration, $value, $key));
        }

        return $structType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param array $typeExample
     * @param string $typeName
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseSliceType(Configuration $configuration, array $typeExample, $typeName)
    {
        $sliceGoType = null;
        $sliceLength = count($typeExample);

        foreach($typeExample as $item)
        {
            $thisType = Typer::goType($configuration, $item);

            if (null === $sliceGoType)
            {
                $sliceGoType = $thisType;
            }
            else if ($sliceGoType !== $thisType)
            {
                $sliceGoType = Typer::mostSpecificPossibleGoType($thisType, $sliceGoType);
                if ('interface{}' === $sliceGoType)
                    break;
            }
        }

        if ('struct' === $sliceGoType)
        {
            $allFields = [];

            foreach($typeExample as $item)
            {
                foreach(get_object_vars($item) as $key => $value)
                {
                    if (!isset($allFields[$key]))
                    {
                        $allFields[$key] = [
                            'value' => $value,
                            'count' => 0,
                        ];
                    }

                    $allFields[$key]['count']++;
                }
            }

            $structExample = new \stdClass();

            $omitempty = [];
            foreach(array_keys($allFields) as $key)
            {
                $structExample->$key = $allFields[$key]['value'];
                $omitempty[$key] = $allFields[$key]['count'] !== $sliceLength;
            }

            $type = static::parseStructType($configuration, $structExample, $typeName);
        }
        else if ('slice' === $sliceGoType)
        {
            $type = static::parseType($configuration, reset($typeExample), $typeName);
        }
        else if ('interface{}' === $sliceGoType)
        {
            $type = new InterfaceType($configuration, $typeName, $typeExample);
        }
        else
        {
            if ($sliceGoType)
                $type = new SimpleType($configuration, $typeName, $typeExample, $sliceGoType);
            else
                $type = new InterfaceType($configuration, $typeName, $typeExample);
        }

        $type->collection();

        return $type;
    }
}