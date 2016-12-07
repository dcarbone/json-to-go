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
     * @return string
     */
    public static function goType(Configuration $configuration, $val)
    {
        $type = gettype($val);

        if ('string' === $type)
        {
            if (preg_match('/\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?(\+\d\d:\d\d|Z)/S', $val))
                return 'time.Time';

            return 'string';
        }

        if ('integer' === $type)
        {
            if ($configuration->forceIntToFloat())
                return 'float64';

            if ($val > -2147483648 && $val < 2147483647)
                return 'int';

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