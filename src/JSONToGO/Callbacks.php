<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Types\TypeInterface;
use DCarbone\JSONToGO\Types\ParentTypeInterface;
use DCarbone\JSONToGO\Types\StructType;

/**
 * Class Callbacks
 *
 * @package DCarbone\JSONToGO
 */
class Callbacks {
    /** @var callable */
    private $sanitizeInput = ['\\DCarbone\\JSONToGO\\Helpers', 'sanitizeInput'];
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
    private $isFieldExported = ['\\DCarbone\\JSONToGO\\Helpers', 'isFieldExported'];
    /** @var callable */
    private $isFieldIgnored = ['\\DCarbone\\JSONToGO\\Helpers', 'isFieldIgnored'];

    /**
     * Callbacks constructor.
     *
     * @param array $callableArray
     */
    public function __construct(array $callableArray = []) {
        foreach ($callableArray as $k => $callable) {
            if (isset($this->$k)) {
                $this->$k = $callable;
            }
        }
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param mixed $typeExample
     * @return mixed
     */
    public function sanitizeInput(Configuration $configuration, $typeExample) {
        return call_user_func($this->sanitizeInput, $configuration, $typeExample);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $propertyName
     * @return string
     */
    public function formatPropertyName(Configuration $configuration, string $propertyName): string {
        return call_user_func($this->formatPropertyName, $configuration, $propertyName);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public function handleSpecialCharacters(Configuration $configuration, string $string): string {
        return call_user_func($this->handleSpecialCharacters, $configuration, $string);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $string
     * @return string
     */
    public function toProperCase(Configuration $configuration, string $string): string {
        return call_user_func($this->toProperCase, $configuration, $string);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $typeName
     * @param mixed $typeExample
     * @param \DCarbone\JSONToGO\Types\ParentTypeInterface|null $parent
     * @return string
     */
    public function goType(Configuration $configuration,
                           string $typeName,
                           $typeExample,
                           ParentTypeInterface $parent = null): string {
        return call_user_func($this->goType, $configuration, $typeName, $typeExample, $parent);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\TypeInterface $type1
     * @param \DCarbone\JSONToGO\Types\TypeInterface $type2
     * @return \DCarbone\JSONToGO\Types\TypeInterface
     */
    public function mostSpecificPossibleGoType(Configuration $configuration,
                                               TypeInterface $type1,
                                               TypeInterface $type2): TypeInterface {
        return call_user_func($this->mostSpecificPossibleGoType, $configuration, $type1, $type2);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\TypeInterface $field
     * @return string
     */
    public function buildStructFieldTag(Configuration $configuration, StructType $struct, TypeInterface $field): string {
        return call_user_func($this->buildStructFieldTag, $configuration, $struct, $field);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\TypeInterface $field
     * @return bool
     */
    public function isFieldExported(Configuration $configuration, StructType $struct, TypeInterface $field): bool {
        return (bool)call_user_func($this->isFieldExported, $configuration, $struct, $field);
    }

    /**
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param \DCarbone\JSONToGO\Types\StructType $struct
     * @param \DCarbone\JSONToGO\Types\TypeInterface $field
     * @return bool
     */
    public function isFieldIgnored(Configuration $configuration, StructType $struct, TypeInterface $field): bool {
        return (bool)call_user_func($this->isFieldIgnored, $configuration, $struct, $field);
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setSanitizeInputCallback($callable): Callbacks {
        $this->sanitizeInput = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setFormatPropertyNameCallback($callable): Callbacks {
        $this->formatPropertyName = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setHandleSpecialCharactersCallback($callable): Callbacks {
        $this->handleSpecialCharacters = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setToProperCaseCallback($callable): Callbacks {
        $this->toProperCase = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setGoTypeCallback($callable): Callbacks {
        $this->goType = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setMostSpecificPossibleGoTypeCallback($callable): Callbacks {
        $this->mostSpecificPossibleGoType = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setBuildStructFieldTagCallback($callable): Callbacks {
        $this->buildStructFieldTag = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setIsFieldExportedCallback($callable): Callbacks {
        $this->isFieldExported = $callable;
        return $this;
    }

    /**
     * @param callable $callable
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function setIsFieldIgnoredCallback($callable): Callbacks {
        $this->isFieldIgnored = $callable;
        return $this;
    }
}