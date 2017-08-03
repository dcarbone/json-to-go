<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Interface TypeInterface
 * @package DCarbone\JSONToGO\Types
 */
interface TypeInterface {
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
     * @return \DCarbone\JSONToGO\Types\ParentTypeInterface|null
     */
    public function parent(): ?ParentTypeInterface;

    /**
     * @param \DCarbone\JSONToGO\Types\ParentTypeInterface $parent
     * @return \DCarbone\JSONToGO\Types\TypeInterface
     */
    public function setParent(ParentTypeInterface $parent): TypeInterface;

    /**
     * @return string
     */
    public function __toString(): string;
}