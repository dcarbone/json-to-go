<?php namespace DCarbone\JSONToGO\Types;

/**
 * Class SliceType
 *
 * @package DCarbone\JSONToGO\Types
 */
class SliceType extends AbstractType implements ParentTypeInterface {
    /** @var \DCarbone\JSONToGO\Types\TypeInterface */
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
     * @param \DCarbone\JSONToGO\Types\TypeInterface $sliceType
     * @return \DCarbone\JSONToGO\Types\SliceType
     */
    public function setSliceType(TypeInterface $sliceType): SliceType {
        $sliceType->setParent($this);
        $this->sliceType = $sliceType;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\TypeInterface
     */
    public function sliceType(): TypeInterface {
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