<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;

/**
 * Class AbstractType
 * @package DCarbone\JSONToGO\Types
 */
abstract class AbstractType implements Type {
    /** @var \DCarbone\JSONToGO\Configuration */
    protected $configuration;

    /** @var string */
    protected $name;
    /** @var mixed */
    protected $example;

    /** @var bool */
    protected $alwaysDefined = true;

    /** @var \DCarbone\JSONToGO\Types\TypeParent|null */
    protected $parent = null;

    /**
     * AbstractType constructor.
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $name
     * @param mixed $example
     * @param \DCarbone\JSONToGO\Types\TypeParent|null $parent
     */
    public function __construct(Configuration $configuration,
                                string $name,
                                $example,
                                TypeParent $parent = null) {
        $this->configuration = $configuration;
        $this->name = $name;
        $this->example = $example;
        $this->parent = $parent;
    }

    /**
     * @return array
     */
    public function __debugInfo() {
        return [
            'name' => $this->name,
            'alwaysDefined' => $this->alwaysDefined,
            'example' => $this->example,
        ];
    }

    /**
     * @return string
     */
    public function name(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function goName(): string {
        if (null === $this->parent()) {
            return $this->name();
        }

        return $this->configuration->callbacks()->formatPropertyName($this->configuration, $this->name());
    }

    /**
     * @return string
     */
    public function goTypeName(): string {
        if (null === ($parent = $this->parent())) {
            return $this->goName();
        }

        if ($parent instanceof SliceType || $parent instanceof MapType) {
            return $parent->goTypeName();
        }

        return sprintf('%s%s', $parent->goTypeName(), $this->goName());
    }

    /**
     * @return mixed
     */
    public function example() {
        return $this->example;
    }

    /**
     * @return bool
     */
    public function isAlwaysDefined(): bool {
        return $this->alwaysDefined && false === $this->configuration->forceOmitEmpty();
    }

    /**
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function notAlwaysDefined(): Type {
        $this->alwaysDefined = false;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\TypeParent|null
     */
    public function parent(): ?TypeParent {
        return $this->parent;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\TypeParent $parent
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function setParent(TypeParent $parent): Type {
        if ($parent instanceof SliceType || $parent instanceof StructType || $parent instanceof MapType) {
            $this->parent = $parent;
            return $this;
        }

        throw new \InvalidArgumentException(sprintf(
            'JSONToGO: Cannot assign type "%s" to parent of "%s".  Only Slice, Map, and Struct types may be parents.',
            is_object($parent) ? get_class($parent) : gettype($parent),
            $this->name()
        ));
    }

    /**
     * @return string
     */
    public function __toString(): string {
        return $this->toGO(0);
    }

    /**
     * @param int $level
     * @return string
     */
    protected static function indents(int $level): string {
        $level = (int)$level;

        if (0 >= $level) {
            return '';
        }

        return str_repeat("\t", $level);
    }
}