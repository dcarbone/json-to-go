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
class MapType extends AbstractType
{
    /** @var AbstractType */
    protected $mapType;

    /**
     * @return array
     */
    public function __debugInfo()
    {
        return parent::__debugInfo() + ['mapType' => $this->mapType()];
    }

    /**
     * @return string
     */
    public function type()
    {
        return GOTYPE_MAP;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\AbstractType $mapType
     * @return MapType
     */
    public function setMapType(AbstractType $mapType)
    {
        $mapType->setParent($this);
        $this->mapType = $mapType;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public function mapType()
    {
        return $this->mapType;
    }

    /**
     * @return string
     */
    public function goTypeMapName()
    {
        return sprintf('%sMap', $this->goTypeName());
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO($indentLevel = 0)
    {
        $output = [];

        $mapType = $this->mapType();

        $parent = $this->parent();

        if ($this->configuration->breakOutInlineStructs())
        {
            if ($mapType instanceof StructType)
            {
                $output[] = sprintf('type %s map[string]*%s', $this->goTypeMapName(), $this->goTypeName());
                $output[] = $mapType->toGO($indentLevel);
            }
            else if (null === $parent)
            {
                $output[] = sprintf('type %s map[string]%s', $this->goTypeName(), $mapType->toGO());
            }
            else if ($parent instanceof SliceType || $parent instanceof MapType)
            {
                $output[] = sprintf('map[string]%s', $mapType->toGO($indentLevel));
            }
            else
            {
                $output[] = sprintf('type %s map[string]%s', $this->goTypeMapName(), $mapType->toGO());
            }
        }
        else if (null === $parent)
        {
            $output[] = sprintf(
                'type %s map[string]%s',
                $this->goTypeName(),
                $mapType->toGO($indentLevel)
            );
        }
        else
        {
            $output[] = sprintf('map[string]%s', $mapType->toGO($indentLevel));
        }

        return implode("\n\n", $output);
    }
}