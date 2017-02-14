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
class Configuration implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array */
    protected static $defaultConfigurationValues = [
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

    /** @var string[] */
    protected static $commonInitialisms = [
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

    /**
     * Configuration constructor.
     *
     * @param array $config
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(array $config = [], LoggerInterface $logger = null)
    {
        if (null === $logger)
            $logger = new NullLogger();

        $this->setLogger($logger);

        foreach(static::$defaultConfigurationValues as $configKey => $defaultValue)
        {
            if (isset($config[$configKey]))
                $value = $config[$configKey];
            else
                $value = $defaultValue;

            if ('callbacks' === $configKey && null == $value)
                $value = new Callbacks();

            $this->{sprintf('set%s', ucfirst($configKey))}($value);
        }
    }

    /**
     * @deprecated
     * @return Configuration
     */
    public static function newDefaultConfiguration()
    {
        return new static();
    }

    /**
     * @deprecated
     * @param array $conf
     * @return Configuration
     */
    public static function newConfiguration(array $conf = [])
    {
        return new static($conf);
    }

    /**
     * @return \Psr\Log\LoggerInterface
     */
    public function logger()
    {
        return $this->logger;
    }

    /**
     * @return bool
     */
    public function forceOmitEmpty()
    {
        return $this->forceOmitEmpty;
    }

    /**
     * @param bool $forceOmitEmpty
     * @return Configuration
     */
    public function setForceOmitEmpty($forceOmitEmpty)
    {
        $this->forceOmitEmpty = (bool)$forceOmitEmpty;
        return $this;
    }

    /**
     * @return bool
     */
    public function forceIntToFloat()
    {
        return $this->forceIntToFloat;
    }

    /**
     * @param bool $intToFloat
     * @return Configuration
     */
    public function setForceIntToFloat($intToFloat)
    {
        $this->forceIntToFloat = (bool)$intToFloat;
        return $this;
    }

    /**
     * @return bool
     */
    public function useSimpleInt()
    {
        return $this->useSimpleInt;
    }

    /**
     * @param bool $simpleInt
     * @return bool
     */
    public function setUseSimpleInt($simpleInt)
    {
        $this->useSimpleInt = (bool)$simpleInt;
        return $this->useSimpleInt;
    }

    /**
     * @return bool
     */
    public function forceScalarToPointer()
    {
        return $this->forceScalarToPointer;
    }

    /**
     * @param bool $forceScalarToPointer
     * @return Configuration
     */
    public function setForceScalarToPointer($forceScalarToPointer)
    {
        $this->forceScalarToPointer = (bool)$forceScalarToPointer;
        return $this;
    }

    /**
     * @return bool
     */
    public function emptyStructToInterface()
    {
        return $this->emptyStructToInterface;
    }

    /**
     * @param bool $emptyStructToInterface
     * @return Configuration
     */
    public function setEmptyStructToInterface($emptyStructToInterface)
    {
        $this->emptyStructToInterface = (bool)$emptyStructToInterface;
        return $this;
    }

    /**
     * @return bool
     */
    public function breakOutInlineStructs()
    {
        return $this->breakOutInlineStructs;
    }

    /**
     * @param bool $breakOutInlineStructs
     * @return Configuration
     */
    public function setBreakOutInlineStructs($breakOutInlineStructs)
    {
        $this->breakOutInlineStructs = (bool)$breakOutInlineStructs;
        return $this;
    }

    /**
     * @return bool
     */
    public function sanitizeInput()
    {
        return $this->sanitizeInput;
    }

    /**
     * @param bool $sanitizeInput
     * @return Configuration
     */
    public function setSanitizeInput($sanitizeInput)
    {
        $this->sanitizeInput = (bool)$sanitizeInput;
        return $this;
    }

    /**
     * @return \DCarbone\JSONToGO\Callbacks
     */
    public function callbacks()
    {
        return $this->callbacks;
    }

    /**
     * @param Callbacks|array $callbacks
     * @return Configuration
     */
    public function setCallbacks($callbacks)
    {
        if ($callbacks instanceof Callbacks)
        {
            $this->callbacks = $callbacks;
        }
        else if (is_array($callbacks))
        {
            $this->callbacks = new Callbacks($callbacks);
        }
        else
        {
            throw new \InvalidArgumentException(
                'Configuration: Type mismatch: "callbacks" must either be instance of ' .
                '\\DCarbone\\JSONToGo\\Callbacks or an array of callback type => callable'
            );
        }

        return $this;
    }

    /**
     * @return string[]
     */
    public function commonInitialisms()
    {
        return static::$commonInitialisms;
    }

    /**
     * @param string|int $number
     * @return string
     */
    public function numberToWord($number)
    {
       return $this->initialNumberMap[(int)$number];
    }

    /**
     * @return string[]
     */
    public function initialNumberMap()
    {
        return $this->initialNumberMap;
    }

    /**
     * @param array $initialNumberMap
     * @return Configuration
     */
    public function setInitialNumberMap(array $initialNumberMap)
    {
        $this->initialNumberMap = $initialNumberMap;
        return $this;
    }
}