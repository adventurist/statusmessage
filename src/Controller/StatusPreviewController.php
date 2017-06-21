<?php

namespace Drupal\statusmessage\Controller;
require_once(DRUPAL_ROOT .'/vendor/autoload.php');

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\Markup;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\Client;
use Drupal\statusmessage\MarkupGenerator;



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

      $generator = new MarkupGenerator();

      if ($generator->parseMarkup($url)) {

        $preview = $generator->generatePreview();

        $response = new Response();
        $response->setContent(\GuzzleHttp\json_encode(array('data' => $preview)));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
      }

//      $contents = file_get_contents('http://' . $url);
//      $response = new Response();

//      $this->dom = new \DOMDocument;
//      $this->dom->loadHTML($contents);
//
//      $xpath = new \DomXpath($this->dom);
//
//      $anchorAttributes = $this->getAnchorNodeNames();
//      $imgAttributes = $this->getImgNodeNames();
//      $imgLogos = $this->searchDom('img', 'logo');
//      $anchorLogos = $this->searchDom('a', 'logo');
//

    }
    return false;
  }



//  private function getAnchorNodeNames() {
//    if ($this->dom) {
//      $names = array();
//      $attrXpath = new \DomXpath($this->dom);
//
//      $nodes = $attrXpath->query('//a/@*');
//      $i = 0;
//      foreach ($nodes as $node) {
//        $names[$i] = new \stdClass();
//        $names[$i]->name = $node->nodeName;
//        $names[$i]->value = $node->nodeValue;
//        $i++;
//      }
//
//      return $names;
//    }
//  }
//
//  private function getImgNodeNames() {
//    if ($this->dom) {
//      $names = array();
//      $attrXpath = new \DomXpath($this->dom);
//
//      $nodes = $attrXpath->query('//img/@*');
//      $i = 0;
//      foreach ($nodes as $node) {
//        $names[$i] = new \stdClass();
//        $names[$i]->name = $node->nodeName;
//        $names[$i]->value = $node->nodeValue;
//        $i++;
//      }
//
//      return $names;
//    }
//  }
//
//  private function searchDom($tag, $string) {
//
//    if ($this->dom) {
//
//      $results = array();
//      $tags = $this->dom->getElementsByTagName($tag);
//
//
//      for ($i = 0; $i < $tags->length; $i++) {
//        $results[$i] = new \stdClass();
//
//        $src = $tags->item($i)->getAttribute('src');
//        if (strpos($src, 'logo')) {
//          $results[$i]->src = $src;
//        }
//
//        $href = $tags->item($i)->getAttribute('href');
//        if (strpos($href, 'logo')) {
//          $results[$i]->href = $href;
//        }
//      }
//
//      return $results;
//    }
//  }

}
