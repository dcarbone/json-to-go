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
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\SliceType;
use DCarbone\JSONToGO\Types\StructType;
use DCarbone\JSONToGO\Types\Type;

/**
 * Class NameUtils
 *
 * @package DCarbone\JSONToGO
 */
abstract class Typer {
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param mixed $typeExample
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     * @return string
     */
    public static function goType(Configuration $configuration, string $typeName, $typeExample, TypeParent $parent = null): string {
        $type = gettype($typeExample);

        if ('string' === $type) {
            return GOTYPE_STRING;
        }

        if ('integer' === $type) {
            if ($configuration->forceIntToFloat()) {
                return GOTYPE_FLOAT64;
            }

            if ($configuration->useSimpleInt()) {
                return GOTYPE_INT;
            }

            if ($typeExample > -2147483648 && $typeExample < 2147483647) {
                return GOTYPE_INT;
            }

            return GOTYPE_INT64;
        }

        if ('boolean' === $type) {
            return GOTYPE_BOOLEAN;
        }

        if ('double' === $type) {
            return GOTYPE_FLOAT64;
        }

        if ('array' === $type) {
            return GOTYPE_SLICE;
        }

        if ('object' === $type) {
            return GOTYPE_STRUCT;
        }

        return GOTYPE_INTERFACE;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\Type $type
     * @return bool
     */
    public static function isSimpleGoType(Type $type) {
        return $type instanceof SimpleType;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\Type $type1
     * @param \DCarbone\JSONToGO\Types\Type $type2
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function mostSpecificPossibleSimpleGoType(Configuration $configuration, Type $type1, Type $type2): Type {
        if (get_class($type1) === get_class($type2)) {
            return $type1;
        }

        if ('float' === substr($type1->type(), 0, 5) && 'int' === substr($type2->type(), 0, 3)) {
            return $type1;
        }

        if ('int' === substr($type1->type(), 0, 3) && 'float' === substr($type2->type(), 0, 5)) {
            return $type1;
        }

        return new InterfaceType($configuration, $type1->name(), $type1->example());
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\Type $type1
     * @param \DCarbone\JSONToGO\Types\Type $type2
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function mostSpecificPossibleComplexGoType(Configuration $configuration, Type $type1, Type $type2): Type {
        if ($type1 instanceof SliceType && $type2 instanceof SliceType) {
            $compType = $configuration
                ->callbacks()
                ->mostSpecificPossibleGoType($configuration, $type1->sliceType(), $type2->sliceType());
            if (!($compType instanceof InterfaceType)) {
                return $type1;
            }
        } else if ($type1 instanceof MapType && $type2 instanceof MapType) {
            $compType = $configuration
                ->callbacks()
                ->mostSpecificPossibleGoType($configuration, $type1->mapType(), $type2->mapType());
            if (!($compType instanceof InterfaceType)) {
                return $type1;
            }
        } else if ($type1 instanceof StructType && $type2 instanceof StructType) {
            $parent1 = $type1->parent();
            if (null === $parent1) {
                return $type1;
            }

            // TODO: This is a terrible assumption...

            $parent1Parent = $parent1->parent();
            if ($parent1Parent && !($parent1Parent instanceof SliceType)) {
                return $type1;
            }

            $k1 = array_keys(get_object_vars($type1->example()));
            $k2 = array_keys(get_object_vars($type2->example()));

            sort($k1);
            sort($k2);

            $diff = array_diff($k1, $k2);
            if (0 === count($diff)) {
                return $type1;
            }
        } else if (get_class($type1) === get_class($type2)) {
            return $type1;
        } else if ($type1 instanceof InterfaceType && !($type2 instanceof InterfaceType)) {
            return $type2;
        } else if (!($type1 instanceof InterfaceType) && $type2 instanceof InterfaceType) {
            return $type1;
        }

        return new InterfaceType($configuration, $type1->name(), $type1->example());
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\Type $type1
     * @param \DCarbone\JSONToGO\Types\Type $type2
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function mostSpecificPossibleGoType(Configuration $configuration, Type $type1, Type $type2): Type {
        if (static::isSimpleGoType($type1) && static::isSimpleGoType($type2)) {
            return static::mostSpecificPossibleSimpleGoType($configuration, $type1, $type2);
        }

        return static::mostSpecificPossibleComplexGoType($configuration, $type1, $type2);
    }
}
