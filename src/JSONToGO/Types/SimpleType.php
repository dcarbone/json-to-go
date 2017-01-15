<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;

/**
 * Class SimpleType
 *
 * @package DCarbone\JSONToGO\Types
 */
class SimpleType extends AbstractType
{
    /** @var string */
    protected $type;

    /**
     * SimpleType constructor.
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $name
     * @param int|float|bool|string $definition
     * @param string $type
     * @param null|\DCarbone\JSONToGO\Types\StructType|\DCarbone\JSONToGO\Types\SliceType|\DCarbone\JSONToGO\Types\MapType $parent
     */
    public function __construct(Configuration $configuration, $name, $definition, $type, $parent = null)
    {
        parent::__construct($configuration, $name, $definition, $parent);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO($indentLevel = 0)
    {
        if (null === $this->parent())
        {
            return sprintf(
                'type %s %s%s',
                $this->goTypeName(),
                $this->configuration->forceScalarToPointer() ? '*' : '',
                $this->type()
            );
        }

        if ($this->configuration->forceScalarToPointer())
            return sprintf('*%s', $this->type());

        return $this->type();
    }
}