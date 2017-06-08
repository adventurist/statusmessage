<?php
namespace Drupal\statusmessage\Ajax;

use Drupal\Core\Ajax\CommandInterface;

class ClientCommand implements CommandInterface {
  protected $message;

  public function __construct($message) {
    $this->message = $message;
  }

  public function render() {

    return array(
      'command' => 'generatePreview',
      'url' => $this->message
    );
  }
}
