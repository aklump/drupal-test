<?php

namespace AKlump\DrupalTest\Utilities;

use AKlump\DrupalTest\EndToEndTestCase;
use Drupal\Core\Entity\EntityInterface;

interface EntityBuilderInterface {

  /**
   * Use to indicate a field should be ignored when entering data.  Should be
   * returned by any fill__* method.
   *
   * @var bool
   */
  const SKIP_FORM_ENTRY = 1;
  const FORM_ENTRY_COMPLETE = 2;

  public function save(EndToEndTestCase $test_case): int;

  /**
   * Constructs a new entity object, without permanently saving it.
   *
   * @param array $values
   *   (optional) An array of values to set, keyed by property name. If the
   *   entity type has bundles, the bundle key has to be specified.
   *
   * @return static
   *   The entity object.
   */
  public static function create(array $values = []);

  /**
   * Gets the ID of the type of the entity.
   *
   * @return string
   *   The entity type ID.
   */
  public function getEntityTypeId();

  /**
   * Determines whether the entity is new.
   *
   * Usually an entity is new if no ID exists for it yet. However, entities may
   * be enforced to be new with existing IDs too.
   *
   * @return bool
   *   TRUE if the entity is new, or FALSE if the entity has already been saved.
   *
   * @see \Drupal\Core\Entity\EntityInterface::enforceIsNew()
   */
  public function isNew();

  /**
   * Gets the identifier.
   *
   * @return string|int|null
   *   The entity identifier, or NULL if the object does not yet have an
   *   identifier.
   */
  public function id();

  /**
   * Gets the bundle of the entity.
   *
   * @return string
   *   The bundle of the entity. Defaults to the entity type ID if the entity
   *   type does not make use of different bundles.
   */
  public function bundle();

  /**
   * Gets the URL object for the entity.
   *
   * The entity must have an id already. Content entities usually get their IDs
   * by saving them.
   *
   * URI templates might be set in the links array in an annotation, for
   * example:
   * @code
   * links = {
   *   "canonical" = "/node/{node}",
   *   "edit-form" = "/node/{node}/edit",
   *   "version-history" = "/node/{node}/revisions"
   * }
   * @endcode
   * or specified in a callback function set like:
   * @code
   * uri_callback = "comment_uri",
   * @endcode
   * If the path is not set in the links array, the uri_callback function is
   * used for setting the path. If this does not exist and the link relationship
   * type is canonical, the path is set using the default template:
   * entity/entityType/id.
   *
   * @param string $rel
   *   The link relationship type, for example: canonical or edit-form.
   * @param array $options
   *   See \Drupal\Core\Routing\UrlGeneratorInterface::generateFromRoute() for
   *   the available options.
   *
   * @return \Drupal\Core\Url
   *   The URL object.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   * @throws \Drupal\Core\Entity\Exception\UndefinedLinkTemplateException
   */
  public function toUrl($rel = 'canonical', array $options = []);

  /**
   * Gets an array of all property values.
   *
   * @return mixed[]
   *   An array of property values, keyed by property name.
   */
  public function toArray();

}
