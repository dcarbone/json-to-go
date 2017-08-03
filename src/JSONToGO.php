<?php namespace DCarbone;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;
use DCarbone\JSONToGO\Parser;
use DCarbone\JSONToGO\Types\Type;

/**
 * Class JSONToGO
 *
 * @package DCarbone
 */
class JSONToGO {

    const TYPE_NAME_REGEX = '^[a-zA-Z][a-zA-Z0-9]*$';

    /** @var \DCarbone\JSONToGO\Configuration */
    protected $configuration;

    /**
     * JSONToGO Constructor
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     */
    public function __construct(Configuration $configuration) {
        $this->configuration = $configuration;
    }

    /**
     * @param string $typeName
     * @param string $input
     * @param \DCarbone\JSONToGO\Configuration|null $configuration
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function __invoke(string $typeName, string $input, Configuration $configuration = null): Type {
        if (null === $configuration) {
            $configuration = new Configuration();
        }

        $new = new static($configuration);

        return $new->generate($typeName, $input);
    }

    /**
     * @param string $typeName
     * @param string $input
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function parse(string $typeName, string $input, Configuration $configuration = null): Type {
        if (null === $configuration) {
            $configuration = new Configuration();
        }

        $new = new static($configuration);

        return $new->generate($typeName, $input);
    }

    /**
     * @param string $typeName
     * @param mixed $decodedInput
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public static function parseDecoded(string $typeName,
                                        $decodedInput,
                                        Configuration $configuration = null): Type {
        if (null === $configuration) {
            $configuration = new Configuration();
        }

        $encoded = json_encode($decodedInput);
        if (JSON_ERROR_NONE === json_last_error()) {
            $new = new static($configuration);
            return $new->generate($typeName, $encoded);
        }

        throw new \InvalidArgumentException('Unable to encode input: ' . json_last_error_msg());
    }

    /**
     * @param string $typeName
     * @param string $input
     * @return \DCarbone\JSONToGO\Types\Type
     */
    public function generate(string $typeName, string $input): Type {
        $typeName = trim($typeName);
        if ('' === $typeName || !preg_match('/' . self::TYPE_NAME_REGEX . '/', $typeName)) {
            throw new \InvalidArgumentException(get_class($this) .
                '::generate - Root type name must follow "' . self::TYPE_NAME_REGEX . '", ' .
                $typeName .
                ' does not.');
        }

        $input = trim($input);
        if ('' === $input) {
            throw new \RuntimeException(get_class($this) .
                '::generate - Input is empty, please re-construct with valid input');
        }

        $decoded = json_decode($input);
        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \RuntimeException(get_class($this) .
                '::generate - Unable to json_decode input: ' .
                json_last_error_msg());
        }

        if ($this->configuration->sanitizeInput()) {
            $decoded = $this->configuration->callbacks()->sanitizeInput($this->configuration, $decoded);
        }

        return Parser::parseType($this->configuration, $typeName, $decoded);
    }
}