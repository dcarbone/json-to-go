<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class MapType
 *
 * @package DCarbone\JSONToGO\Types
 */
class MapType extends AbstractType implements ParentTypeInterface {
    /** @var AbstractType */
    protected $mapType;

    /**
     * @return array
     */
    public function __debugInfo() {
        return parent::__debugInfo() + ['mapType' => $this->mapType()];
    }

    /**
     * @return string
     */
    public function type(): string {
        return GOTYPE_MAP;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\TypeInterface $mapType
     * @return \DCarbone\JSONToGO\Types\MapType
     */
    public function setMapType(TypeInterface $mapType): MapType {
        $mapType->setParent($this);
        $this->mapType = $mapType;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\TypeInterface
     */
    public function mapType(): TypeInterface {
        return $this->mapType;
    }

    /**
     * @return string
     */
    public function goTypeMapName(): string {
        return sprintf('%sMap', $this->goTypeName());
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        $output = [];

        $mapType = $this->mapType();

        $parent = $this->parent();

        if ($this->configuration->breakOutInlineStructs()) {
            if ($mapType instanceof StructType) {
                $output[] = sprintf('type %s map[string]*%s', $this->goTypeMapName(), $this->goTypeName());
                $output[] = $mapType->toGO($indentLevel);
            } else if (null === $parent) {
                $output[] = sprintf('type %s map[string]%s', $this->goTypeName(), $mapType->toGO());
            } else if ($parent instanceof SliceType || $parent instanceof MapType) {
                $output[] = sprintf('map[string]%s', $mapType->toGO($indentLevel));
            } else {
                $output[] = sprintf('type %s map[string]%s', $this->goTypeMapName(), $mapType->toGO());
            }
        } else if (null === $parent) {
            $output[] = sprintf(
                'type %s map[string]%s',
                $this->goTypeName(),
                $mapType->toGO($indentLevel)
            );
        } else {
            $output[] = sprintf('map[string]%s', $mapType->toGO($indentLevel));
        }

        return implode("\n\n", $output);
    }
}