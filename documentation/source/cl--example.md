# Client Test Examples

## Assert Page Content

Using a string search:

    $this->loadPageByUrl('/collections')
      ->assert()->responseContains('logo.jpg')

    $this->loadPageByUrl('/collections')
      ->assert()->pageTextContains('Welcome home!')

Using CSS selectors:
      
    $this->loadPageByUrl('/search')
      ->assertElementExists('.views-widget-filter-id')

## Assert HTTP Status

    public function testBlogPage() {
      $this->loadPageByUrl('/blog')->assert()->statusCodeEquals(200);
    }
    
## Response validation with JSON Schema

<https://json-schema.org/latest/json-schema-validation.html#rfc.section.6.3.3>

The client tests provide the means of validation using JSON Schema.  You indicate where your schema files are located in _phpunit.xml_ using the custom key `jsonschema`.  Child nodes of `directory` are relative to _phpunit.xml_; you may use globs; you may have more than one `directory` node. 

    <phpunit>
        <jsonschema>
            <directory>../web/sites/all/modules/custom/*/tests/schema</directory>
        </jsonschema>
    </phpunit>

Then to validate an URL do something like the following:

## Validating XML Responses

This example shows how load an endpoint that returns XML and validate that using a JSON Schema; then it checks for specific values in the XML.

    public function testXMLEndpoint() {
      $this->loadXmlByUrl('api/1/some/endpoint')
        ->assertResponseMatchesSchema('resource.json');
  
      $this->assertSame(123, (int) $this->xml->id);
      $this->assertSame('name', (string) $this->xml->name);
    }  

