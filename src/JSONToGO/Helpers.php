<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Types\AbstractType;
use DCarbone\JSONToGO\Types\StructType;

/**
 * Class Helpers
 *
 * @package DCarbone\JSONToGO
 */
abstract class Helpers
{
    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\AbstractType $field
     * @return string
     */
    public static function buildStructFieldTag(Configuration $configuration, StructType $struct, AbstractType $field)
    {
        $tag = sprintf('json:"%s', $field->name());

        if (false === $field->isAlwaysDefined() || $configuration->forceOmitEmpty())
            $tag = sprintf('%s,omitempty', $tag);

        return sprintf('%s"', $tag);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\AbstractType $field
     * @return bool
     */
    public static function isFieldExported(Configuration $configuration, StructType $struct, AbstractType $field)
    {
        return true;
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param mixed $typeExample
     * @return mixed
     */
    public static function sanitizeInput(Configuration $configuration, $typeExample)
    {
        switch(gettype($typeExample))
        {
            case 'string': return 'string';
            case 'double': return 1.0;
            case 'integer': return 1;
            case 'boolean': return true;

            case 'array':
                $tmp = $typeExample;
                foreach($tmp as $k => &$v)
                {
                    $v = $configuration->callbacks()->sanitizeInput($configuration, $v);
                }
                return array_values(array_unique($tmp, SORT_REGULAR));

            case 'object':
                $tmp = $typeExample;
                foreach(get_object_vars($tmp) as $k => $v)
                {
                    $tmp->{$k} = $configuration->callbacks()->sanitizeInput($configuration, $v);
                }
                return $tmp;

            default:
                return null;
        }
    }
}
