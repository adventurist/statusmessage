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
   * Drupal\Core\Database\Driver\mysql\Connection definition.
   *
   * @var Drupal\Core\Database\Driver\mysql\Connection
   */
  protected $database;

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var Drupal\Core\Entity\EntityTypeManager
   */
  protected $entity_type_manager;

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entity_query;

  /**
   * Drupal\flag\FlagService definition.
   *
   * @var Drupal\flag\FlagService
   */
  protected $flag;
  /**
   * Constructor.
   */
  public function __construct(Connection $database, EntityTypeManager $entity_type_manager, QueryFactory $entity_query, FlagService $flag) {
    $this->database = $database;
    $this->entity_type_manager = $entity_type_manager;
    $this->entity_query = $entity_query;
    $this->flag = $flag;
  }


  public function getMimeTypes() {
    return ['image/jpeg', 'image/png', 'application/octet-stream', 'video/mp4', 'text/plain', 'application/pdf', 'image/gif'];
  }

}
