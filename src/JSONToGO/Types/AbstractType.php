<?php namespace DCarbone\JSONToGO\Types;

/**
 * Class AbstractType
 *
 * @package DCarbone\JSONToGO\Types
 */
abstract class AbstractType
{
    /** @var string */
    protected $name;

    /** @var mixed */
    protected $definition;

    /** @var bool */
    protected $collection = false;

    /**
     * AbstractType constructor.
     *
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function name()
    {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function isCollection()
    {
        return $this->collection;
    }

    /**
     * @return AbstractType
     */
    public function collection()
    {
        $this->collection = true;
        return $this;
    }

    /**
     * @return string
     */
    abstract public function type();

    /**
     * @return string
     */
    abstract public function toJson();
}