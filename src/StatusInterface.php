<?php

namespace Drupal\statusmessage;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Status entities.
 *
 * @ingroup statusmessage
 */
interface StatusInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {
  // Add get/set methods for your configuration properties here.
  /**
   * Gets the Status type.
   *
   * @return string
   *   The Status type.
   */
  public function getType();

  /**
   * Gets the Status name.
   *
   * @return string
   *   Name of the Status.
   */
  public function getName();

  /**
   * Sets the Status name.
   *
   * @param string $name
   *   The Status name.
   *
   * @return \Drupal\statusmessage\StatusInterface
   *   The called Status entity.
   */
  public function setName($name);

  /**
   * Gets the Status creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Status.
   */
  public function getCreatedTime();

  /**
   * Sets the Status creation timestamp.
   *
   * @param int $timestamp
   *   The Status creation timestamp.
   *
   * @return \Drupal\statusmessage\StatusInterface
   *   The called Status entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Status published status indicator.
   *
   * Unpublished Status are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Status is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Status.
   *
   * @param bool $published
   *   TRUE to set this Status to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\statusmessage\StatusInterface
   *   The called Status entity.
   */
  public function setPublished($published);

  public function setMessage($message);

  public function getMessage();

//  public function setSender($sender);
//
//  public function getSender();

  public function setRecipient($recipient);

  public function getRecipient();

  public function setEntityTarget($entityTarget);

  public function getEntityTarget();

}
