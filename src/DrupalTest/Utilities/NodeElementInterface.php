<?php

namespace AKlump\DrupalTest\Utilities;

interface NodeElementInterface {

  /**
   * Returns specified attribute value.
   *
   * @param string $name
   *
   * @return string|null
   */
  public function getAttribute($name);
  /**
   * Returns the value of the form field or option element.
   *
   * For checkbox fields, the value is a boolean indicating whether the checkbox is checked.
   * For radio buttons, the value is the value of the selected button in the radio group
   *      or null if no button is selected.
   * For single select boxes, the value is the value of the selected option.
   * For multiple select boxes, the value is an array of selected option values.
   * for file inputs, the return value is undefined given that browsers don't allow accessing
   *      the value of file inputs for security reasons. Some drivers may allow accessing the
   *      path of the file set in the field, but this is not required if it cannot be implemented.
   * For textarea elements and all textual fields, the value is the content of the field.
   * Form option elements, the value is the value of the option (the value attribute or the text
   *      content if the attribute is not set).
   *
   * Calling this method on other elements than form fields or option elements is not allowed.
   *
   * @return string|bool|array
   */
  public function getValue();

  /**
   * Checks whether an element has a named CSS class.
   *
   * @param string $className Name of the class
   *
   * @return bool
   */
  public function hasClass($className);

  /**
   * Checks whether element has attribute with specified name.
   *
   * @param string $name
   *
   * @return Boolean
   */
  public function hasAttribute($name);

  /**
   * Returns element text (inside tag).
   *
   * @return string
   */
  public function getText();

  /**
   * Returns element inner html.
   *
   * @return string
   */
  public function getHtml();
}
