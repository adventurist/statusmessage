<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class StatusTypeForm.
 *
 * @package Drupal\statusmessage\Form
 */
class StatusTypeForm extends EntityForm {
  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $status_type = $this->entity;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $status_type->label(),
      '#description' => $this->t("Label for the Status type."),
      '#required' => TRUE,
    );

    $form['id'] = array(
      '#type' => 'machine_name',
      '#default_value' => $status_type->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\statusmessage\Entity\StatusType::load',
      ),
      '#disabled' => !$status_type->isNew(),
    );

    /* You will need additional form elements for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $status_type = $this->entity;
    $status = $status_type->save();

    switch ($status) {
      case SAVED_NEW:
        drupal_set_message($this->t('Created the %label Status type.', [
          '%label' => $status_type->label(),
        ]));
        break;

      default:
        drupal_set_message($this->t('Saved the %label Status type.', [
          '%label' => $status_type->label(),
        ]));
    }
    $form_state->setRedirectUrl($status_type->urlInfo('collection'));
  }

}
