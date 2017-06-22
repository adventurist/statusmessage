<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/21/17
 * Time: 7:41 PM
 */

namespace Drupal\statusmessage;


use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\heartbeat\Entity\Heartbeat;


/**
 * @property \Drupal\statusmessage\MarkupGenerator generator
 * @property  message
 */
class StatusHeartPost implements SharedContentInterface {

  protected $url;

  protected $message;

  protected $generator;

  protected $tags;

  /**
   * StatusHeartPost constructor.
   * @param $url
   */
  public function __construct($url, $message = NULL) {
    $this->url = $url;
    $this->message = $message;
    $this->generator = new MarkupGenerator();
  }

  public function sendRequest() {

    if ($this->generateRequest()) {

      $node = $this->setNodeData();

      $tags = $this->processTerms();

      if ($fid = $this->getMedia()) {
        $node->set('field_image', $fid);
      }

      if (!empty($this->tags)) {
        $node->set('field_tags', $this->tags);
      }

      if ($node->save()) {
        return $node->id();
      }
    }

  }

  private function generateRequest() {

    return $this->generator->parseMarkup($this->url);
  }


  private function processTerms() {

    foreach ($this->generator->getTags() as $tag) {
      $newTag = $tag;
    }

    if ($this->message) {
      $this->tags = self::parseHashtags($this->message);
    }

    return $this->generator->getTags();

  }


  private function setNodeData() {

    $append = FALSE;

    $node = Node::create([
      'type' => 'heartpost',
      'title' => $this->generator->getTitle(),
      'status' => 1,
    ]);


    if (strlen($this->generator->getDescription()) <= 255) {
      $node->set('field_description', [
        'value' => '<div class="status-heartpost-description"> ' . $this->generator->getDescription() . '</div>',
        'format' => 'full_html'
      ]);
    }
    else {
      $append = TRUE;
    }

    if ($this->message) {
      $this->message = $append ? $this->message . ' ' . PHP_EOL . $this->generator->getDescription() : $this->message;

      $node->set('body', [
        'value' => '<div class="status-heartpost"> ' . $this->message . ' </div>',
        'format' => 'full_html'
      ]);
    }


    return $node;

  }


  private function getMedia() {

    if ($this->generator->getImage()) {
      $mainImage = file_get_contents($this->generator->getImage());
      $file = file_save_data($mainImage, 'public://' . substr($this->generator->getImage(), strrpos($this->generator->getImage(), '/') + 1), FILE_EXISTS_REPLACE);

      return $file->id();
    }
//    return $this->generator->getImages();

  }


  public static function parseHashtags($message) {

    $tids = array();
    $i = 0;
    $tagsArray = explode('#', $message);
    $num = count($tagsArray);

    if ($num > 1) {
      unset($tagsArray[0]);
    }

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
