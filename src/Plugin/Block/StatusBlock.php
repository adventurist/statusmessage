<?php

namespace Drupal\statusmessage\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block;


/**
 * Provides a 'StatusBlock' block.
 *
 * @Block(
 *  id = "status_block",
 *  admin_label = @Translation("Status block"),
 * )
 */

class StatusBlock extends BlockBase {


  /**
   * {@inheritdoc}
   */
  public function build() {

    return \Drupal::formBuilder()->getForm('Drupal\statusmessage\Form\StatusForm');

  }

}

