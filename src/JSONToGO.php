<?php namespace DCarbone;

use DCarbone\JSONToGO\Types\InterfaceType;
use DCarbone\JSONToGO\Types\SimpleType;
use DCarbone\JSONToGO\Types\StructType;

/**
 * Much of the logic for this class comes from https://github.com/mholt/json-to-go/blob/master/json-to-go.js
 */
class JSONToGO
{
    /** @var array */
    public static $specialCharacterRewriteMap = [
        '.' => 'DOT',
        '_' => 'UNDERSCORE',
        '-' => 'HYPHEN',
    ];

    /** @var string */
    protected $input = '';

    /** @var mixed */
    protected $decoded = null;

    /** @var string */
    protected $typeName;

    /** @var bool */
    protected $forceOmitEmpty = false;

    /** @var bool */
    protected $forceIntToFloat = false;

    /** @var bool */
    protected $forceScalarToPointer = false;

    /** @var string */
    protected $go = '';

    /** @var int */
    protected $tabs = 0;

    /** @var bool */
    protected $generated = false;

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

    /** @var string[] */
    protected static $numbers = [
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
    ];

    /**
     * JSONToGO Constructor
     *
     * @param string $input
     * @param string $typeName
     * @param bool $forceOmitEmpty
     * @param bool $forceIntToFloat
     * @param bool $forceScalarToPointer
     */
    public function __construct($input,
                                $typeName,
                                $forceOmitEmpty = false,
                                $forceIntToFloat = false,
                                $forceScalarToPointer = false)
    {
        $this->input = trim((string)$input);
        $this->typeName = trim((string)$typeName);
        $this->forceOmitEmpty = (bool)$forceOmitEmpty;
        $this->intToFloat = (bool)$forceIntToFloat;
        $this->forceScalarToPointer = (bool)$forceScalarToPointer;
    }

    /**
     * @param string $input
     * @param string $typeName
     * @param bool $forceOmitEmpty
     * @param bool $forceIntToFloat
     * @param bool $forceScalarToPointer
     * @return \DCarbone\JSONToGO
     */
    public function __invoke($input,
                             $typeName,
                             $forceOmitEmpty = false,
                             $forceIntToFloat = false,
                             $forceScalarToPointer = false)
    {
        $new = new static($input, $typeName, $forceOmitEmpty, $forceIntToFloat, $forceScalarToPointer);
        return $new->generate();
    }

    /**
     * @param string $input
     * @param string $typeName
     * @param bool $forceOmitEmpty
     * @param bool $forceIntToFloat
     * @param bool $forceScalarToPointer
     * @return \DCarbone\JSONToGO
     */
    public static function parse($input,
                                 $typeName,
                                 $forceOmitEmpty = false,
                                 $forceIntToFloat = false,
                                 $forceScalarToPointer = false)
    {
        $new = new static($input, $typeName, $forceOmitEmpty, $forceIntToFloat, $forceScalarToPointer);
        return $new->generate();
    }

    /**
     * @param mixed $decodedInput
     * @param string $typeName
     * @param bool $forceOmitEmpty
     * @param bool $forceIntToFloat
     * @param bool $forceScalarToPointer
     * @return \DCarbone\JSONToGO
     */
    public static function parseDecoded($decodedInput,
                                        $typeName,
                                        $forceOmitEmpty = false,
                                        $forceIntToFloat = false,
                                        $forceScalarToPointer = false)
    {
        $encoded = json_encode($decodedInput);
        if (JSON_ERROR_NONE === json_last_error())
        {
            $new = new static($encoded, $typeName, $forceOmitEmpty, $forceIntToFloat, $forceScalarToPointer);
            return $new->generate();
        }

        throw new \InvalidArgumentException('Unable to encode input: ' . json_last_error_msg());
    }

    /**
     * @return string
     */
    public function getInput()
    {
        return $this->input;
    }

    /**
     * @return mixed
     */
    public function getDecoded()
    {
        return $this->decoded;
    }

    /**
     * @return string
     */
    public function getTypeName()
    {
        return $this->typeName;
    }

    /**
     * @return boolean
     */
    public function isForceOmitEmpty()
    {
        return $this->forceOmitEmpty;
    }

    /**
     * @return boolean
     */
    public function isIntToFloat()
    {
        return $this->intToFloat;
    }

    /**
     * @return boolean
     */
    public function isForceScalarToPointer()
    {
        return $this->forceScalarToPointer;
    }

    /**
     * @return JSONToGO
     */
    public function generate()
    {
        if (!$this->generated)
        {
            if ('' === $this->input)
                throw new \RuntimeException(get_class($this).'::generate - Input is empty, please re-construct with valid input');

            $this->decoded = json_decode($this->input);
            if (JSON_ERROR_NONE !== json_last_error())
                throw new \RuntimeException(json_last_error_msg());

            $this->parseScope($this->decoded, $this->typeName);
            $this->generated = true;
        }

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->go;
    }

//    /**
//     * @param string $string
//     */
//    protected function append($string)
//    {
//        $this->go = sprintf('%s%s', $this->go, $string);
//    }
//
//    /**
//     * @param int $tabs
//     */
//    protected function indent($tabs)
//    {
//        $this->append(str_repeat("\t", (int)$tabs));
//    }

