<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Types\InterfaceType;
use DCarbone\JSONToGO\Types\MapType;
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\SliceType;
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
     * @param string $typeName
     * @param mixed $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseType(Configuration $configuration, $typeName, $typeExample, $parent = null)
    {
        $goType = $configuration->callbacks()->goType($configuration, $typeName, $typeExample, $parent);
        switch($goType)
        {
            case 'struct':
                if ($configuration->emptyStructToInterface() && 0 === count(get_object_vars($typeExample)))
                    $type = new InterfaceType($configuration, $typeName, $typeExample, $goType, $parent);
                else
                    $type = static::parseStructType($configuration, $typeName, $typeExample, $parent);
                break;

            case 'map':
                $type = static::parseMapType($configuration, $typeName, $typeExample, $parent);
                break;

            case 'slice':
                $type = static::parseSliceType($configuration, $typeName, $typeExample, $parent);
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
     * @param string $typeName
     * @param \stdClass $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public static function parseStructType(Configuration $configuration,
                                           $typeName,
                                           \stdClass $typeExample,
                                           $parent = null)
    {
        $structType = new StructType($configuration, $typeName, $typeExample);

        foreach(get_object_vars($typeExample) as $childTypeName => $childTypeExample)
        {
            $structType->addChild(static::parseType($configuration, $childTypeName, $childTypeExample, $structType));
        }

        return $structType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param \stdClass $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return \DCarbone\JSONToGO\Types\MapType
     */
    public static function parseMapType(Configuration $configuration,
                                        $typeName,
                                        \stdClass $typeExample,
                                        $parent = null)
    {
        $mapType = new MapType($configuration, $typeName, $typeExample, $parent);

        $varList = get_object_vars($typeExample);
        $firstType = $configuration->callbacks()->goType($configuration, key($varList), reset($varList), $mapType);

        if (1 === count($varList))
        {
            $type = static::parseType($configuration, $typeName, reset($varList), $mapType);
        }
        else
        {
            $same = true;
            foreach($varList as $k => $v)
            {
                $thisType = $configuration->callbacks()->goType($configuration, $typeName, $v, $mapType);
                if ($firstType !== $thisType)
                {
                    $same = false;
                    break;
                }
            }

            if ($same)
                $type = static::parseType($configuration, $typeName, reset($varList), $mapType);
            else
               $type = new InterfaceType($configuration, $typeName, reset($varList));

        }

        $mapType->setMapType($type);

        return $mapType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param array $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return \DCarbone\JSONToGO\Types\SliceType
     */
    public static function parseSliceType(Configuration $configuration, $typeName, array $typeExample, $parent = null)
    {
        $sliceType = new SliceType($configuration, $typeName, $typeExample, $parent);

        $sliceGoType = null;
        $sliceLength = count($typeExample);

        foreach($typeExample as $item)
        {
            $thisType = $configuration->callbacks()->goType($configuration, $typeName, $item, $sliceType);

            if (null === $sliceGoType)
            {
                $sliceGoType = $thisType;
            }
            else if ($sliceGoType !== $thisType)
            {
                $sliceGoType = $configuration->callbacks()->mostSpecificPossibleGoType($thisType, $sliceGoType);
                if ('interface{}' === $sliceGoType)
                    break;
            }
        }

        if ('struct' === $sliceGoType || 'map' === $sliceGoType)
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

            if ('struct' === $sliceGoType && $configuration->emptyStructToInterface() && 0 === count($allFields))
            {
                $type = new InterfaceType($configuration, $typeName, $typeExample);
            }
            else
            {
                $childTypeExample = new \stdClass();

                $omitempty = [];
                foreach(array_keys($allFields) as $key)
                {
                    $childTypeExample->$key = $allFields[$key]['value'];
                    $omitempty[$key] = $allFields[$key]['count'] !== $sliceLength;
                }

                if ('struct' === $sliceGoType)
                {
                    $type = static::parseStructType($configuration, $typeName, $childTypeExample, $sliceType);
                    foreach($type->children() as $child)
                    {
                        if ($omitempty[$child->name()])
                            $child->notAlwaysDefined();
                    }
                }
                else
                {
                    $type = static::parseMapType($configuration, $typeName, $childTypeExample, $sliceType);
                }
            }
        }
        else if ('slice' === $sliceGoType)
        {
            $type = static::parseType($configuration, $typeName, reset($typeExample), $sliceType);
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

        $sliceType->setSliceType($type);

        return $sliceType;
    }
}