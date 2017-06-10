<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\statusmessage\StatusTwitter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactory;

/**
 * Class TwitterApiForm.
 *
 * @package Drupal\statusmessage\Form
 */
class TwitterApiForm extends FormBase {

  /**
   * The configuration factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  private $statusTwitter;
  /**
   * Constructs a new TwitterApiForm object.
   */
  public function __construct(ConfigFactory $config_factory) {
    $this->configFactory = $config_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory')
    );
  }


  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'twitter_api_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    $form['oauth_access_token'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oauth Access Token'),
//      '#description' => $this->t('Oauth Access Token'),
      '#maxlength' => 64,
      '#size' => 64,
    ];
    $form['oauth_access_token_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Oauth Access Token Secret'),
//      '#description' => $this->t('Oauth Access Token Secret'),
      '#maxlength' => 64,
      '#size' => 64,
    ];
    $form['consumer_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Key'),
//      '#description' => $this->t('Consumer Key'),
      '#maxlength' => 64,
      '#size' => 64,
    ];
    $form['consumer_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Consumer Secret'),
//      '#description' => $this->t('Consumer Secret'),
      '#maxlength' => 64,
      '#size' => 64,
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

    if ($this->statusTwitter === null) {
      $this->statusTwitter = new StatusTwitter();
    }

    if ($form_state->getValue('oauth_access_token')) {

      $twitterConfig = $this->configFactory->getEditable('twitter_api.settings');

      $twitterConfig->set('consumer_key', $form_state->getValue('consumer_key'))->save();
      $twitterConfig->set('consumer_secret', $form_state->getValue('consumer_secret'))->save();
      $twitterConfig->set('oauth_access_token', $form_state->getValue('oauth_access_token'))->save();
      $twitterConfig->set('oauth_access_token_secret', $form_state->getValue('oauth_access_token_secret'))->save();

    }
  }
}
