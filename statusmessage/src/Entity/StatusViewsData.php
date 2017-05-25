<?php

namespace Drupal\statusmessage\Entity;

use Drupal\views\EntityViewsData;
use Drupal\views\EntityViewsDataInterface;

/**
 * Provides Views data for Status entities.
 */
class StatusViewsData extends EntityViewsData implements EntityViewsDataInterface {
  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['status']['table']['base'] = array(
      'field' => 'id',
      'title' => $this->t('Status'),
      'help' => $this->t('The Status ID.'),
    );

    return $data;
  }

}
