<?php

namespace AKlump\DrupalTest\Drupal8;

/**
 * Trait EntityMockingTrait.
 *
 * Use this to make mocking entities easier.  When implementing you must:
 * - use EasyMockTrait
 * - add `'entity' => EntityInterface::class` to getSchema, mockObjectsMap.
 */
trait EntityMockingTrait {

  /**
   * Populates $this->entity with the arguments.
   *
   * @param string $entity_type_id
   *   The entity type id to mock.
   * @param string $bundle
   *   The entity bundle to mock.
   * @param null $id
   *   Optional id, defaults to 1 more than the last id.
   */
  protected function populateEntity($entity_type_id, $bundle, $id = NULL) {
    static $fallback = 1;
    if ($id) {
      $fallback = $id;
    }
    $this->entity->allows('getEntityTypeId')->andReturns($entity_type_id);
    $this->entity->allows('bundle')->andReturns($bundle);
    $this->entity->allows('id')->andReturns($fallback++);
  }

  /**
   * Prepare $this->entity to be able to return certain field values.
   *
   * @param string $field_name
   *   The entity field name.
   * @param array $values
   *   An array of values to be set on $field_name.
   * @param string $key
   *   The item array key to set $value(s) into.
   */
  protected function populateEntityFieldValues($field_name, array $values, $key = 'value') {
    $item_list = [];
    foreach ($values as $value) {
      $item = \Mockery::mock(TypedDataInterface::class);
      $item->allows('getValue')->andReturns([$key => $value]);
      $item_list[] = $item;
    }
    $this->entity->allows('get')->with($field_name)->andReturns($item_list);
    $this->entity->shouldReceive('hasField')
      ->with($field_name)
      ->andReturn(TRUE);
  }

}
