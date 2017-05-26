<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Form controller for Status edit forms.
 *
 * @ingroup statusmessage
 */
class StatusForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    /* @var $entity \Drupal\statusmessage\Entity\Status */

    $form['message'] = array(
      '#type' => 'textarea',
      '#description' => 'Status Message',

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
//    $status = parent::save($form, $form_state);
    
    
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
  public function getFormId()
  {
    // TODO: Implement getFormId() method.
  }

  /**
   * Form submission handler.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function submitForm(array &$form, FormStateInterface $form_state)
  {
    // TODO: Implement submitForm() method.
  }
}

