<?php

namespace Drupal\statusmessage\Controller;
require_once(DRUPAL_ROOT .'/vendor/autoload.php');

use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;



/**
 * Class StatusPreviewController.
 *
 * @package Drupal\statusmessage\Controller
 */
class StatusPreviewController extends ControllerBase {

  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('http_client'));
  }

  /**
   * Constructor.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }
  /**
   * Generate.
   *
   * @return string
   *   Return Hello string.
   */
  public function generate($url) {

    if ($url == 'build') {
      $url = \Drupal::request()->get('data');

      $contents = file_get_contents('http://' . $url);
      $response = new Response();
      $response->setContent(\GuzzleHttp\json_encode(array('data' => $contents)));
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

  }

}
