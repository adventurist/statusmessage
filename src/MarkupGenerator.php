<?php

namespace Drupal\statusmessage;
use GuzzleHttp\Client;
use Embed\Embed;
use Drupal\statusmessage\TemplateCreator;

/**
 * Class MarkupGenerator.
 *
 * @package Drupal\statusmessage
 */
class MarkupGenerator implements Parser {

  public $match;

  public $parsedMarkup;

  /**
   * GuzzleHttp\Client definition.
   *
   * @var \GuzzleHttp\Client
   */
  protected $httpClient;
  /**
   * Constructs a new MarkupGenerator object.
   */
//  public function __construct(Client $http_client) {
//    $this->httpClient = $http_client;
//  }

  /**
   * @param $url
   * @return mixed
   */
  public function validateUrl($text) {
    preg_match_all('#\bhttps?://[^,\s()<>]+(?:\([\w\d]+\)|([^,[:punct:]\s]|/))#', $text, $this->match);
    return $this->match;
  }

  /**
   * @param $url
   * @return mixed
   */
  public function parseMarkup($url) {
    $url = strpos($url, 'http') ? 'http://' . $url : $url;
    $url = !is_array($url) ? $url : array_values($url)[0];
    $this->parsedMarkup = Embed::create($url);
    return true;
  }

  /**
   * @param $url
   * @return mixed
   */
  public function generatePreview() {

    if (!$this->parsedMarkup) {
      return null;
    }

    $templateCreator = new TemplateCreator();

    $templateCreator->generateTitle($this->parsedMarkup->title);
    $templateCreator->generateDescription($this->parsedMarkup->description);
    $templateCreator->generateImage($this->parsedMarkup->image);

    return $templateCreator->getPreview();

  }


  /**
   * @return mixed
   */

  public function getImages() {
    return $this->parsedMarkup->images;
  }

  /**
   * @return mixed
   */
  public function getImage() {
    return $this->parsedMarkup->image;
  }

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->parsedMarkup->title;
  }

  /**
   * @return mixed
   */
  public function getDescription() {
    return $this->parsedMarkup->description;
  }

  /**
   * @return mixed
   */
  public function getTags() {
    return $this->parsedMarkup->tags;
  }


}
