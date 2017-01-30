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
            return 'string';

        if ('integer' === $type)
        {
            if ($configuration->forceIntToFloat())
                return 'float64';

            if ($configuration->useSimpleInt())
                return 'int';

            if ($typeExample > -2147483648 && $typeExample < 2147483647)
                return 'int32';

            return 'int64';
        }

        if ('boolean' === $type)
            return 'bool';

        if ('double' === $type)
            return 'float64';

        if ('array' === $type)
            return 'slice';

        if ('object' === $type)
            return 'struct';

        return 'interface{}';
    }

    /**
     * @param string $type1
     * @param string $type2
     * @return string
     */
    public static function mostSpecificPossibleGoType($type1, $type2)
    {
        if ('float' === substr($type1, 0, 5) && 'int' === substr($type2, 0, 3))
            return $type1;

        if ('int' === substr($type1, 0, 3) && 'float' === substr($type2, 0, 5))
            return $type1;

        return 'interface{}';
    }
}