# Laravel JSONx

Add XML support to your JSON API just by adding this one middleware. All incoming XML requests are converted to JSON. All outgoing JSON responses are converted to XML.

Requests just need to use the `Accept: application/xml` header to receive the response as XML. And if they're sending in XML, they just need to use the `Content-Type: application/xml` header too.

It does this using IBM's standard for representing JSON as XML: [JSONx](https://tools.ietf.org/html/draft-rsalz-jsonx-00).

## Installation

```
composer require danharper/laravel-jsonx
```

Register the middleware within `$routeMiddleware` in `app/Http/Kernel`:

```php
protected $routeMiddleware => [
  // ...
  'jsonx' => \danharper\LaravelJSONx\JSONxMiddleware::class,
];
```

And simply add the `jsonx` middleware to your API routes.

## Example

Once the middleware's registered, use it like so:

```php
Route::get('foo', ['middleware' => ['jsonx'], 'uses' => function() {
  return [
    'fruits' => ['apple', 'banana', 'pear'],
    'something' => true,
  ];
});
```

Make a JSON request, e.g. using the `Accept: application/json` header and in response you'll get (as Laravel provides by default):

```json
{
  "fruits": ["apple", "banana", "pear"],
  "something": true
}
```

But make an XML request using the `Accept: application/xml` header and you'll get back JSONx:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<json:object xmlns:json="http://www.ibm.com/xmlns/prod/2009/jsonx" xsi:schemaLocation="http://www.datapower.com/schemas/json jsonx.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <json:array name="fruits">
    <json:string>apple</json:string>
    <json:string>banana</json:string>
    <json:string>pear</json:string>
  </json:array>
  <json:boolean name="something">true</json:boolean>
</json:object>
```

Additionally, incoming XML data (formatted as JSONx) will be seamlessly converted to JSON.

So for example, these two are equivalent (assuming they're sent with `Content-Type: application/json` and `Content-Type: application/xml` headers respectively):

```json
{
  "address": {
    "line1": "9 Long Street",
    "postcode": "Portsmouth"
  }
}
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<json:object xmlns:json="http://www.ibm.com/xmlns/prod/2009/jsonx" xsi:schemaLocation="http://www.datapower.com/schemas/json jsonx.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <json:object name="address">
    <json:string name="line1">9 Long Street</json:string>
    <json:string name="city">Portsmouth</json:string>
  </json:object>
</json:object>
```

And with a handler:

```php
Route::post('/', ['middleware' => ['jsonx'], 'uses' => function() {
  return [
    'hello' => request('address.city')
  ];
});
```

When the response is asked for as JSON (default) as `Accept: application/json` or XML as `Accept: application/xml`, the response will be:

```json
{
  "hello": "Portsmouth"
}
```

```xml
<?xml version="1.0" encoding="UTF-8"?>
<json:object xmlns:json="http://www.ibm.com/xmlns/prod/2009/jsonx" xsi:schemaLocation="http://www.datapower.com/schemas/json jsonx.xsd" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
	<json:string name="hello">Portsmouth</json:string>
</json:object>
```
