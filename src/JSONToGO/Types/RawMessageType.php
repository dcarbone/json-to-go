<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;

/**
 * Class RawMessageType
 *
 * @package DCarbone\JSONToGO\Types
 */
class RawMessageType extends SimpleType {
    /**
     * RawMessageType constructor.
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @param string $name
     * @param mixed $example
     */
    public function __construct(Configuration $configuration, string $name, $example) {
        parent::__construct($configuration, $name, $example, 'json.RawMessage');
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        if (null === $this->parent()) {
            return sprintf(
                'type %s %s',
                $this->goTypeName(),
                $this->type()
            );
        }

        return $this->type();
    }
}