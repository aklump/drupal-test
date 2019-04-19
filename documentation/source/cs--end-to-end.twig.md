# Cheatsheet for End to End Tests

## Generating Data

When filling out forms you probably want to use the generators.

     $el['.t-field_first_name']->setValue($this->generate('name:first'));

{% include('_Generators.md') %}

{% include('_EndToEndTestCase.md') %}
{% include('_BrowserTestCase.md') %}
