<?php

namespace Drupal\statusmessage;
use Drupal\Core\Entity\Query\QueryFactory;
use Drupal\Core\Entity\EntityTypeManager;
use GuzzleHttp\Client;

/**
 * Class StatusTypeService.
 *
 * @package Drupal\statusmessage
 */
class ClientGeneratorService {

  protected $httpClient;

  /**
   * Constructor.
   */
  public function __construct(Client $http_client) {
    $this->httpClient = $http_client;
  }


  public function generatePreview($url) {

    $request = $this->httpClient->post('/statusmessage/generate/preview/', [
      'payload' => $url
    ]);

    $response = $request->getBody();

  }

}
