<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
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

        // If this starts with anything other than an alpha character prefix with X
        if (preg_match('/^[^a-zA-Z]/S', $propertyName))
            $propertyName = sprintf('X%s', $propertyName);

        // Do stuff with non-alphanumeric characters
        $propertyName = $configuration->callbacks()->handleSpecialCharacters($configuration, $propertyName);

        // Case it and remove non-alpha characters
        $propertyName = $configuration->callbacks()->toProperCase($configuration, $propertyName);

        // Finally, strip out everything that was not caught above and is not an alphanumeric character.
        return preg_replace('/[^a-zA-Z0-9]/S', '', $propertyName);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public static function handleSpecialCharacters(Configuration $configuration, $string)
    {
        $ci = $configuration->commonInitialisms();

        return preg_replace_callback('/(^|[^a-zA-Z])([a-z]+)/S', function($item) use ($ci) {
            list($full, $symbol, $string) = $item;
            $upper = strtoupper($string);

            if (in_array($upper, $ci, true))
                return $upper;
            return ucfirst(strtolower($string));
        }, $string);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public static function toProperCase(Configuration $configuration, $string)
    {
        $ci = $configuration->commonInitialisms();

        return preg_replace_callback('/([A-Z])([a-z]+)/S', function($item) use ($ci) {
            list($full, $firstLetter, $rest) = $item;
            $upper = strtoupper($full);
            if (in_array($upper, $ci, true))
                return $upper;
            return $full;
        }, $string);
    }
}