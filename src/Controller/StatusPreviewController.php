<?php

namespace Drupal\statusmessage\Controller;

use Drupal\Core\Controller\ControllerBase;

/**
 * Class StatusPreviewController.
 *
 * @package Drupal\statusmessage\Controller
 */
class StatusPreviewController extends ControllerBase {

  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function generate() {
    return [
      '#type' => 'markup',
      '#markup' => $this->t('Implement method: generate')
    ];
  }

}
