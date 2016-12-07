<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;
use DCarbone\JSONToGO\Namer;

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
    protected $collection = false;

    /** @var \DCarbone\JSONToGO\Types\StructType */
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
            'collection' => $this->collection,
            'parent' => $this->parent
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
    abstract public function toJson($indentLevel = 0);

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
        return Namer::formatPropertyName($this->configuration, $this->name());
    }

    /**
     * @return string
     */
    public function goTypeName()
    {
        if (null === ($parent = $this->parent()))
            return $this->goName();

        return sprintf('%s%s', $parent->goName(), $this->goName());
    }

    /**
     * @return string
     */
    public function goTypeSliceName()
    {
        if ($this->isCollection())
            return sprintf('%sSlice', $this->goTypeName());

        throw new \BadMethodCallException(sprintf(
            '"%s" is not a collection.',
            $this->goTypeName()
        ));
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
    public function isCollection()
    {
        return $this->collection;
    }

    /**
     * @return AbstractType
     */
    public function collection()
    {
        $this->collection = true;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public function parent()
    {
        return $this->parent;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\StructType $parent
     * @return AbstractType
     */
    public function setParent(StructType $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->toJson(0);
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