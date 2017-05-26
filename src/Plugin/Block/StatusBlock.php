<?php

namespace Drupal\statusmessage\Plugin\Block;

use Drupal\Core\Block\BlockBase;

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
    $build = [];
    $build['status_block']['#markup'] = 'Implement StatusBlock.';

    $form = \Drupal::formBuilder()->getForm('Drupal\statusmessage\Form\StatusForm');

    return $form;
  }

}

