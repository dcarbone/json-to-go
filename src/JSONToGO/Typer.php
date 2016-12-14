<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
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
     * @param mixed $val
     * @return array
     */
    public static function goType(Configuration $configuration, $val)
    {
        $type = gettype($val);

        if ('string' === $type)
            return ['string', 'string'];

        if ('integer' === $type)
        {
            if ($configuration->forceIntToFloat())
                return ['float64', 1.0];

            if ($val > -2147483648 && $val < 2147483647)
                return ['int', 1];

            return ['int64', 1];
        }

        if ('boolean' === $type)
            return ['bool', true];

        if ('double' === $type)
            return ['float64', 1.0];

        if ('array' === $type)
            return ['slice', $val];

        if ('object' === $type)
            return ['struct', $val];

        return ['interface{}', null];
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