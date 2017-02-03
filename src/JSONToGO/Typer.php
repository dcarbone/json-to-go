<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */
use DCarbone\JSONToGO\Types\AbstractType;
use DCarbone\JSONToGO\Types\InterfaceType;
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\SliceType;

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
     * @param AbstractType $type
     * @return bool
     */
    public static function isSimpleGoType(AbstractType $type)
    {
        return $type instanceof SimpleType;
    }


    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\AbstractType $type1
     * @param \DCarbone\JSONToGO\Types\AbstractType $type2
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function mostSpecificPossibleSimpleGoType(Configuration $configuration, AbstractType $type1, AbstractType $type2)
    {
        if ($type1 instanceof $type2)
            return $type1;

        if ('float' === substr($type1->type(), 0, 5) && 'int' === substr($type2->type(), 0, 3))
            return $type1;

        if ('int' === substr($type1->type(), 0, 3) && 'float' === substr($type2->type(), 0, 5))
            return $type1;

        return new InterfaceType($configuration, $type1->name(), $type1->example());
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\AbstractType $type1
     * @param \DCarbone\JSONToGO\Types\AbstractType $type2
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function mostSpecificPossibleComplexGoType(Configuration $configuration, AbstractType $type1, AbstractType $type2)
    {
        if ($type1 instanceof $type2 && !($type1 instanceof SliceType))
            return $type1;

        return new InterfaceType($configuration, $type1->name(), $type1->example());
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\AbstractType $type1
     * @param \DCarbone\JSONToGO\Types\AbstractType $type2
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function mostSpecificPossibleGoType(Configuration $configuration, AbstractType $type1, AbstractType $type2)
    {
        if (static::isSimpleGoType($type1) && static::isSimpleGoType($type2))
            return static::mostSpecificPossibleSimpleGoType($configuration, $type1, $type2);

        return static::mostSpecificPossibleComplexGoType($configuration, $type1, $type2);
    }
}
