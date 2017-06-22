<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/21/17
 * Time: 7:41 PM
 */

namespace Drupal\statusmessage;


use Drupal\node\Entity\Node;

/**
 * @property \Drupal\statusmessage\MarkupGenerator generator
 * @property  message
 */
class StatusHeartPost implements SharedContentInterface {

  protected $url;

  protected $message;

  protected $generator;

  /**
   * StatusHeartPost constructor.
   * @param $url
   */
  public function __construct($url, $message = null) {
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

    return $this->generator->getTags();

  }


  private function setNodeData() {

    $node = Node::create([
      'type' => 'heartpost',
      'title' => $this->generator->getTitle(),
      'status' => 1,
    ]);

    if ($this->message) {
      $node->set('body', ['value' => '<div class="status-heartpost"> ' . $this->message . '</div>']);
    }
    $node->set('field_description', ['value' => '<div class="status-heartpost-description"> ' . $this->generator->getDescription() . '</div>', 'format' =>'full_html']);

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


}
