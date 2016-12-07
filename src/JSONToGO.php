<?php namespace DCarbone;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use DCarbone\JSONToGO\Configuration;
use DCarbone\JSONToGO\Parser;

/**
 * Class JSONToGO
 *
 * @package DCarbone
 */
class JSONToGO
{
    /** @var array */
    public static $specialCharacterRewriteMap = [
        '.' => 'DOT',
        '_' => 'UNDERSCORE',
        '-' => 'HYPHEN',
    ];

    /** @var \DCarbone\JSONToGO\Configuration */
    protected $configuration;

    /** @var mixed */
    protected $decoded = null;


    /**
     * JSONToGO Constructor
     *
     * @param \DCarbone\JSONToGO\Configuration $configuration
     */
    public function __construct(Configuration $configuration)
    {
        $this->configuration = $configuration;
    }

    /**
     * @param string $typeName
     * @param string $input
     * @param \DCarbone\JSONToGO\Configuration|null $configuration
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public function __invoke($typeName, $input, Configuration $configuration = null)
    {
        if (null === $configuration)
            $configuration = Configuration::newDefaultConfiguration();

        $new = new static($configuration);


        return $new->generate($typeName, $input);
    }

    /**
     * @param string $typeName
     * @param string $input
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parse($typeName, $input, Configuration $configuration = null)
    {
        if (null === $configuration)
            $configuration = Configuration::newDefaultConfiguration();

        $new = new static($configuration);


        return $new->generate($typeName, $input);
    }

    /**
     * @param string $typeName
     * @param mixed $decodedInput
     * @param \DCarbone\JSONToGO\Configuration $configuration
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public static function parseDecoded($typeName, $decodedInput, Configuration $configuration = null)
    {
        if (null === $configuration)
            $configuration = Configuration::newDefaultConfiguration();

        $encoded = json_encode($decodedInput);
        if (JSON_ERROR_NONE === json_last_error())
        {
            $new = new static($configuration);
            return $new->generate($typeName, $encoded);
        }

        throw new \InvalidArgumentException('Unable to encode input: ' . json_last_error_msg());
    }

    /**
     * @param string $typeName
     * @param string $input
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    public function generate($typeName, $input)
    {
        if ('' === $input)
            throw new \RuntimeException(get_class($this).'::generate - Input is empty, please re-construct with valid input');

        $decoded = json_decode($input);
        if (JSON_ERROR_NONE !== json_last_error())
            throw new \RuntimeException(json_last_error_msg());

        return Parser::parseType($this->configuration, $decoded, $typeName);
    }
}