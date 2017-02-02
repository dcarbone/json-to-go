<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class NameUtils
 *
 * @package DCarbone\JSONToGO
 */
abstract class Typer
{
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param mixed $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return string
     */
    public static function goType(Configuration $configuration, $typeName, $typeExample, $parent = null)
    {
        $type = gettype($typeExample);

        if ('string' === $type)
            return GOTYPE_STRING;

        if ('integer' === $type)
        {
            if ($configuration->forceIntToFloat())
                return GOTYPE_FLOAT64;

            if ($configuration->useSimpleInt())
                return GOTYPE_INT;

            if ($typeExample > -2147483648 && $typeExample < 2147483647)
                return GOTYPE_INT;

            return GOTYPE_INT64;
        }

        if ('boolean' === $type)
            return GOTYPE_BOOLEAN;

        if ('double' === $type)
            return GOTYPE_FLOAT64;

        if ('array' === $type)
            return GOTYPE_SLICE;

        if ('object' === $type)
            return GOTYPE_STRUCT;

        return GOTYPE_INTERFACE;
    }

    /**
     * @param string $type
     * @return bool
     */
    public static function isSimpleGoType($type)
    {
        switch($type)
        {
            case GOTYPE_INT:
            case GOTYPE_INT32:
            case GOTYPE_INT64:
            case GOTYPE_FLOAT32:
            case GOTYPE_FLOAT64:
            case GOTYPE_STRING:
            case GOTYPE_BOOLEAN:
                return true;
        }

        return false;
    }

    /**
     * @param string $type1
     * @param mixed $type1Example
     * @param string $type2
     * @param mixed $type2Example
     * @return string
     */
    public static function mostSpecificPossibleSimpleGoType($type1, $type1Example, $type2, $type2Example)
    {
        if ($type1 === $type2)
            return $type1;

        if ('float' === substr($type1, 0, 5) && 'int' === substr($type2, 0, 3))
            return $type1;

        if ('int' === substr($type1, 0, 3) && 'float' === substr($type2, 0, 5))
            return $type1;

        return 'interface{}';
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $type1
     * @param mixed $type1Example
     * @param string $type2
     * @param mixed $type2Example
     * @return string
     */
    public static function mostSpecificPossibleComplexGoType(Configuration $configuration, $type1, $type1Example, $type2, $type2Example)
    {
        if ($type1 === $type2)
            return $type1;

        return 'interface{}';
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $type1
     * @param mixed $type1Example
     * @param string $type2
     * @param mixed $type2Example
     * @return string
     */
    public static function mostSpecificPossibleGoType(Configuration $configuration, $type1, $type1Example, $type2, $type2Example)
    {
        if (static::isSimpleGoType($type1) && static::isSimpleGoType($type2))
            return static::mostSpecificPossibleSimpleGoType($type1, $type1Example, $type2, $type2Example);

        return static::mostSpecificPossibleComplexGoType($configuration, $type1, $type1Example, $type2, $type2Example);
    }
}
