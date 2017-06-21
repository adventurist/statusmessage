<?php

namespace Drupal\statusmessage;

/**
 * Interface Parser.
 *
 * @package Drupal\statusmessage
 */
interface Parser {


  /**
   * @param $url
   * @return mixed
   */
  public function validateUrl($url);

  /**
   * @param $url
   * @return mixed
   */
  public function parseMarkup($url);

  /**
   * @param $url
   * @return mixed
   */
  public function generatePreview();


}
