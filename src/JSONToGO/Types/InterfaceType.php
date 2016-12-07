<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class InterfaceType
 *
 * @package DCarbone\JSONToGO\Types
 */
class InterfaceType extends AbstractType
{
    /**
     * @return string
     */
    public function type()
    {
        return 'interface{}';
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toJson($indentLevel = 0)
    {
        if ($this->isCollection())
            return sprintf('[]%s', $this->type());

        return $this->type();
    }
}