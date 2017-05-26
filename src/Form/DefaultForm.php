<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\statusmessage\StatusService;

/**
 * Class DefaultForm.
 *
 * @package Drupal\statusmessage\Form
 */
class DefaultForm extends FormBase {

  /**
   * Drupal\statusmessage\StatusService definition.
   *
   * @var \Drupal\statusmessage\StatusService
   */
  protected $statusService;
  public function __construct(
    StatusService $statusservice
  ) {
    $this->statusService = $statusservice;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('statusservice')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'status_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['message'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Message'),
      '#description' => $this->t('Status Message'),
    ];
    $form['media'] = [
      '#type' => 'radio',
      '#title' => $this->t('Media'),
      '#description' => $this->t('Media'),
    ];
    $form['post'] = [
      '#type' => 'submit',
      '#title' => $this->t('Post'),
      '#description' => $this->t('Post the message'),
    ];

    $form['submit'] = [
        '#type' => 'submit',
        '#value' => $this->t('Submit'),
    ];

    return $form;
  }

  /**
    * {@inheritdoc}
    */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Display result.
    foreach ($form_state->getValues() as $key => $value) {
        drupal_set_message($key . ': ' . $value);
    }

  }

}
