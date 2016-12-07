<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class Namer
 *
 * @package DCarbone\JSONToGO
 */
abstract class Namer
{
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $propertyName
     * @return string
     */
    public static function formatPropertyName(Configuration $configuration, $propertyName)
    {
        if (!$propertyName)
            return '';

        // If entire name is a number...
        if (preg_match('/^\d+$/S', $propertyName))
        {
            $propertyName = sprintf('Num%s', $propertyName);
        }
        // If first character of name is a number...
        else if (preg_match('/^\d/S', $propertyName))
        {
            $propertyName = $configuration->numberToWord(substr($propertyName, 0, 1)) . substr($propertyName, 1);
        }

        // Case it
        $propertyName = static::toProperCase($configuration, $propertyName);

        // Then, if this starts with anything other than an alpha character prefix with X
        if (preg_match('/^[^a-zA-Z]/S', $propertyName))
            $propertyName = sprintf('X%s', $propertyName);

        // Finally, strip out everything that was not caught above and is not an alphanumeric character.
        return preg_replace('/[^a-zA-Z0-9]/S', '', $propertyName);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public static function toProperCase(Configuration $configuration, $string)
    {
        $commonInitialisms = $configuration->commonInitialisms();

        return preg_replace_callback('/([A-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return $item;
        }, preg_replace_callback('/(^|[^a-zA-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return ucfirst(strtolower($item));
        }, $string));
    }
}