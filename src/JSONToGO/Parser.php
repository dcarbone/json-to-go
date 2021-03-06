<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Types\InterfaceType;
use DCarbone\JSONToGO\Types\MapType;
use DCarbone\JSONToGO\Types\TypeParent;
use DCarbone\JSONToGO\Types\RawMessageType;
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\SliceType;
use DCarbone\JSONToGO\Types\StructType;
use DCarbone\JSONToGO\Types\Type;

/**
 * Class Parser
 *
 * @package DCarbone\JSONToGO
 */
abstract class Parser {
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param $typeExample
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function parseType(Configuration $configuration,
                                     string $typeName,
                                     $typeExample,
                                     TypeParent $parent = null): Type {
        $goType = $configuration->callbacks()->goType($configuration, $typeName, $typeExample, $parent);
        switch ($goType) {
            case GOTYPE_STRUCT:
                if ($configuration->emptyStructToInterface() && 0 === count(get_object_vars($typeExample))) {
                    $type = new InterfaceType($configuration, $typeName, $typeExample);
                } else {
                    $type = static::parseStructType($configuration, $typeName, $typeExample, $parent);
                }
                break;

            case GOTYPE_MAP:
                $type = static::parseMapType($configuration, $typeName, $typeExample, $parent);
                break;

            case GOTYPE_SLICE:
                $type = static::parseSliceType($configuration, $typeName, $typeExample, $parent);
                break;

            case GOTYPE_INTERFACE:
                $type = new InterfaceType($configuration, $typeName, $typeExample);
                break;

            case GOTYPE_RAWMESSAGE:
                $type = new RawMessageType($configuration, $typeName, $typeExample);
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
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public static function parseStructType(Configuration $configuration,
                                           string $typeName,
                                           \stdClass $typeExample,
                                           TypeParent $parent = null): StructType {
        $structType = new StructType($configuration, $typeName, $typeExample, $parent);

        foreach (get_object_vars($typeExample) as $childTypeName => $childTypeExample) {
            $structType->addChild(static::parseType($configuration, $childTypeName, $childTypeExample, $structType));
        }

        return $structType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param \stdClass $typeExample
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     * @return \DCarbone\JSONToGO\Types\MapType
     */
    public static function parseMapType(Configuration $configuration, string $typeName, \stdClass $typeExample, TypeParent $parent = null): MapType {
        $mapType = new MapType($configuration, $typeName, $typeExample, $parent);

        $varList = get_object_vars($typeExample);
        $firstType = $configuration->callbacks()->goType($configuration, $typeName, reset($varList), $mapType);

        if (1 === count($varList)) {
            $type = static::parseType($configuration, $typeName, reset($varList), $mapType);
        } else {
            $same = true;
            foreach ($varList as $k => $v) {
                $thisType = $configuration->callbacks()->goType($configuration, $typeName, $v, $mapType);
                if ($firstType !== $thisType) {
                    $same = false;
                    break;
                }
            }

            if ($same) {
                $type = static::parseType($configuration, $typeName, reset($varList), $mapType);
            } else {
                $type = new InterfaceType($configuration, $typeName, reset($varList));
            }

        }

        $mapType->setMapType($type);

        return $mapType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param array $typeExample
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     * @return \DCarbone\JSONToGO\Types\SliceType
     */
    public static function parseSliceType(Configuration $configuration,
                                          string $typeName,
                                          array $typeExample,
                                          TypeParent $parent = null): SliceType {
        $sliceType = new SliceType($configuration, $typeName, $typeExample, $parent);

        $sliceGoType = null;
        $sliceLength = count($typeExample);

        foreach ($typeExample as $item) {
            $thisType = static::parseType($configuration, $typeName, $item, $sliceType);

            if (null === $sliceGoType) {
                $sliceGoType = $thisType;
            } else {
                $sliceGoType = $configuration
                    ->callbacks()
                    ->mostSpecificPossibleGoType($configuration, $thisType, $sliceGoType);

                if ($sliceGoType instanceof InterfaceType) {
                    break;
                }
            }
        }

        if ($sliceGoType instanceof StructType || $sliceGoType instanceof MapType) {
            $allFields = [];

            foreach ($typeExample as $item) {
                foreach (get_object_vars($item) as $key => $value) {
                    if (!isset($allFields[$key])) {
                        $allFields[$key] = [
                            'value' => $value,
                            'count' => 0,
                        ];
                    }

                    $allFields[$key]['count']++;
                }
            }

            if ($sliceGoType instanceof StructType && $configuration->emptyStructToInterface() && 0 === count($allFields)) {
                $type = new InterfaceType($configuration, $typeName, $typeExample);
            } else {
                $childTypeExample = new \stdClass();

                $omitempty = [];
                foreach (array_keys($allFields) as $key) {
                    $childTypeExample->$key = $allFields[$key]['value'];
                    $omitempty[$key] = $allFields[$key]['count'] !== $sliceLength;
                }

                if ($sliceGoType instanceof StructType) {
                    $type = static::parseStructType($configuration, $typeName, $childTypeExample, $sliceType);
                    foreach ($type->fields() as $field) {
                        if ($omitempty[$field->name()]) {
                            $field->notAlwaysDefined();
                        }
                    }
                } else {
                    $type = static::parseMapType($configuration, $typeName, $childTypeExample, $sliceType);
                }
            }
        } else if ($sliceGoType instanceof SliceType) {
            if (2 > count($typeExample)) {
                // if there is no example or only one example, no further parsing is needed
                $type = static::parseType($configuration, $typeName, reset($typeExample), $sliceType);
            } else {
                // if we have more than one child of this slice, loop through and ensure that all child
                $sliceSubTypeList = [];

                foreach ($typeExample as $i => $subType) {
                    $sliceSubTypeList[] = static::parseType($configuration, $typeName, $subType, $sliceType);
                }

                $type = null;

                foreach ($sliceSubTypeList as $sliceSubType) {
                    if (null === $type) {
                        $type = $sliceSubType;
                    } else if (get_class($type) === get_class($sliceSubType)) {
                        $type = $configuration->callbacks()
                            ->mostSpecificPossibleGoType($configuration, $type, $sliceSubType);
                        if ($type instanceof InterfaceType) {
                            break;
                        }
                    }
                }
            }
        } else if ($sliceGoType instanceof InterfaceType) {
            $type = new InterfaceType($configuration, $typeName, $typeExample);
        } else {
            if ($sliceGoType) {
                $type = new SimpleType($configuration, $typeName, $typeExample, $sliceGoType->type());
            } else {
                $type = new InterfaceType($configuration, $typeName, $typeExample);
            }
        }

        $sliceType->setSliceType($type);

        return $sliceType;
    }
}