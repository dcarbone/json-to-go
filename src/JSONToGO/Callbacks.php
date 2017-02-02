<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class Callbacks
 *
 * @package DCarbone\JSONToGO
 */
class Callbacks
{
    /** @var callable */
    private $formatPropertyName = ['\\DCarbone\\JSONToGO\\Namer', 'formatPropertyName'];
    /** @var callable */
    private $toProperCase = ['\\DCarbone\\JSONToGO\\Namer', 'toProperCase'];
    /** @var callable */
    private $goType = ['\\DCarbone\\JSONToGO\\Typer', 'goType'];
    /** @var callable */
    private $mostSpecificPossibleGoType = ['\\DCarbone\\JSONToGO\\Typer', 'mostSpecificPossibleGoType'];

    /**
     * Callbacks constructor.
     *
     * @param array $callableArray
     */
    public function __construct(array $callableArray = [])
    {
        foreach($callableArray as $k => $callable)
        {
            if (isset($this->$k))
                $this->$k = $callable;
        }
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $propertyName
     * @return string
     */
    public function formatPropertyName(Configuration $configuration, $propertyName)
    {
        return call_user_func($this->formatPropertyName, $configuration, $propertyName);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public function toProperCase(Configuration $configuration, $string)
    {
        return call_user_func($this->toProperCase, $configuration, $string);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param mixed $typeExample
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return string
     */
    public function goType(Configuration $configuration, $typeName, $typeExample, $parent = null)
    {
        return call_user_func($this->goType, $configuration, $typeName, $typeExample, $parent);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $type1
     * @param mixed $type1Example
     * @param string $type2
     * @param mixed $type2Example
     * @return string
     */
    public function mostSpecificPossibleGoType(Configuration $configuration, $type1, $type1Example, $type2, $type2Example)
    {
        return call_user_func($this->mostSpecificPossibleGoType, $configuration, $type1, $type1Example, $type2, $type2Example);
    }

    /**
     * @param callable $callable
     * @return Callbacks
     */
    public function setFormatPropertyNameCallback($callable)
    {
        $this->formatPropertyName = $callable;
        return $this;
    }

    /**
     * @param callable $toProperCase
     * @return Callbacks
     */
    public function setToProperCaseCallback($toProperCase)
    {
        $this->toProperCase = $toProperCase;
        return $this;
    }

    /**
     * @param callable $goType
     * @return Callbacks
     */
    public function setGoTypeCallback($goType)
    {
        $this->goType = $goType;
        return $this;
    }

    /**
     * @param callable $mostSpecificPossibleGoType
     * @return Callbacks
     */
    public function setMostSpecificPossibleGoTypeCallback($mostSpecificPossibleGoType)
    {
        $this->mostSpecificPossibleGoType = $mostSpecificPossibleGoType;
        return $this;
    }
}