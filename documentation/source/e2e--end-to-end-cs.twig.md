# Cheatsheet for End to End Tests

## Generating Data

When filling out forms you probably want to use the generators.

     $el['.t-field_first_name']->setValue($this->generate('name:first'));

{% include('_Generators.md') %}

##  Traits

{% include('_CKEditorTrait.md') %}
{% include('_InteractiveTrait.md') %}
{% include('_TimeTrait.md') %}

## EndToEndTestCase

{% include('_EndToEndTestCase.md') %}

{% include('_BrowserTestCase.md') %}

## Helpers

{% include('_Balloon.md') %}
{% include('_Url.md') %}

