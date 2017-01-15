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
 *
 * @package DCarbone\JSONToGO\Types
 */
abstract class AbstractType
{
    /** @var \DCarbone\JSONToGO\Configuration */
    protected $configuration;

    /** @var string */
    protected $name;
    /** @var mixed */
    protected $definition;

    /** @var bool */
    protected $alwaysDefined = true;

    /** @var \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType */
    protected $parent = null;

    /**
     * AbstractType constructor.
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $name
     * @param mixed $definition
     */
    public function __construct(Configuration $configuration, $name, $definition)
    {
        $this->configuration = $configuration;
        $this->name = $name;
        $this->definition = $definition;
    }

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return [
            'name' => $this->name,
            'parent' => $this->parent,
            'alwaysDefined' => $this->alwaysDefined,
        ];
    }

    /**
     * @return string
     */
    abstract public function type();

    /**
     * @param int $indentLevel
     * @return string
     */
    abstract public function toGO($indentLevel = 0);

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function goName()
    {
        if (null === $this->parent())
            return $this->name();

        return $this->configuration->callbacks()->formatPropertyName($this->configuration, $this->name());
    }

    /**
     * @return string
     */
    public function goTypeName()
    {
        if (null === ($parent = $this->parent()))
            return $this->goName();

        if ($parent instanceof SliceType || $parent instanceof MapType)
            return $parent->goTypeName();

        return sprintf('%s%s', $parent->goTypeName(), $this->goName());
    }

    /**
     * @return mixed
     */
    public function definition()
    {
        return $this->definition;
    }

    /**
     * @return bool
     */
    public function isAlwaysDefined()
    {
        return $this->alwaysDefined && false === $this->configuration->forceOmitEmpty();
    }

    /**
     * @return AbstractType
     */
    public function notAlwaysDefined()
    {
        $this->alwaysDefined = false;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     * @return AbstractType
     */
    public function setParent($parent)
    {
        if ($parent instanceof SliceType || $parent instanceof StructType || $parent instanceof MapType)
        {
            $this->parent = $parent;
            return $this;
        }

        throw new \InvalidArgumentException(sprintf(
            'JSONToGO: Cannot assign type "%s" to parent of "%s".  Only Slice and Struct types may be parents.',
            is_object($parent) ? get_class($parent) : gettype($parent),
            $this->name()
        ));
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toGO(0);
    }

    /**
     * @param int $level
     * @return string
     */
    protected static function indents($level)
    {
        $level = (int)$level;

        if (0 >= $level)
            return '';

        return str_repeat("\t", $level);
    }
}