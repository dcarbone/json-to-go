# json-to-go
PHP Implementation of mholt/json-to-go

## Composer

```json
{
    "require": {
        "dcarbone/json-to-go": "@stable"
    }
}
```

## Why Do This in PHP?

Because it fits better into my personal workflow.

Also because why not.

## Basic Usage

Once included in your project, the easiest way to use it is probably using the static initializers:

```php
$jsonToGO = \DCarbone\JSONToGO::parse('RootTypeName', $myjson);
```

This will return to you an instance of a concrete implementation of
[AbstractType](./src/JSONToGO/Types/AbstractType.php) with your input parsed.  If there was an issue during parsing,
an exception will be thrown.

For a complete list of possible types, see [here](./src/JSONToGO/Types)

This class implements `__toString()`, and the return value is the parsed GO object.

## General Rules

- If a property name in an object is entirely comprised of numbers, it will be prefixed with `Num`
- If a property name in an object begins with a non-alphanumeric value, it will be prefixed with `X`
- If a property name in an object has a numerical first character, that character will be converted to a string
  following the map seen [here](./src/JSONToGO/Configuration.php#L25). (e.g.: `80211X` becomes `Eight_0211X`)
  You may optionally define your own map when initializing the Configuration class. 
- If a type is not possible (is a NULL in the example json...), or if there is a value type conflict between keys
  within a map, the type will be defined as `interface{}`
- The names for things will always be Exported
- It is always a good idea to run the results through `go fmt`

## Custom Configuration

There are a few possible options when parsing JSON, definable in the [Configuration](./src/JSONToGO/Configuration.php)
class:

- `forceOmitEmpty` - Will always place the `json:,omitempty` markup at the end of struct properties
- `forceIntToFloat` - Will convert all `int` types to `float`.
- `useSimpleInt` - Will, if using ints, use the simple `int` type rather than attempting to determine `int32` vs `int64`
- `forceScalarToPointer` - Will turn all simple types (string, int, float, bool) into pointers
- `emptyStructToInterface` - Will convert an object without properties into an `interface{}`
- `breakOutInlineStructs` - Will create bespoke type definitions for nested objects
- `sanitizeInput` - Will override the values present in the example json (see [here](./src/JSONToGO.php#L121))
- `initialNumberMap`- Array to use for converting number characters to alpha characters at the beginning of struct
- `callbacks` - Instance of [Callbacks](./src/JSONToGO/Callbacks.php) or array of `['callback' => callable]` where
  `callback` == name of parameter on [Callbacks](./src/JSONToGO/Callbacks.php) and `callable` == any valid php callable
  that accepts the parameters present in the function named the same as the property.

### Map and RawMessage types

By default, the [MapType](src/JSONToGO/Types/MapType.php) and [RawMessageType](./src/JSONToGO/Types/RawMessageType.php)
are never used.  To take advantage of these types, you must implement your own [goType](./src/JSONToGO/Typer.php#L30)
that returns either `map` or `raw` respectively.

## Examples:

Taking this example JSON payload ([source](http://json.org/example.html)):

```json
{"web-app": {
  "servlet": [   
    {
      "servlet-name": "cofaxCDS",
      "servlet-class": "org.cofax.cds.CDSServlet",
      "init-param": {
        "configGlossary:installationAt": "Philadelphia, PA",
        "configGlossary:adminEmail": "ksm@pobox.com",
        "configGlossary:poweredBy": "Cofax",
        "configGlossary:poweredByIcon": "/images/cofax.gif",
        "configGlossary:staticPath": "/content/static",
        "templateProcessorClass": "org.cofax.WysiwygTemplate",
        "templateLoaderClass": "org.cofax.FilesTemplateLoader",
        "templatePath": "templates",
        "templateOverridePath": "",
        "defaultListTemplate": "listTemplate.htm",
        "defaultFileTemplate": "articleTemplate.htm",
        "useJSP": false,
        "jspListTemplate": "listTemplate.jsp",
        "jspFileTemplate": "articleTemplate.jsp",
        "cachePackageTagsTrack": 200,
        "cachePackageTagsStore": 200,
        "cachePackageTagsRefresh": 60,
        "cacheTemplatesTrack": 100,
        "cacheTemplatesStore": 50,
        "cacheTemplatesRefresh": 15,
        "cachePagesTrack": 200,
        "cachePagesStore": 100,
        "cachePagesRefresh": 10,
        "cachePagesDirtyRead": 10,
        "searchEngineListTemplate": "forSearchEnginesList.htm",
        "searchEngineFileTemplate": "forSearchEngines.htm",
        "searchEngineRobotsDb": "WEB-INF/robots.db",
        "useDataStore": true,
        "dataStoreClass": "org.cofax.SqlDataStore",
        "redirectionClass": "org.cofax.SqlRedirection",
        "dataStoreName": "cofax",
        "dataStoreDriver": "com.microsoft.jdbc.sqlserver.SQLServerDriver",
        "dataStoreUrl": "jdbc:microsoft:sqlserver://LOCALHOST:1433;DatabaseName=goon",
        "dataStoreUser": "sa",
        "dataStorePassword": "dataStoreTestQuery",
        "dataStoreTestQuery": "SET NOCOUNT ON;select test='test';",
        "dataStoreLogFile": "/usr/local/tomcat/logs/datastore.log",
        "dataStoreInitConns": 10,
        "dataStoreMaxConns": 100,
        "dataStoreConnUsageLimit": 100,
        "dataStoreLogLevel": "debug",
        "maxUrlLength": 500}},
    {
      "servlet-name": "cofaxEmail",
      "servlet-class": "org.cofax.cds.EmailServlet",
      "init-param": {
      "mailHost": "mail1",
      "mailHostOverride": "mail2"}},
    {
      "servlet-name": "cofaxAdmin",
      "servlet-class": "org.cofax.cds.AdminServlet"},
 
    {
      "servlet-name": "fileServlet",
      "servlet-class": "org.cofax.cds.FileServlet"},
    {
      "servlet-name": "cofaxTools",
      "servlet-class": "org.cofax.cms.CofaxToolsServlet",
      "init-param": {
        "templatePath": "toolstemplates/",
        "log": 1,
        "logLocation": "/usr/local/tomcat/logs/CofaxTools.log",
        "logMaxSize": "",
        "dataLog": 1,
        "dataLogLocation": "/usr/local/tomcat/logs/dataLog.log",
        "dataLogMaxSize": "",
        "removePageCache": "/content/admin/remove?cache=pages&id=",
        "removeTemplateCache": "/content/admin/remove?cache=templates&id=",
        "fileTransferFolder": "/usr/local/tomcat/webapps/content/fileTransferFolder",
        "lookInContext": 1,
        "adminGroupID": 4,
        "betaServer": true}}],
  "servlet-mapping": {
    "cofaxCDS": "/",
    "cofaxEmail": "/cofaxutil/aemail/*",
    "cofaxAdmin": "/admin/*",
    "fileServlet": "/static/*",
    "cofaxTools": "/tools/*"},
 
  "taglib": {
    "taglib-uri": "cofax.tld",
    "taglib-location": "/WEB-INF/tlds/cofax.tld"}}}
```

And executing the following:

```php
    $go = \DCarbone\JSONToGO::parse('RootTypeName', $json);
    
    file_put_contents(__DIR__.'/example.go', (string)$go);
```

Will result in (pre `go fmt`):

```go
type RootTypeName struct {
	WebApp struct {
			Servlet []struct {
					ServletName string `json:"servlet-name"`
					ServletClass string `json:"servlet-class"`
					InitParam struct {
							ConfigGlossaryInstallationAt string `json:"configGlossary:installationAt"`
							ConfigGlossaryAdminEmail string `json:"configGlossary:adminEmail"`
							ConfigGlossaryPoweredBy string `json:"configGlossary:poweredBy"`
							ConfigGlossaryPoweredByIcon string `json:"configGlossary:poweredByIcon"`
							ConfigGlossaryStaticPath string `json:"configGlossary:staticPath"`
							TemplateProcessorClass string `json:"templateProcessorClass"`
							TemplateLoaderClass string `json:"templateLoaderClass"`
							TemplatePath string `json:"templatePath"`
							TemplateOverridePath string `json:"templateOverridePath"`
							DefaultListTemplate string `json:"defaultListTemplate"`
							DefaultFileTemplate string `json:"defaultFileTemplate"`
							UseJSP bool `json:"useJSP"`
							JspListTemplate string `json:"jspListTemplate"`
							JspFileTemplate string `json:"jspFileTemplate"`
							CachePackageTagsTrack int `json:"cachePackageTagsTrack"`
							CachePackageTagsStore int `json:"cachePackageTagsStore"`
							CachePackageTagsRefresh int `json:"cachePackageTagsRefresh"`
							CacheTemplatesTrack int `json:"cacheTemplatesTrack"`
							CacheTemplatesStore int `json:"cacheTemplatesStore"`
							CacheTemplatesRefresh int `json:"cacheTemplatesRefresh"`
							CachePagesTrack int `json:"cachePagesTrack"`
							CachePagesStore int `json:"cachePagesStore"`
							CachePagesRefresh int `json:"cachePagesRefresh"`
							CachePagesDirtyRead int `json:"cachePagesDirtyRead"`
							SearchEngineListTemplate string `json:"searchEngineListTemplate"`
							SearchEngineFileTemplate string `json:"searchEngineFileTemplate"`
							SearchEngineRobotsDb string `json:"searchEngineRobotsDb"`
							UseDataStore bool `json:"useDataStore"`
							DataStoreClass string `json:"dataStoreClass"`
							RedirectionClass string `json:"redirectionClass"`
							DataStoreName string `json:"dataStoreName"`
							DataStoreDriver string `json:"dataStoreDriver"`
							DataStoreURL string `json:"dataStoreUrl"`
							DataStoreUser string `json:"dataStoreUser"`
							DataStorePassword string `json:"dataStorePassword"`
							DataStoreTestQuery string `json:"dataStoreTestQuery"`
							DataStoreLogFile string `json:"dataStoreLogFile"`
							DataStoreInitConns int `json:"dataStoreInitConns"`
							DataStoreMaxConns int `json:"dataStoreMaxConns"`
							DataStoreConnUsageLimit int `json:"dataStoreConnUsageLimit"`
							DataStoreLogLevel string `json:"dataStoreLogLevel"`
							MaxURLLength int `json:"maxUrlLength"`
						} `json:"init-param,omitempty"`
				} `json:"servlet"`
			ServletMapping struct {
					CofaxCDS string `json:"cofaxCDS"`
					CofaxEmail string `json:"cofaxEmail"`
					CofaxAdmin string `json:"cofaxAdmin"`
					FileServlet string `json:"fileServlet"`
					CofaxTools string `json:"cofaxTools"`
				} `json:"servlet-mapping"`
			Taglib struct {
					TaglibURI string `json:"taglib-uri"`
					TaglibLocation string `json:"taglib-location"`
				} `json:"taglib"`
		} `json:"web-app"`
}

```
