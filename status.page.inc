<?php

/**
 * @file
 * Contains status.page.inc.
 *
 * Page callback for Status entities.
 */

use Drupal\Core\Render\Element;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Prepares variables for Status templates.
 *
 * Default template: status.html.twig.
 *
 * @param array $variables
 *   An associative array containing:
 *   - elements: An associative array containing the user information and any
 *   - attributes: HTML attributes for the containing element.
 */
function template_preprocess_status(array &$variables) {
  // Fetch Status Entity Object.
  $status = $variables['elements']['#status'];

  // Helpful $content variable for templates.
  foreach (Element::children($variables['elements']) as $key) {
    $variables['content'][$key] = $variables['elements'][$key];
  }

  $variables['content']['status_message'] = $status->getMessage()->view();

}

/**
* Prepares variables for a custom entity type creation list templates.
*
* Default template: status-content-add-list.html.twig.
*
* @param array $variables
*   An associative array containing:
*   - content: An array of status-types.
*
* @see block_content_add_page()
*/
function template_preprocess_status_content_add_list(&$variables) {
  $variables['types'] = array();
  $query = \Drupal::request()->query->all();
  foreach ($variables['content'] as $type) {
    $variables['types'][$type->id()] = array(
      'link' => Link::fromTextAndUrl($type->label(), new Url('entity.status.add_form', array(
        'status_type' => $type->id()
      ), array('query' => $query))),
      'description' => array(
      '#markup' => $type->label(),
      ),
      'title' => $type->label(),
      'localized_options' => array(
      'query' => $query,
      ),
    );
  }
}
