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

      if ($fids = $this->getMedia()) {
        $node->set('field_image', $fids);
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
    $title = $this->generator->getTitle();
    $node = Node::create([
      'type' => 'heartpost',
      'title' => strlen($title) < 50 ? $title : substr($title, 0, 47) . '...',
      'status' => 1,
    ]);


    if (strlen($this->generator->getDescription()) <= 255) {
      $node->set('field_description', [
        'value' => '<div class="status-heartpost-description"> ' . $this->generator->getDescription() . '</div>',
        'format' => 'full_html'
      ]);
    } else {
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


  private function getMedia()
  {

    $fids = array();

    if ($images = $this->generator->getImages()) {
      for ($i = 0; $i < 10; $i++) {
        if (count($fids) < 6 && $images[$i]['width'] > 400) {
          $ext = strtolower(pathinfo($images[$i]['url'], PATHINFO_EXTENSION));
          if (!$this->verifyExtension($ext)) {
            $ext = explode($ext, $images[$i]['url']);
            $ext = count($ext) > 1 ? $ext[1] : $ext[0];
          }
          $ext = strpos($ext, '?') ? substr($ext, 0, strpos($ext, '?')) : $ext;
          $fileUrl = strlen($ext) > 0 ? substr($images[$i]['url'], 0, strpos($images[$i]['url'], $ext)) . $ext : $images[$i]['url'];
          $data = file_get_contents($fileUrl);
          $file = file_save_data($data, 'public://' . substr($fileUrl, strrpos($images[$i]['url'], '/') + 1), FILE_EXISTS_REPLACE);
          $fids[] = $file->id();
        }
      }
    } else {

      if ($this->generator->getImage()) {

        $ext = strtolower(pathinfo($this->generator->getImage(), PATHINFO_EXTENSION));
        $ext = strpos($ext, '?') ? substr($ext, 0, strpos($ext, '?')) : $ext;
        $fileUrl = strlen($ext) > 0 ? substr($this->generator->getImage(), 0, strpos($this->generator->getImage(), $ext)) . $ext : $this->generator->getImage();

        $mainImage = file_get_contents($fileUrl);
        $file = file_save_data($mainImage, 'public://' . substr($fileUrl, strrpos($this->generator->getImage(), '/') + 1), FILE_EXISTS_REPLACE);

        $fids[] = $file->id();
      }
    }
    return $fids;
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

  public function verifyExtension($string) {
    return $this->strposMultiple($string, ['jpg', 'jpeg', 'png', 'gif', 'bmp', ]);
  }

  public function strposMultiple($string, $patterns) {
    $patterns = is_array($patterns) ? $patterns : is_object($patterns) ? (array) $patterns : array($patterns);

    foreach($patterns as $pattern) {
      if (stripos($string, $pattern)) {
        return true;
      }
    }
  }
}
