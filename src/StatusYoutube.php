<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/14/17
 * Time: 12:08 AM
 */

namespace Drupal\statusmessage;

use Drupal\heartbeat\Entity\Heartbeat;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


class StatusYoutube {

  protected $url;

  protected $message;

  protected $generator;


  public function __construct($url, $message = null) {
    $this->url = $url;
    $this->message = $message;
    $this->generator = new MarkupGenerator();
  }


  public function generateEmbed() {
    return '<iframe class="heartbeat-youtube" width="auto" height="auto" src="' . $this->parameter . '" frameborder="0"></iframe>';
  }

  public function generateNode() {

    if ($this->generateRequest()) {

      $node = $this->setNodeData();

      $this->processTerms();

      if ($node->save()) {
        return $node->id();
      }
    }
    return null;
  }

  public function setNodeData() {

    $provider_manager = \Drupal::service('video.provider_manager');
    $enabled_providers = $provider_manager->loadDefinitionsFromOptionList(array('youtube' => 'youtube'));

    if ($provider_matches = $provider_manager->loadApplicableDefinitionMatches($enabled_providers, $this->url)) {

      $definition = $provider_matches['definition'];
      $matches = $provider_matches['matches'];
      $uri = $definition['stream_wrapper'] . '://' . $matches['id'];
      $storage = \Drupal::entityManager()->getStorage('file');
      $results = $storage->getQuery()
        ->condition('uri', $uri)
        ->execute();
      if (!(count($results) > 0)) {
        $user = \Drupal::currentUser();
        $file = File::Create([
          'uri' => $uri,
          'filemime' => $definition['mimetype'],
          'filesize' => 1,
          'uid' => $user->id()
        ]);
        $file->save();
        $fid = $file->id();
      } else {
        $fid = array_values($results)[0];
      }
      $node = Node::create([
        'type' => 'youtube_video',
        'title' => $this->generator->getTitle(),
        'status' => 1,
        'uid' => \Drupal::currentUser()->id(),
        'field_video_embed' => $fid,
      ]);

      if ($this->message) {
        $node->set('body', [
          'value' => '<div class="status-youtube"> ' . $this->message . ' </div>',
          'format' => 'full_html'
        ]);
      }

      return $node;

    }

    return null;
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
          Heartbeat::updateTermUsage(array_values($tid)[0], 'tags');
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
            Heartbeat::newTermUsage($term->id());
          }
        }
      }
      $i++;
    }
    return $tids;
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


  private function generateRequest() {
    return $this->generator->parseMarkup($this->url);
  }

}

