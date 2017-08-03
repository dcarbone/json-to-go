<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class InterfaceType
 * @package DCarbone\JSONToGO\Types
 */
class InterfaceType extends AbstractType {
    /**
     * @return string
     */
    public function type(): string {
        return GOTYPE_INTERFACE;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        if (null === $this->parent()) {
            return sprintf('type %s %s', $this->goTypeName(), $this->type());
        }

        return $this->type();
    }
}