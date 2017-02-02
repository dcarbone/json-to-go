<?php namespace DCarbone\JSONToGO\Types;

/**
 * Class SliceType
 *
 * @package DCarbone\JSONToGO\Types
 */
class SliceType extends AbstractType
{
    /** @var \DCarbone\JSONToGO\Types\AbstractType */
    protected $sliceType;

    /**
     * @return string
     */
    public function type()
    {
        return '[]';
    }

    /**
     * @param \DCarbone\JSONToGO\Types\AbstractType $sliceType
     * @return SliceType
     */
    public function setSliceType(AbstractType $sliceType)
    {
        $sliceType->setParent($this);
        $this->sliceType = $sliceType;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public function sliceType()
    {
        return $this->sliceType;
    }

    /**
     * @return string
     */
    public function goTypeSliceName()
    {
        return sprintf('%sSlice', $this->goTypeName());
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO($indentLevel = 0)
    {
        $output = [];

        $sliceType = $this->sliceType();
        $parent = $this->parent();

        if ($this->configuration->breakOutInlineStructs())
        {
            if (null === $parent)
            {
                $output[] = sprintf('type %s []%s', $this->goTypeName(), $sliceType->toGO());
            }
            else
            {
                if ($parent instanceof MapType)
                {
                    $output[] = sprintf('[]%s', $sliceType->toGO($indentLevel));
                }
                else if ($parent instanceof SliceType)
                {
                    if ($sliceType instanceof StructType)
                    {
                        $output[] = sprintf('[]*%s', $this->goTypeSliceName(), $this->goTypeName());
                        $output[] = $sliceType->toGO($indentLevel);
                    }
                    else
                    {
                        $output[] = sprintf('[]%s', $sliceType->toGO($indentLevel));
                    }
                }
                else
                {
                    $output[] = sprintf('type %s []%s', $this->goTypeSliceName(), $sliceType->toGO());
                }
            }
        }
        else if (null === $parent)
        {
            $output[] = sprintf(
                'type %s []%s',
                $this->goTypeName(),
                $sliceType->toGO($indentLevel)
            );
        }
        else
        {
            $output[] = sprintf('[]%s', $sliceType->toGO($indentLevel));
        }

        return implode("\n\n", $output);
    }

}