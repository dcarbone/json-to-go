<?php namespace DCarbone\JSONToGO\Types;

/**
 * Class StructType
 *
 * @package DCarbone\JSONToGO\Types
 */
class StructType extends AbstractType
{
    /** @var \DCarbone\JSONToGO\Types\StructType */
    private $parent = null;

    /** @var \DCarbone\JSONToGO\Types\StructType[] */
    private $children = [];

    /** @var \stdClass */
    private $definition;

    /**
     * StructType constructor.
     *
     * @param string $name
     * @param \stdClass $definition
     */
    public function __construct($name, \stdClass $definition)
    {
        parent::__construct($name);
        $this->definition = $definition;
    }

    /**
     * @return string
     */
    public function type()
    {
        return 'struct';
    }

    /**
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public function parent()
    {
        return $this->parent;
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
     * @param \DCarbone\JSONToGO\Types\StructType $child
     * @return StructType
     */
    public function addChild(StructType $child)
    {
        $this->children[$child->name()] = $child;
        return $this;
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return '';
    }

    protected function parse()
    {
        $this->append("struct {\n");
        $this->tabs++;
        foreach(get_object_vars($scope) as $key => $value)
        {
            $propertyName = $this->formatPropertyName($key);

            $this->indent($this->tabs);
            $this->append($propertyName . ' ');
            $this->parseScope($value, $propertyName);
            $this->append(' `json:"' . $key);
            if ($this->forceOmitEmpty || in_array($key, $omitempty, true))
                $this->append(',omitempty');
            $this->append("\"`\n");
        }
        $this->tabs--;
        $this->indent($this->tabs);
        $this->append('}');
    }
}