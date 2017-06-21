<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/20/17
 * Time: 7:51 PM
 */

namespace Drupal\statusmessage;


class TemplateCreator {

  protected $markup;
  protected $images;
  protected $imageMarkup;
  protected $title;
  protected $description;

  /**
   * @param $image
   */
  public function generateImage($image) {
    $this->images[] = '<img class="statusmessage-image" src="' . $image . '"/ >"';
  }

  /**
   * @param $title
   */
  public function generateTitle($title) {
    $this->title = '<h3 class="statusmessage-title">' . $title . '</h3>';
  }

  /**
   * @param $description
   */
  public function generateDescription($description) {
    $this->description = '<p class="statusmessage-description">' . $description. '</p>';
  }

  /**
   *
   */
  private function generateImageMarkup() {
    foreach ($this->images as $image) {
      $this->imageMarkup .= $image;
    }
  }

  /**
   * @return string
   */
  public function getPreview() {

    if ($this->imageMarkup === null) {
      $this->generateImageMarkup();
    }

    return $this->wrap($this->title . $this->description . $this->imageMarkup);
  }

  private function wrap($string) {
    return '<div class="statusmessage-preview">' . $string . '</div>';
  }

}
