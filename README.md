# json-to-go
PHP Implementation of mholt/json-to-go

## Composer

```json
{
    "require": {
        "dcarbone/json-to-go": "0.2.*"
    }
}
```

## Why Do This in PHP?

Because it fits better into my personal workflow.

Also because why not.

## Basic Usage

Once included in your project, the easiest way to use it is probably using the static initializers:

```php
$jsonToGO = \DCarbone\JSONToGO::parse($myjson);
```

This will return to you an instance of [JSONToGO](./src/JSONToGO.php) with your input parsed.  If there was an issue
during parsing, an exception will be thrown.

This class implements `__toString()`, and the return value is the parsed GO object.
