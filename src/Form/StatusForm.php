<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\statusmessage\Entity\Status;
use Drupal\statusmessage\ClientGeneratorService;
use Drupal\statusmessage\StatusService;
use Drupal\statusmessage\StatusTypeService;
use Drupal\statusmessage\Ajax\ClientCommand;
use Drupal\heartbeat\HeartbeatStreamServices;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\heartbeat\Ajax\SelectFeedCommand;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form controller for Status edit forms.
 *
 * @ingroup statusmessage
 */
class StatusForm extends FormBase {

  protected $statusTypeService;

  protected $statusService;

  protected $previewGenerator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('status_type_service'),
      $container->get('statusservice'),
      $container->get('preview_generator'));
  }

  /**
   * StatusForm constructor.
   * @param StatusTypeService $status_type_service
   * @param StatusService $status_service
   */
  public function __construct(StatusTypeService $status_type_service, StatusService $status_service, ClientGeneratorService $preview_generator) {
    $this->statusTypeService = $status_type_service;
    $this->statusService = $status_service;
    $this->previewGenerator = $preview_generator;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\statusmessage\Entity\Status */

    $form['#attached']['library'][] = 'statusmessage/status';

    if (\Drupal::moduleHandler()->moduleExists('heartbeat')) {
      $friendData = \Drupal::config('heartbeat_friendship.settings')->get('data');

      $form['#attached']['library'][] = 'heartbeat/heartbeat';
      $form['#attached']['drupalSettings']['friendData'] = $friendData;
    }

    $form['message'] = array(
      '#type' => 'textarea',
      '#description' => 'Status Message',
      '#attributes' => array(
        'placeholder' => t('Post a status update'),
      ),
      '#ajax' => [
        'event' => 'change, paste, keyup',
        'callback' => '::generatePreview',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Generating preview'),
        ),
      ],
    );


    $form['post'] = array(
      '#type' => 'submit',
      '#description' => 'Post',
      '#value' => t('Post'),
      '#ajax' => [
        'callback' => '::statusAjaxSubmit',
        'progress' => array(
          'type' => 'throbber',
          'message' => t('Posting Message'),
          ),
      ]

    );
$stophere = null;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = parent::save($form, $form_state);


    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Status.', [
          '%label' => $entity->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Status.', [
          '%label' => $entity->label(),
        ]));
    }
    $form_state->setRedirect('entity.status.canonical', ['status' => $entity->id()]);
  }

  /**
   * Returns a unique string identifying the form.
   *
   * @return string
   *   The unique string identifying the form.
   */
  public function getFormId() {
    return 'status_form';
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @throws \Drupal\Core\Entity\EntityStorageException
   * @throws \InvalidArgumentException
   */

  public function generatePreview(array &$form, FormStateInterface $form_state) {

    $message = $form_state->getValue('message');

    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message, $match);


    if ($this->previewGenerator !== null && !empty($match) && array_values($match)[0] !== null) {

      $url = array_values($match)[0];

//      $this->previewGenerator->generatePreview($url);

      $response = new AjaxResponse();
      $response->addCommand(new ClientCommand($url[0]));

      return $response;


    }

//    if (!empty($this->statusTypeService)) {
//      foreach ($this->statusTypeService->loadAll() as $type) {
//        if (!$type->getMedia()) {
//
//          $userViewed = \Drupal::routeMatch()->getParameters()->get('user') === null ? \Drupal::currentUser()->id() : \Drupal::routeMatch()->getParameters()->get('user')->id();
//
//          if ($userViewed !== null) {
//
//            $statusEntity = Status::create([
//              'type' => $type->id(),
//              'uid' => \Drupal::currentUser()->id(),
//              'recipient' => $userViewed
//            ]);
//
//            $statusEntity->setMessage($form_state->getValue('message'));
//            $statusEntity->save();
//
//            if (\Drupal::service('module_handler')->moduleExists('heartbeat')) {
//
////              $configManager = \Drupal::service('config.manager');
//              $feedConfig = \Drupal::config('heartbeat_feed.settings');
////              $feedConfig = $feedConfig = $configManager->get('heartbeat_feed.settings');
//              $response = new AjaxResponse();
//              $response->addCommand(new SelectFeedCommand($feedConfig->get('message')));
//
//              return $response;
//            }
//            break;
//          }
//        }
//      }
//    }
  }
  public function statusAjaxSubmit(array &$form, FormStateInterface $form_state) {




    if (!empty($this->statusTypeService)) {
      foreach ($this->statusTypeService->loadAll() as $type) {
        if (!$type->getMedia()) {

          $userViewed = \Drupal::routeMatch()->getParameters()->get('user') === null ? \Drupal::currentUser()->id() : \Drupal::routeMatch()->getParameters()->get('user')->id();

          if ($userViewed !== null) {

            $statusEntity = Status::create([
              'type' => $type->id(),
              'uid' => \Drupal::currentUser()->id(),
              'recipient' => $userViewed
            ]);

            $statusEntity->setMessage($form_state->getValue('message'));
            $statusEntity->save();

            if (\Drupal::service('module_handler')->moduleExists('heartbeat')) {

//              $configManager = \Drupal::service('config.manager');
              $feedConfig = \Drupal::config('heartbeat_feed.settings');
//              $feedConfig = $feedConfig = $configManager->get('heartbeat_feed.settings');
              $response = new AjaxResponse();
              $response->addCommand(new SelectFeedCommand($feedConfig->get('message')));

              return $response;
            }
            break;
          }
        }
      }
    }
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

  }
}

