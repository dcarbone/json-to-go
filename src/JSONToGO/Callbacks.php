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
 * Class Callbacks
 *
 * @package DCarbone\JSONToGO
 */
class Callbacks
{
    /** @var callable */
    private $formatPropertyName = ['\\DCarbone\\JSONToGO\\Namer', 'formatPropertyName'];
    /** @var callable */
    private $handleSpecialCharacters = ['\\DCarbone\\JSONToGO\\Namer', 'handleSpecialCharacters'];
    /** @var callable */
    private $toProperCase = ['\\DCarbone\\JSONToGO\\Namer', 'toProperCase'];
    /** @var callable */
    private $goType = ['\\DCarbone\\JSONToGO\\Typer', 'goType'];
    /** @var callable */
    private $mostSpecificPossibleGoType = ['\\DCarbone\\JSONToGO\\Typer', 'mostSpecificPossibleGoType'];
    /** @var callable */
    private $buildStructFieldTag = ['\\DCarbone\\JSONToGO\\Helpers', 'buildStructFieldTag'];
    /** @var callable */
    private $isFieldExposed = ['\\DCarbone\\JSONToGO\\Helpers', 'isFieldExposed'];

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
    public function handleSpecialCharacters(Configuration $configuration, $string)
    {
        return call_user_func($this->handleSpecialCharacters, $configuration, $string);
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
     * @param \DCarbone\JSONToGO\Types\AbstractType $type1
     * @param \DCarbone\JSONToGO\Types\AbstractType $type2
     * @return AbstractType
     */
    public function mostSpecificPossibleGoType(Configuration $configuration, AbstractType $type1, AbstractType $type2)
    {
        return call_user_func($this->mostSpecificPossibleGoType, $configuration, $type1, $type2);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\AbstractType $field
     * @return string
     */
    public function buildStructFieldTag(Configuration $configuration, StructType $struct, AbstractType $field)
    {
        return call_user_func($this->buildStructFieldTag, $configuration, $struct, $field);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\AbstractType $field
     * @return bool
     */
    public function isFieldExposed(Configuration $configuration, StructType $struct, AbstractType $field)
    {
        return (bool)call_user_func($this->isFieldExposed, $configuration, $struct, $field);
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
     * @param callable $callable
     * @return Callbacks
     */
    public function setHandleSpecialCharactersCallback($callable)
    {
        $this->handleSpecialCharacters = $callable;
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

    /**
     * @param callable $buildStructFieldTag
     * @return Callbacks
     */
    public function setBuildStructFieldTagCallback($buildStructFieldTag)
    {
        $this->buildStructFieldTag = $buildStructFieldTag;
        return $this;
    }

    /**
     * @param callable $isFieldExposed
     * @return Callbacks
     */
    public function setIsFieldExposedCallback($isFieldExposed)
    {
        $this->isFieldExposed = $isFieldExposed;
        return $this;
    }
}