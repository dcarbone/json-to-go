<?php namespace DCarbone\JSONToGO\Types;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class StructType
 * @package DCarbone\JSONToGO\Types
 */
class StructType extends AbstractType implements TypeParent {
    /** @var \DCarbone\JSONToGO\Types\Type[] */
    protected $fields = [];

    /**
     * @return array
     */
    public function __debugInfo() {
        return parent::__debugInfo() + ['fields' => $this->fields];
    }

    /**
     * @return string
     */
    public function type(): string {
        return GOTYPE_STRUCT;
    }

    /**
     * @return \DCarbone\JSONToGO\Types\Type[]
     */
    public function fields(): array {
        return $this->fields;
    }

    /**
     * @param \DCarbone\JSONToGO\Types\Type $child
     * @return \DCarbone\JSONToGO\Types\StructType
     */
    public function addChild(Type $child): StructType {
        $child->setParent($this);
        $this->fields[$child->name()] = $child;
        return $this;
    }

    /**
     * @param int $indentLevel
     * @return string
     */
    public function toGO(int $indentLevel = 0): string {
        $output = [];

        $breakOutInlineStructs = $this->configuration->breakOutInlineStructs();
        $parent = $this->parent();

        if ($breakOutInlineStructs || null === $parent) {
            $go = sprintf(
                "type %s %s {\n",
                $this->goTypeName(),
                $this->type()
            );
        } else {
            $go = sprintf(
                "%s {\n",
                $this->type()
            );
        }

        foreach ($this->fields() as $field) {
            if ($this->configuration->callbacks()->isFieldIgnored($this->configuration, $this, $field)) {
                $this->configuration->logger()->info(sprintf('[json-to-go] Ignoring field "%s" in struct "%s"',
                    $field->name(),
                    $this->name()));
                continue;
            }

            $this->configuration->logger()->debug(sprintf('[json-to-go] Writing field "%s" in struct "%s"',
                $field->name(),
                $this->name()));

            $exported = $this->configuration->callbacks()->isFieldExported($this->configuration, $this, $field);

            $go = sprintf(
                '%s%s%s',
                $go,
                static::indents($indentLevel + 1),
                $exported ? $field->goName() : lcfirst($field->goName())
            );

            $fieldTag = $this->configuration->callbacks()->buildStructFieldTag($this->configuration, $this, $field);

            $fieldTag = trim($fieldTag, " \t\n\r\0\x0B`");
            if ('' !== $fieldTag) {
                $fieldTag = sprintf(' `%s`', $fieldTag);
            }

            if ($breakOutInlineStructs && !($field instanceof SimpleType || $field instanceof InterfaceType)) {
                // Add the child struct to the output list...
                $output[] = $field->toGO();

                if ($field instanceof StructType) {
                    $go = sprintf(
                        '%s *%s%s',
                        $go,
                        $field->goTypeName(),
                        $fieldTag
                    );
                } else if ($field instanceof SliceType) {
                    $go = sprintf(
                        '%s %s%s',
                        $go,
                        $field->goTypeSliceName(),
                        $fieldTag
                    );
                } else if ($field instanceof MapType) {
                    $go = sprintf(
                        '%s %s%s',
                        $go,
                        $field->goTypeMapName(),
                        $fieldTag
                    );
                }
            } else {
                $go = sprintf(
                    '%s %s%s',
                    $go,
                    $field->toGO($indentLevel + 2),
                    $fieldTag
                );
            }

            $go = sprintf("%s\n", $go);
        }

        $output[] = sprintf("%s\n%s}", rtrim($go), static::indents($indentLevel));

        return implode("\n\n", $output);
    }
}