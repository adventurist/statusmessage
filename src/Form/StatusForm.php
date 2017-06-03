<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\statusmessage\Entity\Status;
use Drupal\statusmessage\StatusService;
use Drupal\statusmessage\StatusTypeService;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Form controller for Status edit forms.
 *
 * @ingroup statusmessage
 */
class StatusForm extends FormBase {

  protected $statusTypeService;

  protected $statusService;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('status_type_service'),
      $container->get('statusservice'));
  }

  /**
   * StatusForm constructor.
   * @param StatusTypeService $status_type_service
   * @param StatusService $status_service
   */
  public function __construct(StatusTypeService $status_type_service, StatusService $status_service) {
    $this->statusTypeService = $status_type_service;
    $this->statusService = $status_service;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\statusmessage\Entity\Status */

    $form['message'] = array(
      '#type' => 'textarea',
      '#description' => 'Status Message',
      '#attributes' => array(
        'placeholder' => t('Post a status update'),
      ),

    );

    $form['post'] = array(
      '#type' => 'submit',
      '#description' => 'Post',
      '#value' => t('Post')

    );

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
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if (!empty($this->statusTypeService)) {
      foreach ($this->statusTypeService->loadAll() as $type) {
        if (!$type->getMedia()) {

          $userViewed = \Drupal::routeMatch()->getParameters()->get('user');

          if ($userViewed !== null) {

            $recipientUid = \Drupal::routeMatch()->getParameters()->get('user')->id();

            $statusEntity = Status::create([
              'type' => $type->id(),
              'uid' => \Drupal::currentUser()->id(),
              'recipient' => $recipientUid ? $recipientUid : \Drupal::currentUser()->id()
            ]);

            $statusEntity->setMessage($form_state->getValue('message'));
            $statusEntity->save();

            break;
          }
        }
      }
    }
  }
}

