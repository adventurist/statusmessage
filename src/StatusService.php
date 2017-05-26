<?php

namespace Drupal\statusmessage;

use Drupal\Core\Database\Driver\mysql\Connection;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Database\Database;
use Drupal\flag\FlagService;

/**
 * Class StatusService.
 *
 * @package Drupal\statusmessage
 */
class StatusService {


  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var Drupal\flag\FlagService
   */
  protected $flag;
  /**
   * Constructor.
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query, FlagService $flag) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
    $this->flag = $flag;
  }


  public function getMimeTypes() {
    return ['image/jpeg', 'image/png', 'application/octet-stream', 'video/mp4', 'text/plain', 'application/pdf', 'image/gif'];
  }

  public function getStatuses() {
    return $this->entityQuery->get('status')->execute();
  }

  public function load($id) {
    return $this->entityTypeManager->getStorage('status')->load($id);
  }

  public function loadAll() {
    return $this->entityTypeManager->getStorage('status')->loadMultiple($this->getStatuses());
  }

}
