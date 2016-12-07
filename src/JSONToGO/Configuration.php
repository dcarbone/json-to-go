<?php namespace DCarbone\JSONToGO;

/*
 * Copyright (C) 2016 Daniel Carbone (daniel.p.carbone@gmail.com)
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
        'breakOutInlineStructs' => true,
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
        ]
    ];

    /** @var bool */
    protected $forceOmitEmpty;
    /** @var bool */
    protected $forceIntToFloat;
    /** @var bool */
    protected $forceScalarToPointer;
    /** @var bool */
    protected $breakOutInlineStructs;

    /** @var string[] */
    protected $initialNumberMap;

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
     * @return \DCarbone\JSONToGO\Configuration
     */
    public static function newDefaultConfiguration()
    {
        $c = new static;
        foreach(static::$defaultConfigurationValues as $k => $v)
        {
            $c->{$k} = $v;
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

            $c->{$k} = $v;
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
     * @return bool
     */
    public function forceIntToFloat()
    {
        return $this->forceIntToFloat;
    }

    /**
     * @return bool
     */
    public function forceScalarToPointer()
    {
        return $this->forceScalarToPointer;
    }

    /**
     * @return bool
     */
    public function breakOutInlineStructs()
    {
        return $this->breakOutInlineStructs;
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