    /**
     * @param string $propertyName
     * @return string
     */
    protected function formatPropertyName($propertyName)
    {
        if (!$propertyName)
            return '';

        // If entire name is a number...
        if (preg_match('/^\d+$/S', $propertyName))
        {
            $propertyName = sprintf('Num%s', $propertyName);
        }
        // If first character of name is a number...
        else if (preg_match('/^\d/S', $propertyName))
        {
            $propertyName = static::$numbers[(int)substr($propertyName, 0, 1)] . substr($propertyName, 1);
        }

        // Case it
        $propertyName = $this->toProperCase($propertyName);

        // Then, if this starts with anything other than an alpha character prefix with X
        if (preg_match('/^[^a-zA-Z]/S', $propertyName))
            $propertyName = sprintf('X%s', $propertyName);

        // Replace special characters, if map is not empty...
        if (0 < count(static::$specialCharacterRewriteMap))
        {
            $propertyName = str_replace(
                array_keys(static::$specialCharacterRewriteMap),
                array_values(static::$specialCharacterRewriteMap),
                $propertyName
            );
        }

        // Finally, strip out everything that was not caught above and is not an alphanumeric character.
        return preg_replace('/[^a-zA-Z0-9]/S', '', $propertyName);
    }

    /**
     * @param mixed $scope
     * @param $name
     * @return \DCarbone\JSONToGO\Types\AbstractType
     */
    protected function parseScope($scope, $name)
    {
        $scopeType = $this->goType($scope);

        if ('struct' === $scopeType)
        {
            $type = new StructType($name, $scope);
        }
        else if ('slice' === $scopeType)
        {
            $sliceType = null;
            $scopeLength = count($scope);

            foreach($scope as $item)
            {
                $thisType = $this->goType($item);

                if (null === $sliceType)
                {
                    $sliceType = $thisType;
                }
                else if ($sliceType !== $thisType)
                {
                    $sliceType = $this->mostSpecificPossibleGoType($thisType, $sliceType);
                    if ('interface{}' === $sliceType)
                        break;
                }
            }

            if ('struct' === $sliceType)
            {
                $allFields = [];
                foreach($scope as $item)
                {
                    foreach(get_object_vars($item) as $key => $value)
                    {
                        if (!isset($allFields[$key]))
                        {
                            $allFields[$key] = [
                                'value' => $value,
                                'count' => 0,
                            ];
                        }

                        $allFields[$key]['count']++;
                    }
                }

                $struct = new \stdClass();
                $omitempty = [];
                foreach(array_keys($allFields) as $key)
                {
                    $struct->$key = $allFields[$key]['value'];
                    $omitempty[$key] = $allFields[$key]['count'] !== $scopeLength;
                }

                $type = new StructType($name, $struct);
                $type->collection();
            }
            else if ('slice' === $sliceType)
            {
                $type = $this->parseScope(reset($scope), $name);
                $type->collection();
            }
            else if ('interface{}' === $sliceType)
            {
                $type = new InterfaceType($name);
            }
            else
            {
                $type = $sliceType ? new SimpleType($name, $sliceType) : new InterfaceType($name);
            }
        }
        else if ('interface{}' === $scopeType)
        {
            $type = new InterfaceType($name);
        }
        else
        {
            $type = new SimpleType($name, $scopeType);
        }

        return $type;
    }

    /**
     * @param mixed $val
     * @return string
     */
    protected function goType($val)
    {
        if (null === $val)
            return 'interface{}';

        $type = gettype($val);

        if ('string' === $type)
        {
            if (preg_match('/\d{4}-\d\d-\d\dT\d\d:\d\d:\d\d(\.\d+)?(\+\d\d:\d\d|Z)/S', $val))
                return 'time.Time';

            return 'string';
        }

        if ('integer' === $type)
        {
            if ($this->intToFloat)
                return 'float64';

            if ($val > -2147483648 && $val < 2147483647)
                return 'int';

            return 'int64';
        }

        if ('boolean' === $type)
            return 'bool';

        if ('double' === $type)
            return 'float64';

        if ('array' === $type)
            return 'slice';

        if ('object' === $type)
            return 'struct';

        return 'interface{}';
    }

    /**
     * @param string $type1
     * @param string $type2
     * @return string
     */
    protected function mostSpecificPossibleGoType($type1, $type2)
    {
        if ('float' === substr($type1, 0, 5) && 'int' === substr($type2, 0, 3))
            return $type1;

        if ('int' === substr($type1, 0, 3) && 'float' === substr($type2, 0, 5))
            return $type1;

        return 'interface{}';
    }

    /**
     * @param string $string
     * @return string
     */
    protected function toProperCase($string)
    {
        $commonInitialisms = static::$commonInitialisms;

        return preg_replace_callback('/([A-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return $item;
        }, preg_replace_callback('/(^|[^a-zA-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return ucfirst(strtolower($item));
        }, $string));
    }
}