<?php

namespace Drupal\statusmessage;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;

/**
 * Class StatusTypeService.
 *
 * @package Drupal\statusmessage
 */
class StatusTypeService {

  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Constructor.
   */
  public function __construct(QueryFactory $entity_query, EntityTypeManager $entity_type_manager) {
    $this->entityQuery = $entity_query;
    $this->entityTypeManager= $entity_type_manager;
  }


  public function getTypes() {
    return $this->entityQuery->get('status_type')->execute();
  }

  public function load($id) {
    return $this->entityTypeManager->getStorage('status_type')->load($id);
  }

  public function loadAll() {
    return $this->entityTypeManager->getStorage('status_type')->loadMultiple($this->getTypes());
  }
}
