# json-to-go
PHP Implementation of mholt/json-to-go

## Composer

```json
{
    "require": {
        "dcarbone/json-to-go": "0.3.*"
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

- If a key in a map is entirely comprised of numbers, it will be prefixed with `Num`
- If a key in a map beings with a non-alphanumeric value, it will be prefixed with `X`
- If a key in a map has a numerical first character, that character will be converted to a string following the map
seen [here](./src/JSONToGO/Configuration.php#L23).  You may optionally define your own map when initializing the
Configuration class.
- If a type is not possible (is a NULL in the example json...), or if there is a value type conflict between keys
within a map, the type will be defined as `interface{}`
- The names for things will always be Exported

## Custom Configuration

There are a few possible options when parsing JSON, definable in the [Configuration](./src/JSONToGO/Configuration.php)
class:

- `forceOmitEmpty` - Will place the `json:,omitempty` markup at the end of struct properties
- `forceIntToFloat` - Will convert all `int` types to `float`.
- `forceScalarToPointer` - Will turn all simple types (string, int, float, bool) into pointers
- `breakOutInlineStructs` - Will create bespoke type definitions for nested objects
- `initialNumberMap`- Array to use for converting number characters to alpha characters at the beginning of struct
property names (e.g.: `80211X` becomes `Eight_0211X`)

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

Will result in:

```go
type WebappServletSlice []*WebappServlet

type ServletInitparam struct {
	ConfigGlossaryinstallationAt string `json:"configGlossary:installationAt"`
	ConfigGlossaryadminEmail     string `json:"configGlossary:adminEmail"`
	ConfigGlossarypoweredBy      string `json:"configGlossary:poweredBy"`
	ConfigGlossarypoweredByIcon  string `json:"configGlossary:poweredByIcon"`
	ConfigGlossarystaticPath     string `json:"configGlossary:staticPath"`
	TemplateProcessorClass       string `json:"templateProcessorClass"`
	TemplateLoaderClass          string `json:"templateLoaderClass"`
	TemplatePath                 string `json:"templatePath"`
	TemplateOverridePath         string `json:"templateOverridePath"`
	DefaultListTemplate          string `json:"defaultListTemplate"`
	DefaultFileTemplate          string `json:"defaultFileTemplate"`
	UseJSP                       bool   `json:"useJSP"`
	JspListTemplate              string `json:"jspListTemplate"`
	JspFileTemplate              string `json:"jspFileTemplate"`
	CachePackageTagsTrack        int    `json:"cachePackageTagsTrack"`
	CachePackageTagsStore        int    `json:"cachePackageTagsStore"`
	CachePackageTagsRefresh      int    `json:"cachePackageTagsRefresh"`
	CacheTemplatesTrack          int    `json:"cacheTemplatesTrack"`
	CacheTemplatesStore          int    `json:"cacheTemplatesStore"`
	CacheTemplatesRefresh        int    `json:"cacheTemplatesRefresh"`
	CachePagesTrack              int    `json:"cachePagesTrack"`
	CachePagesStore              int    `json:"cachePagesStore"`
	CachePagesRefresh            int    `json:"cachePagesRefresh"`
	CachePagesDirtyRead          int    `json:"cachePagesDirtyRead"`
	SearchEngineListTemplate     string `json:"searchEngineListTemplate"`
	SearchEngineFileTemplate     string `json:"searchEngineFileTemplate"`
	SearchEngineRobotsDb         string `json:"searchEngineRobotsDb"`
	UseDataStore                 bool   `json:"useDataStore"`
	DataStoreClass               string `json:"dataStoreClass"`
	RedirectionClass             string `json:"redirectionClass"`
	DataStoreName                string `json:"dataStoreName"`
	DataStoreDriver              string `json:"dataStoreDriver"`
	DataStoreURL                 string `json:"dataStoreUrl"`
	DataStoreUser                string `json:"dataStoreUser"`
	DataStorePassword            string `json:"dataStorePassword"`
	DataStoreTestQuery           string `json:"dataStoreTestQuery"`
	DataStoreLogFile             string `json:"dataStoreLogFile"`
	DataStoreInitConns           int    `json:"dataStoreInitConns"`
	DataStoreMaxConns            int    `json:"dataStoreMaxConns"`
	DataStoreConnUsageLimit      int    `json:"dataStoreConnUsageLimit"`
	DataStoreLogLevel            string `json:"dataStoreLogLevel"`
	MaxURLLength                 int    `json:"maxUrlLength"`
}

type WebappServlet struct {
	Servletname  string            `json:"servlet-name"`
	Servletclass string            `json:"servlet-class"`
	Initparam    *ServletInitparam `json:"init-param"`
}

type WebappServletmapping struct {
	CofaxCDS    string `json:"cofaxCDS"`
	CofaxEmail  string `json:"cofaxEmail"`
	CofaxAdmin  string `json:"cofaxAdmin"`
	FileServlet string `json:"fileServlet"`
	CofaxTools  string `json:"cofaxTools"`
}

type WebappTaglib struct {
	Tagliburi      string `json:"taglib-uri"`
	Tagliblocation string `json:"taglib-location"`
}

type RootTypeNameWebapp struct {
	Servlet        WebappServletSlice    `json:"servlet"`
	Servletmapping *WebappServletmapping `json:"servlet-mapping"`
	Taglib         *WebappTaglib         `json:"taglib"`
}

type RootTypeName struct {
	Webapp *RootTypeNameWebapp `json:"web-app"`
}
```
