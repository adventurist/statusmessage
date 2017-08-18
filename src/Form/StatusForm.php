<?php

namespace Drupal\statusmessage\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\heartbeat\Ajax\ClearPreviewCommand;
use Drupal\statusmessage\Entity\Status;
use Drupal\statusmessage\MarkupGenerator;
use Drupal\statusmessage\StatusService;
use Drupal\statusmessage\StatusTypeService;
use Drupal\statusmessage\Ajax\ClientCommand;
use Drupal\statusmessage\StatusHeartPost;
use Drupal\statusmessage\StatusTwitter;
use Drupal\statusmessage\StatusYoutube;
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

  protected $markupgenerator;

  private $mediaTabs;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('status_type_service'),
      $container->get('statusservice'),
      $container->get('markupgenerator'));
  }

  //TODO remove markup generator from this class

  /**
   * StatusForm constructor.
   * @param StatusTypeService $status_type_service
   * @param StatusService $status_service
   */
  public function __construct(StatusTypeService $status_type_service, StatusService $status_service, MarkupGenerator $markupgenerator) {
    $this->statusTypeService = $status_type_service;
    $this->statusService = $status_service;
    $this->markupgenerator = $markupgenerator;
    $this->mediaTabs = ['Photo', 'Video'];
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
          'type' => 'none',
          'message' => t('Generating preview'),
        ),
      ],
    );

    $form['mediatabs'] = [
      '#type' => 'radios',
//      '#description' => $this->t('User selectable feeds'),
      '#prefix' => '<div class="status-media-upload"></div>',

      '#options' => $this->mediaTabs,
      '#theme' => 'status-form-element',
//      '#ajax' => [
//        'callback' => '::updateFeed',
////        'event' => 'onclick',
//        'progress' => array(
//          'type' => 'none',
////        'message' => t('Fetching feed'),
//        ),
      ];

    $form['media'] = [
      '#type' => 'managed_file',
      '#upload_location' => 'public://statusmessage/',
      '#states' => array(
        'visible' => array(
          ':input[name="File_type"]' => array('value' => t('Upload Your File')),
        ),
      ),
    ];


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


    if ($this->markupgenerator !== null && !empty($match) && array_values($match)[0] !== null) {

      $url = array_values($match)[0];

//      $this->previewGenerator->generatePreview($url);

      $response = new AjaxResponse();
      $response->addCommand(new ClientCommand($url[0]));

      return $response;
    }
    return null;
  }

  public function statusAjaxSubmit(array &$form, FormStateInterface $form_state) {
    $message = $form_state->getValue('message');
    $file = $form_state->getValue('media');
    if (strlen(trim($message)) > 1) {
      preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $message, $match);

      if ($this->markupgenerator !== NULL && !empty($match) && array_values($match)[0] !== NULL) {
        $url = is_array(array_values($match)[0]) ? array_values(array_values($match)[0])[0] : array_values($match)[0];

        if (strpos($message, 'twitter')) {
          $statusTwitter = new StatusTwitter($url);
          $nid = $statusTwitter->sendRequest();

        } else if (strpos($message, 'youtube') || strpos($message, 'youtu.be')) {
          $statusYoutube = new StatusYoutube($url, $message);
          $nid = $statusYoutube->generateNode();

        } else if ($url !== null) {
          $statusHeartPost = new StatusHeartPost($url, $message);
          $nid = $statusHeartPost->sendRequest();

        }
      }

      if ($nid === NULL && !empty($this->statusTypeService)) {
        $sTypes = $this->statusTypeService->loadAll();
        foreach ($this->statusTypeService->loadAll() as $type) {
          $userViewed = \Drupal::routeMatch()
            ->getParameters()
            ->get('user') === NULL ? \Drupal::currentUser()
            ->id() : \Drupal::routeMatch()
            ->getParameters()
            ->get('user')
            ->id();

          if ($userViewed !== NULL) {
            $statusEntity = Status::create([
              'type' => $type->id(),
              'uid' => \Drupal::currentUser()->id(),
              'recipient' => $userViewed
            ]);

            StatusHeartPost::parseHashtags($message);

            if ($type->getMedia() && $file !== null) {
              $statusEntity->set('field_image', array_values($file)[0]);
            }
            $statusEntity->setMessage($message);
          }

          if (!empty($statusEntity) && $statusEntity->save()) {
            //TODO Log or error report
            $statusCreated = TRUE;
          }
        }
      }

      if (\Drupal::service('module_handler')
          ->moduleExists('heartbeat') && ($nid !== NULL || $statusEntity !== NULL)) {
//              $configManager = \Drupal::service('config.manager');
        $feedConfig = \Drupal::config('heartbeat_feed.settings');
//              $feedConfig = $feedConfig = $configManager->get('heartbeat_feed.settings');
        $response = new AjaxResponse();
        $response->addCommand(new SelectFeedCommand($feedConfig->get('message')));
        $response->addCommand(new ClearPreviewCommand(true));

        $this->clearFormInput($form_state);
        $form['message']['#default'] = '';
        $form['message']['#value'] = '';

        return $response;
      }
    }
    return null;
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

  /**
   * Clears form input.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function clearFormInput(FormStateInterface $form_state) {
    // Replace the form entity with an empty instance.
    // Clear user input.
    $input = $form_state->getUserInput();
    // We should not clear the system items from the user input.
    $clean_keys = $form_state->getCleanValueKeys();
    $clean_keys[] = 'ajax_page_state';
    foreach ($input as $key => $item) {
      if (!in_array($key, $clean_keys) && substr($key, 0, 1) !== '_') {
        unset($input[$key]);
      }
    }
    $form_state->setUserInput($input);
    // Rebuild the form state values.
    $form_state->setRebuild();
    $form_state->setStorage([]);
  }



  public static function parseHashtags($message) {

    $tids = array();
    $i = 0;
    $tagsArray = explode('#', $message);

    unset($tagsArray[0]);

    $num = count($tagsArray);


    foreach ($tagsArray as $hashtag) {
      if ($i === $num - 1) {
        $lastTagArray = explode(' ', $hashtag);
        if (strlen($lastTagArray[1]) > 1) {
          $hashtag = trim($lastTagArray[0]);
        }
      }
      $tid = \Drupal::entityQuery("taxonomy_term")
        ->condition("name", trim($hashtag))
        ->condition('vid', [
          'twitter',
          'tags',
          'kekistan'
        ], 'IN')
        ->execute();

      if (count($tid) > 0) {
        if (\Drupal::moduleHandler()->moduleExists('heartbeat')) {
          \Drupal\heartbeat\Entity\Heartbeat::updateTermUsage(array_values($tid)[0], 'tags');
        }
        $tids[] = array_values($tid)[0];
      } else {
        $term = Term::create([
          'name' => trim($hashtag),
          'vid' => 'tags',
          'field_count' => 1
        ]);
        if ($term->save()) {
          $tids[] = $term->id();
          if (\Drupal::moduleHandler()->moduleExists('heartbeat')) {
            \Drupal\heartbeat\Entity\Heartbeat::newTermUsage($term->id());
          }
        }
      }
      $i++;
    }
    return $tids;
  }

}

