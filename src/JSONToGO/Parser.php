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
     * @param bool $root
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseType(Configuration $configuration, $typeExample, $typeName, $root = false)
    {
        list($goType, $typeExample) = Typer::goType($configuration, $typeExample);

        switch($goType)
        {
            case 'struct':
                $type = static::parseStructType($configuration, $typeExample, $typeName, $root);
                break;

            case 'slice':
                $type = static::parseSliceType($configuration, $typeExample, $typeName, $root);
                break;

            case 'interface{}':
                $type = new InterfaceType($configuration, $typeName, $typeExample, $root);
                break;

            default:
                $type = new SimpleType($configuration, $typeName, $typeExample, $goType, $root);
        }

        if ($root)
            $type->root();

        return $type;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \stdClass $typeExample
     * @param string $typeName
     * @param bool $root
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public static function parseStructType(Configuration $configuration,
                                           \stdClass $typeExample,
                                           $typeName,
                                           $root = false)
    {
        $structType = new StructType($configuration, $typeName, $typeExample, $root);

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
     * @param bool $root
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseSliceType(Configuration $configuration, array $typeExample, $typeName, $root = false)
    {
        $sliceGoType = null;
        $sliceLength = count($typeExample);

        foreach($typeExample as $item)
        {
            list($thisType) = Typer::goType($configuration, $item);

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
                    list($_, $value) = Typer::goType($configuration, $value);

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

            if ($configuration->emptyStructToInterface() && 0 === count($allFields))
            {
                $type = new InterfaceType($configuration, $typeName, $typeExample, $root);
            }
            else
            {
                $structExample = new \stdClass();

                $omitempty = [];
                foreach(array_keys($allFields) as $key)
                {
                    $structExample->$key = $allFields[$key]['value'];
                    $omitempty[$key] = $allFields[$key]['count'] !== $sliceLength;
                }

                $type = static::parseStructType($configuration, $structExample, $typeName, $root);
                foreach($type->children() as $child)
                {
                    if ($omitempty[$child->name()])
                        $child->notAlwaysDefined();
                }
            }
        }
        else if ('slice' === $sliceGoType)
        {
            $type = static::parseType($configuration, reset($typeExample), $typeName);
        }
        else if ('interface{}' === $sliceGoType)
        {
            $type = new InterfaceType($configuration, $typeName, $typeExample, $root);
        }
        else
        {
            if ($sliceGoType)
                $type = new SimpleType($configuration, $typeName, $typeExample, $sliceGoType, $root);
            else
                $type = new InterfaceType($configuration, $typeName, $typeExample, $root);
        }

        $type->collection();

        return $type;
    }
}