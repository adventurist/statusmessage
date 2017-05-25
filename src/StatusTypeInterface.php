<?php

namespace Drupal\statusmessage;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
 * Provides an interface for defining Status type entities.
 */
interface StatusTypeInterface extends ConfigEntityInterface {
  // Add get/set methods for your configuration properties here.

  public function setMedia($bool);

  public function getMedia();

  public function setMime($mime);

  public function getMime();

}
