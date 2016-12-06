<?php namespace DCarbone\JSONToGO\Types;

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
        return 'interface';
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '';
    }
}