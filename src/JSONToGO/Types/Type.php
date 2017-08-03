<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Interface Type
 * @package DCarbone\JSONToGO\Types
 */
interface Type {
    /**
     * @return string
     */
    public function type(): string;

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string;

    /**
     * @return string
     */
    public function name(): string;

    /**
     * @return string
     */
    public function goName(): string;

    /**
     * @return string
     */
    public function goTypeName(): string;

    /**
     * @return mixed
     */
    public function example();

    /**
     * @return bool
     */
    public function isAlwaysDefined(): bool;

    /**
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function notAlwaysDefined(): Type;

    /**
     * @return \DCarbone\JSONToGO\Types\TypeParent|null
     */
    public function parent(): ?TypeParent;

    /**
     * @param \DCarbone\JSONToGO\Types\TypeParent $parent
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function setParent(TypeParent $parent): Type;

    /**
     * @return string
     */
    public function __toString(): string;
}