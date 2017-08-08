<?php

namespace Drupal\statusmessage\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\statusmessage\Entity\Status;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\statusmessage\StatusService;
use Drupal\statusmessage\StatusTypeService;

/**
 * Class ContentController.
 */
class ContentController extends ControllerBase {

  /**
   * Drupal\Core\Entity\EntityTypeManager definition.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;
  /**
   * Drupal\Core\Entity\Query\QueryFactory definition.
   *
   * @var \Drupal\Core\Entity\Query\QueryFactory
   */
  protected $entityQuery;
  /**
   * Drupal\statusmessage\StatusService definition.
   *
   * @var \Drupal\statusmessage\StatusService
   */
  protected $statusService;
  /**
   * Drupal\statusmessage\StatusTypeService definition.
   *
   * @var \Drupal\statusmessage\StatusTypeService
   */
  protected $statusTypeService;

  /**
   * Constructs a new ContentController object.
   */
  public function __construct(EntityTypeManager $entity_type_manager, QueryFactory $entity_query, StatusService $statusservice, StatusTypeService $status_type_service) {
    $this->entityTypeManager = $entity_type_manager;
    $this->entityQuery = $entity_query;
    $this->statusService = $statusservice;
    $this->statusTypeService = $status_type_service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('entity.query'),
      $container->get('statusservice'),
      $container->get('status_type_service')
    );
  }

  /**
   * Info.
   *
   * @return string
   *   Return Hello string.
   */
  public function info() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: info')
    ];
  }

  public function getStatusMessages() {

    $data = file_get_contents("public://statusmessages.dat");
    $statusmessages = unserialize($data);
    $errors = false;
    if (is_array($statusmessages)) {
      $statusmessages = array_reverse($statusmessages);
      foreach ($statusmessages as $statusMessage) {

        if ($statusMessage instanceof \Drupal\statusmessage\Entity\Status) {
//          try {
//            $heartbeat->save();
//          } catch (\Exception $e) {
//            $message = $e->getMessage();
//          }
//        }


          $status = Status::create([
//            'uid' => $heartbeat->getOwnerId(),
//            'nid' => $heartbeat->getNid()->getValue()[0]['target_id'],
//            'name' => $title,
//            'type' => $heartbeat->getType(),
//            'message' => $heartbeat->getMessage()->getValue()[0]['value']
          ]);

          if (!$status->save()) {
            $errors = true;
          }
        }
      }
    }
    $result = $errors ? 'Error restoring statusmessages' : 'statusmessages restored';

    return [
      '#type' => 'markup',
      '#markup' => $this->t($result),
    ];

  }
//
  public function deleteStatusMessages() {
    $entities = \Drupal::service("entity.query")->get("status")->execute();
    foreach($entities as $entity) {
      $status = $this->entityTypeManager()->getStorage("status")->load($entity);
      $status->delete();
    }

    return [
      '#type' => 'markup',
      '#markup' => $this->t('Deleting them statusmessages')
    ];
  }

}
