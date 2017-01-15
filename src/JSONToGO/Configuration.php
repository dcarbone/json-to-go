<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016-2017 Daniel Carbone (daniel.p.carbone@gmail.com)
 *
 * This software may be modified and distributed under the terms
 * of the MIT license.  See the LICENSE file for details.
 */

/**
 * Class Configuration
 *
 * @package DCarbone\JSONToGO
 */
class Configuration
{
    /** @var array */
    protected static $defaultConfigurationValues = [
        'forceOmitEmpty' => false,
        'forceIntToFloat' => false,
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
        'callbacks' => true,
    ];

    /** @var bool */
    protected $forceOmitEmpty;
    /** @var bool */
    protected $forceIntToFloat;
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
     * @internal
     */
    protected function __construct() {}

    /**
     * @return \DCarbone\JSONToGO\Configuration
     */
    public static function newDefaultConfiguration()
    {
        $c = new static;
        foreach(static::$defaultConfigurationValues as $k => $v)
        {
            if ('callbacks' === $k)
                $c->callbacks = new Callbacks();
            else
                $c->$k = $v;
        }
        return $c;
    }

    /**
     * @param array $conf
     * @return \DCarbone\JSONToGO\Configuration
     */
    public static function newConfiguration(array $conf)
    {
        $c = static::newDefaultConfiguration();
        foreach($conf as $k => $v)
        {
            if (!isset(static::$defaultConfigurationValues[$k]))
                throw new \InvalidArgumentException(sprintf('Configuration: No configuration property "%s" found.', $k));

            if ('callbacks' === strtolower($k))
            {
                $c->setCallbacks($v);
            }
            else
            {
                $t = gettype($v);
                $dt = gettype(static::$defaultConfigurationValues[$k]);

                if ($t !== $dt)
                {
                    throw new \InvalidArgumentException(sprintf(
                        'Configuration: Type mismatch.  Expected "%s", saw "%s" for key "%s"',
                        $dt,
                        $t,
                        $k
                    ));
                }

                $c->$k = $v;
            }
        }

        return $c;
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
}