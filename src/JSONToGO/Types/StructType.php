<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class StructType
 *
 * @package DCarbone\JSONToGO\Types
 */
class StructType extends AbstractType
{
    /** @var \DCarbone\JSONToGO\Types\StructType[] */
    protected $children = [];

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return parent::__debugInfo() + ['children' => $this->children];
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'struct';
    }

    /**
     * @param \DCarbone\JSONToGO\Types\StructType $parent
     * @return StructType
     */
    public function setParent(StructType $parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\StructType[]
     */
    public function children()
    {
        return $this->children;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\AbstractType $child
     * @return StructType
     */
    public function addChild(AbstractType $child)
    {
        $child->setParent($this);
        $this->children[$child->name()] = $child;
        return $this;
    }

    /**
     * @return string
     */
    public function goTypeSliceName()
    {
        if ($this->isCollection())
            return sprintf('%sSlice', $this->goTypeName());

        throw new \BadMethodCallException(sprintf(
            '"%s" is not a collection.',
            $this->goTypeName()
        ));
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toJson($indentLevel = 0)
    {
        $output = [];

        if ($this->configuration->breakOutInlineStructs())
        {
            if ($this->isCollection())
                $output[] = sprintf("type %s []*%s", $this->goTypeSliceName(), $this->goTypeName());

            $go = sprintf("type %s %s {\n", $this->goTypeName(), $this->type());
        }
        else
        {
            if (null === $this->parent())
            {
                $go = sprintf(
                    "type %s %s%s {\n",
                    $this->goTypeName(),
                    $this->isCollection() ? '[]' : '',
                    $this->type()
                );
            }
            else
            {
                $go = sprintf(
                    "%s%s {\n",
                    $this->isCollection() ? '[]' : '',
                    $this->type()
                );
            }
        }

        foreach($this->children() as $child)
        {
            $go = sprintf(
                '%s%s%s',
                $go,
                static::indents($indentLevel + 1),
                $child->goName()
            );

            if ($child instanceof StructType && $this->configuration->breakOutInlineStructs())
            {
                // Add the child struct to the output list...
                $output[] = $child->toJson();

                $go = sprintf(
                    '%s %s%s `json:"%s%s"`',
                    $go,
                    $child->isCollection() ? '' : '*',
                    $child->isCollection() ? $child->goTypeSliceName() : $child->goTypeName(),
                    $child->name(),
                    $child->isAlwaysDefined() ? '' : ',omitempty'
                );
            }
            else
            {
                $go = sprintf(
                    '%s %s `json:"%s%s"`',
                    $go,
                    $child->toJson($indentLevel + 2),
                    $child->name(),
                    $child->isAlwaysDefined() ? '' : ',omitempty'
                );
            }

            $go = sprintf("%s\n", $go);
        }

        $output[] = sprintf("%s\n%s}", rtrim($go), static::indents($indentLevel));

        return implode("\n\n", $output);
    }
}