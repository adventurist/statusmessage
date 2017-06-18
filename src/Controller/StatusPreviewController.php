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



      $this->dom = new \DOMDocument;
      $contents = file_get_contents('http://' . $url);
      $this->dom->loadHTML($contents);

      $xpath = new \DomXpath($this->dom);

      $anchorAttributes = $this->getAnchorNodeNames();
      $imgAttributes = $this->getImgNodeNames();
      $imgLogos = $this->searchDom('img', 'logo');
      $anchorLogos = $this->searchDom('a', 'logo');



      $contents = file_get_contents('http://' . $url);
      $response = new Response();
      $response->setContent(\GuzzleHttp\json_encode(array('data' => $contents)));
      $response->headers->set('Content-Type', 'application/json');

      return $response;
    }

  }



  private function getAnchorNodeNames() {
    if ($this->dom) {
      $names = array();
      $attrXpath = new \DomXpath($this->dom);

      $nodes = $attrXpath->query('//a/@*');
      $i = 0;
      foreach ($nodes as $node) {
        $names[$i] = new \stdClass();
        $names[$i]->name = $node->nodeName;
        $names[$i]->value = $node->nodeValue;
        $i++;
      }

      return $names;
    }
  }

  private function getImgNodeNames() {
    if ($this->dom) {
      $names = array();
      $attrXpath = new \DomXpath($this->dom);

      $nodes = $attrXpath->query('//img/@*');
      $i = 0;
      foreach ($nodes as $node) {
        $names[$i] = new \stdClass();
        $names[$i]->name = $node->nodeName;
        $names[$i]->value = $node->nodeValue;
        $i++;
      }

      return $names;
    }
  }

  private function searchDom($tag, $string) {

    if ($this->dom) {

      $results = array();
      $tags = $this->dom->getElementsByTagName($tag);


      for ($i = 0; $i < $tags->length; $i++) {
        $results[$i] = new \stdClass();

        $src = $tags->item($i)->getAttribute('src');
        if (strpos($src, 'logo')) {
          $results[$i]->src = $src;
        }

        $href = $tags->item($i)->getAttribute('href');
        if (strpos($href, 'logo')) {
          $results[$i]->href = $href;
        }
      }

      return $results;
    }
  }

}
