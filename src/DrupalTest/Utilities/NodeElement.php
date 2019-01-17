<?php

namespace AKlump\DrupalTest\Utilities;

use DOMElement;

/**
 * This class normalizes NODE element to match Mink NodeElement.
 *
 * @link https://symfony.com/doc/3.4/components/dom_crawler.html
 */
class NodeElement extends DOMElement implements NodeElementInterface {


  /**
   * Create a new instance from a \DOMElement
   *
   * @param \DOMElement $source
   *
   * @return \AKlump\DrupalTest\Utilities\NodeElement
   */
  public static function import(\DOMElement $source) {
    $node = new NodeElement($source->nodeName, $source->nodeValue, $source->namespaceURI);
    foreach ($source->attributes as $attribute) {
      $node->setAttributeNode($attribute);
    }

    return $node;
  }


  /**
   * {@inheritdoc}
   */
  public function getValue() {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function hasClass($class) {
    $classes = explode(' ', $this->getAttribute('class'));

    return in_array($class, $classes);
  }

  /**
   * {@inheritdoc}
   */
  public function getText() {
    return $this->textContent;
  }

  /**
   * {@inheritdoc}
   */
  public function getHtml() {
    return $this->nodeValue;
  }

}
