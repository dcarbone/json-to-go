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
class SimpleType extends AbstractType {
    /** @var string */
    protected $type;

    /**
     * SimpleType constructor.
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $name
     * @param int|float|bool|string $example
     * @param string $type
     */
    public function __construct(Configuration $configuration, string $name, $example, string $type) {
        parent::__construct($configuration, $name, $example);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function type(): string {
        return $this->type;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        if (null === $this->parent()) {
            return sprintf(
                'type %s %s%s',
                $this->goTypeName(),
                $this->configuration->forceScalarToPointer() ? '*' : '',
                $this->type()
            );
        }

        if ($this->configuration->forceScalarToPointer()) {
            return sprintf('*%s', $this->type());
        }

        return $this->type();
    }
}