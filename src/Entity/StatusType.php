<?php

namespace Drupal\statusmessage\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBundleBase;
use Drupal\statusmessage\StatusTypeInterface;

/**
 * Defines the Status type entity.
 *
 * @ConfigEntityType(
 *   id = "status_type",
 *   label = @Translation("Status type"),
 *   handlers = {
 *     "list_builder" = "Drupal\statusmessage\StatusTypeListBuilder",
 *     "form" = {
 *       "add" = "Drupal\statusmessage\Form\StatusTypeForm",
 *       "edit" = "Drupal\statusmessage\Form\StatusTypeForm",
 *       "delete" = "Drupal\statusmessage\Form\StatusTypeDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\statusmessage\StatusTypeHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "status_type",
 *   admin_permission = "administer site configuration",
 *   bundle_of = "status",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/ls/status_type/{status_type}",
 *     "add-form" = "/ls/status_type/add",
 *     "edit-form" = "/ls/status_type/{status_type}/edit",
 *     "delete-form" = "/ls/status_type/{status_type}/delete",
 *     "collection" = "/ls/status_type"
 *   }
 * )
 */
class StatusType extends ConfigEntityBundleBase implements StatusTypeInterface {
  /**
   * The Status type ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Status type label.
   *
   * @var string
   */
  protected $label;


  /**
   * Boolean value to determine whether this type contains media
   *
   * @var
   */
  protected $media;


  /**
   * Mime type for media, if present
   *
   * @var
   */
  protected $mime;


  /**
   * @param $bool
   */

  public function setMedia($bool) {
    $this->set('media', $bool);
  }


  /**
   * @return mixed|null
   */

  public function getMedia() {
    return $this->get('media');
  }


  /**
   * @param $mime
   */

  public function setMime($mime) {
    $this->set('mime', $mime);
  }


  /**
   * @return mixed|null
   */

  public function getMime() {
    return $this->get('mime');
  }
}
