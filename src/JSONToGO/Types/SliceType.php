<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class SliceType
 * @package DCarbone\JSONToGO\Types
 */
class SliceType extends AbstractType implements TypeParent {
    /** @var \DCarbone\JSONToGO\Types\Type */
    protected $sliceType = null;

    /**
     * @return array
     */
    public function __debugInfo() {
        return parent::__debugInfo() + ['sliceType' => $this->sliceType];
    }

    /**
     * @return string
     */
    public function type(): string {
        return GOTYPE_SLICE;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\Type $sliceType
     * @return \DCarbone\JSONToGO\Types\SliceType
     */
    public function setSliceType(Type $sliceType): SliceType {
        $sliceType->setParent($this);
        $this->sliceType = $sliceType;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function sliceType(): Type {
        return $this->sliceType;
    }

    /**
     * @return string
     */
    public function goTypeSliceName(): string {
        return sprintf('%sSlice', $this->goTypeName());
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        $output = [];

        $sliceType = $this->sliceType();
        $parent = $this->parent();

        if ($this->configuration->breakOutInlineStructs()) {
            if ($sliceType instanceof StructType) {
                if ($parent instanceof SliceType) {
                    $output[] = sprintf('[]*%s', $sliceType->goTypeName());
                } else {
                    $output[] = sprintf('type %s []*%s', $this->goTypeSliceName(), $this->goTypeName());
                }

                $output[] = $sliceType->toGO($indentLevel);
            } else if (null === $parent) {
                $output[] = sprintf('type %s []%s', $this->goTypeName(), $sliceType->toGO());
            } else if ($parent instanceof SliceType || $parent instanceof MapType) {
                $output[] = sprintf('[]%s', $sliceType->toGO($indentLevel));
            } else {
                $output[] = sprintf('type %s []%s', $this->goTypeSliceName(), $sliceType->toGO());
            }
        } else if (null === $parent) {
            $output[] = sprintf(
                'type %s []%s',
                $this->goTypeName(),
                $sliceType->toGO($indentLevel)
            );
        } else {
            $output[] = sprintf('[]%s', $sliceType->toGO($indentLevel));
        }

        return implode("\n\n", $output);
    }

}