<?php


namespace AKlump\DrupalTest\Utilities;


/**
 * Trait CKEditorTrait helps to simplify tests against a CK Editor.
 *
 * @package AKlump\DrupalTest\Utilities
 */
trait CKEditorTrait {

  /**
   * Set the value of a CK Editor WYSIWYG input.
   *
   * @param string $css_selector
   *   The element selector. It must map to the element whose id is used to
   *   generate the CKEDITOR instance key.
   * @param string $value
   *   The value to set on the editor.
   *
   * @return \AKlump\DrupalTest\Utilities\CKEditorTrait
   *   Self for chaining.
   */
  public function setCkEditorValue(string $css_selector, $value): CKEditorTrait {
    $id = $this->el($css_selector)->getAttribute('id');
    $js = implode('', [
      "CKEDITOR.instances['",
      $id,
      "'].setData('",
      $value,
      "');",
    ]);
    $this
      ->getSession()
      ->executeScript(trim($js));

    return $this;
  }

}
