<?php namespace DCarbone;

/**
 * Logic for this class comes from https://github.com/mholt/json-to-go/blob/master/json-to-go.js
 */
class JSONToGO
{
    /** @var string */
    protected $input = '';

    /** @var mixed */
    protected $decoded = null;

    /** @var string */
    protected $structName = '';

    /** @var bool */
    protected $forceOmitEmpty = false;

    /** @var string */
    protected $go = '';

    /** @var int */
    protected $tabs = 0;

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
     * @param bool   $forceOmitEmpty
     */
    public function __construct($input, $typeName = '', $forceOmitEmpty = false)
    {
        $this->input = (string)$input;
        $this->structName = (string)$typeName;
        $this->forceOmitEmpty = (bool)$forceOmitEmpty;
    }

    public function __invoke($input, $structName = '', $forceOmitEmpty = false)
    {
        $new = new static($input, $structName, $forceOmitEmpty);
        return $new->generate();
    }

    public static function parse($input, $structName = '', $forceOmitEmpty = false)
    {
        $new = new static($input, $structName, $forceOmitEmpty);
        return $new->generate();
    }

    public function generate()
    {
        if ('' === $this->input)
            throw new \RuntimeException('Input is empty, please re-construct with valid input');

        $this->decoded = json_decode($this->input, true);
        if (JSON_ERROR_NONE !== json_last_error())
            throw new \RuntimeException(json_last_error_msg());

        if ('' !== $this->structName)
            $this->append(sprintf('type %s ', $this->structName));

        $this->parseScope($this->decoded);
        return $this->go;
    }

    protected function append($string)
    {
        $this->go = sprintf('%s%s', $this->go, $string);
    }

    protected function indent($tabs)
    {
        for ($i = 0; $i < $tabs; $i++)
        {
            $this->append("\t");
        }
    }

    protected function format($string)
    {
        if (!$string)
            return '';

        if (preg_match('/^\d+$/S', $string))
        {
            $string = sprintf('Num%s', $string);
        }
        else if (preg_match('/^\d/S', $string))
        {
            $string = static::$numbers[(int)substr($string, 0, 1)] . substr($string, 1);
        }

        $string = $this->toProperCase($string);
        if (preg_match('/^[^a-zA-Z]/S', $string))
            $string = sprintf('X%s', $string);

        return preg_replace('/[^a-zA-Z0-9]/S', '', $string);
    }

    protected function parseScope($scope)
    {
        if (is_array($scope))
        {
            $key = key($scope);
            if (is_int($key))
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

                $this->append('[]');

                if ('struct' === $sliceType)
                {
                    $allFields = [];
                    foreach($scope as $item)
                    {
                        foreach(array_keys($scope) as $key)
                        {
                            if (!isset($allFields[$key]))
                            {
                                $allFields[$key] = [
                                    'value' => $item[$key],
                                    'count' => 0,
                                ];
                            }

                            $allFields[$key]['count']++;
                        }
                    }

                    $struct = [];
                    $omitempty = [];
                    foreach(array_keys($allFields) as $key)
                    {
                        $struct[$key] = $allFields[$key]['value'];
                        $omitempty[$key] = $allFields[$key]['count'] !== $scopeLength;
                    }

                    $this->parseStruct($struct, $omitempty);
                }
                else if ('slice' === $sliceType)
                {
                    $this->parseScope(reset($scope));
                }
                else
                {
                    $this->append($sliceType ? $sliceType : 'interface{}');
                }
            }
            else
            {
                $this->parseStruct($scope);
            }
        }
        else
        {
            $this->append($this->goType($scope));
        }
    }

    protected function parseStruct(array $scope, array $omitempty = array())
    {
        $this->append("struct {\n");
        $this->tabs++;
        foreach(array_keys($scope) as $key)
        {
            $this->indent($this->tabs);
            $this->append($this->format($key) . ' ');
            $this->parseScope($scope[$key]);
            $this->append(' `json:"' . $key);
            if ($this->forceOmitEmpty || in_array($key, $omitempty, true))
                $this->append(',omitempty');
            $this->append("\"`\n");
        }
        $this->indent(--$this->tabs);
        $this->append('}');
    }

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

    protected function mostSpecificPossibleGoType($type1, $type2)
    {
        if ('float' === substr($type1, 0, 5) && 'int' === substr($type2, 0, 3))
            return $type1;

        if ('int' === substr($type1, 0, 3) && 'float' === substr($type2, 0, 5))
            return $type1;

        return 'interface{}';
    }

    protected function toProperCase($string)
    {
        $commonInitialisms = static::$commonInitialisms;

        return preg_replace_callback('/([A-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = mb_strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return $item;
        }, preg_replace_callback('/(^|[^a-zA-Z])([a-z]+)/S', function($item) use ($commonInitialisms) {
            $item = reset($item);
            $upper = mb_strtoupper($item);
            if (in_array($upper, $commonInitialisms, true))
                return $upper;
            return ucfirst(strtolower($item));
        }, $string));
    }
}