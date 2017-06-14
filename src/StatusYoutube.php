<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/14/17
 * Time: 12:08 AM
 */

namespace Drupal\statusmessage;

use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


class StatusYoutube {

  protected $parameter;


  public function __construct($parameter) {
    $this->parameter = $parameter;
  }


  private function parseUrl ($text) {
    return explode('status/', $text)[1];
  }

  public function generateEmbed() {
    return '<iframe class="heartbeat-youtube" width="auto" height="auto" src="' . $this->parameter . '" frameborder="0"></iframe>';
  }

  public function generateNode()
  {

    $provider_manager = \Drupal::service('video.provider_manager');
    $enabled_providers = $provider_manager->loadDefinitionsFromOptionList(array('youtube' => 'youtube'));

    if ($provider_matches = $provider_manager->loadApplicableDefinitionMatches($enabled_providers, $this->parameter)) {

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
        'title' => 'dev_title',
        'uid' => \Drupal::currentUser()->id(),
        'field_video_embed' => $fid,
      ]);

      $node->save();

      return $node->id();

    }
  }
}

