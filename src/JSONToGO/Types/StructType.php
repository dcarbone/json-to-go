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
     * @param int $indentLevel
     * @return string
     */
    public function toGO($indentLevel = 0)
    {
        $output = [];

        if ($this->configuration->breakOutInlineStructs() || null === $this->parent())
        {
            $go = sprintf(
                "type %s %s {\n",
                $this->goTypeName(),
                $this->type()
            );
        }
        else
        {
            $go = sprintf(
                "%s {\n",
                $this->type()
            );
        }

        foreach($this->children() as $child)
        {
            $go = sprintf(
                '%s%s%s',
                $go,
                static::indents($indentLevel + 1),
                $child->goName()
            );

            if (($child instanceof StructType || $child instanceof SliceType) && $this->configuration->breakOutInlineStructs())
            {
                // Add the child struct to the output list...
                $output[] = $child->toGO();

                $go = sprintf(
                    '%s *%s `json:"%s%s"`',
                    $go,
                    $child->goTypeName(),
                    $child->name(),
                    $child->isAlwaysDefined() ? '' : ',omitempty'
                );
            }
            else
            {
                $go = sprintf(
                    '%s %s `json:"%s%s"`',
                    $go,
                    $child->toGO($indentLevel + 2),
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