<?php namespace DCarbone\JSONToGO\Types;

/**
 * Class SimpleType
 *
 * @package DCarbone\JSONToGO\Types
 */
class SimpleType extends AbstractType
{
    /** @var string */
    protected $type;

    /**
     * SimpleType constructor.
     *
     * @param string $name
     * @param string $type
     */
    public function __construct($name, $type)
    {
        parent::__construct($name);
        $this->type = $type;
    }

    /**
     * @return string
     */
    public function type()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '';
    }
}