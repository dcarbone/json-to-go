<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Class Configuration
 *
 * @package DCarbone\JSONToGO
 */
class Configuration implements LoggerAwareInterface {
    use LoggerAwareTrait;

    const DEFAULT_VALUES = [
        'forceOmitEmpty' => false,
        'forceIntToFloat' => false,
        'useSimpleInt' => false,
        'forceScalarToPointer' => false,
        'emptyStructToInterface' => false,
        'breakOutInlineStructs' => true,
        'sanitizeInput' => false,
        'initialNumberMap' => [
            'Zero_',
            'One_',
            'Two_',
            'Three_',
            'Four_',
            'Five_',
            'Six_',
            'Seven_',
            'Eight_',
            'Nine_',
        ],
        'callbacks' => null,
    ];

    const COMMON_INITIALISISMS = [
        'API',
        'ASCII',
        'CPU',
        'CSS',
        'DNS',
        'EOF',
        'GUID',
        'HTML',
        'HTTP',
        'HTTPS',
        'ID',
        'IP',
        'JSON',
        'LHS',
        'QPS',
        'RAM',
        'RHS',
        'RPC',
        'SLA',
        'SMTP',
        'SSH',
        'TCP',
        'TLS',
        'TTL',
        'UDP',
        'UI',
        'UID',
        'UUID',
        'URI',
        'URL',
        'UTF8',
        'VM',
        'XML',
        'XSRF',
        'XSS',
    ];

    /** @var bool */
    protected $forceOmitEmpty;
    /** @var bool */
    protected $forceIntToFloat;
    /** @var bool */
    protected $useSimpleInt;
    /** @var bool */
    protected $forceScalarToPointer;
    /** @var bool */
    protected $breakOutInlineStructs;
    /** @var bool */
    protected $emptyStructToInterface;
    /** @var bool */
    protected $sanitizeInput;

    /** @var string[] */
    protected $initialNumberMap;

    /** @var Callbacks */
    protected $callbacks;

    /**
     * Configuration constructor.
     *
     * @param array $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(array $config = [], LoggerInterface $logger = null) {
        if (null === $logger) {
            $logger = new NullLogger();
        }

        $this->setLogger($logger);

        foreach (self::DEFAULT_VALUES as $configKey => $defaultValue) {
            if (isset($config[$configKey])) {
                $value = $config[$configKey];
            } else {
                $value = $defaultValue;
            }

            if ('callbacks' === $configKey && null == $value) {
                $value = new Callbacks();
            }

            $this->{sprintf('set%s', ucfirst($configKey))}($value);
        }
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function logger(): LoggerInterface {
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function forceOmitEmpty(): bool {
        return $this->forceOmitEmpty;
    }

    /**
     * @param bool $forceOmitEmpty
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setForceOmitEmpty(bool $forceOmitEmpty): Configuration {
        $this->forceOmitEmpty = $forceOmitEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function forceIntToFloat(): bool {
        return $this->forceIntToFloat;
    }

    /**
     * @param bool $intToFloat
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setForceIntToFloat(bool $intToFloat): Configuration {
        $this->forceIntToFloat = $intToFloat;
        return $this;
    }

    /**
     * @return bool
     */
    public function useSimpleInt(): bool {
        return $this->useSimpleInt;
    }

    /**
     * @param bool $simpleInt
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setUseSimpleInt(bool $simpleInt): Configuration {
        $this->useSimpleInt = $simpleInt;
        return $this;
    }

    /**
     * @return bool
     */
    public function forceScalarToPointer(): bool {
        return $this->forceScalarToPointer;
    }

    /**
     * @param bool $forceScalarToPointer
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setForceScalarToPointer(bool $forceScalarToPointer): Configuration {
        $this->forceScalarToPointer = $forceScalarToPointer;
        return $this;
    }

    /**
     * @return bool
     */
    public function emptyStructToInterface(): bool {
        return $this->emptyStructToInterface;
    }

    /**
     * @param bool $emptyStructToInterface
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setEmptyStructToInterface(bool $emptyStructToInterface): Configuration {
        $this->emptyStructToInterface = $emptyStructToInterface;
        return $this;
    }

    /**
     * @return bool
     */
    public function breakOutInlineStructs(): bool {
        return $this->breakOutInlineStructs;
    }

    /**
     * @param bool $breakOutInlineStructs
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setBreakOutInlineStructs(bool $breakOutInlineStructs): Configuration {
        $this->breakOutInlineStructs = $breakOutInlineStructs;
        return $this;
    }

    /**
     * @return bool
     */
    public function sanitizeInput(): bool {
        return $this->sanitizeInput;
    }

    /**
     * @param bool $sanitizeInput
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setSanitizeInput(bool $sanitizeInput): Configuration {
        $this->sanitizeInput = $sanitizeInput;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function callbacks(): Callbacks {
        return $this->callbacks;
    }

    /**
     * @param \DCarbone\JSONToGO\Callbacks|array $callbacks
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setCallbacks($callbacks): Configuration {
        if ($callbacks instanceof Callbacks) {
            $this->callbacks = $callbacks;
        } else if (is_array($callbacks)) {
            $this->callbacks = new Callbacks($callbacks);
        } else {
            throw new \InvalidArgumentException(
                'Configuration: Type mismatch: "callbacks" must either be instance of ' .
                '\\DCarbone\\JSONToGo\\Callbacks or an array of callback type => callable'
            );
        }

        return $this;
    }

    /**
     * @param int $number
     * @return string
     */
    public function numberToWord(int $number): string {
        return $this->initialNumberMap[(int)$number];
    }

    /**
     * @return string[]
     */
    public function initialNumberMap(): array {
        return $this->initialNumberMap;
    }

    /**
     * @param array $initialNumberMap
     * @return \DCarbone\JSONToGO\Configuration
     */
    public function setInitialNumberMap(array $initialNumberMap): Configuration {
        $this->initialNumberMap = $initialNumberMap;
        return $this;
    }
}