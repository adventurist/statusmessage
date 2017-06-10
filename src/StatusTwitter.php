<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/9/17
 * Time: 4:12 PM
 */

namespace Drupal\statusmessage;


class StatusTwitter {

  protected $oauthAccessToken;
  protected $oauthAccessTokenSecret;
  protected $consumerKey;
  protected $consumerSecret;


  public function __construct() {
  }

  /**
   * @return mixed
   */
  public function getConsumerSecret()
  {
    return $this->consumerSecret;
  }

  /**
   * @param mixed $consumerSecret
   */
  public function setConsumerSecret($consumerSecret)
  {
    $this->consumerSecret = $consumerSecret;
  }

  /**
   * @return mixed
   */
  public function getConsumerKey()
  {
    return $this->consumerKey;
  }

  /**
   * @param mixed $consumerKey
   */
  public function setConsumerKey($consumerKey)
  {
    $this->consumerKey = $consumerKey;
  }

  /**
   * @return mixed
   */
  public function getOauthAccessTokenSecret()
  {
    return $this->oauthAccessTokenSecret;
  }

  /**
   * @param mixed $oauthAccessTokenSecret
   */
  public function setOauthAccessTokenSecret($oauthAccessTokenSecret) {
    $this->oauthAccessTokenSecret = $oauthAccessTokenSecret;
  }

  /**
   * @return mixed
   */
  public function getOauthAccessToken() {
    return $this->oauthAccessToken;
  }

  /**
   * @param mixed $oauthAccessToken
   */
  public function setOauthAccessToken($oauthAccessToken) {
    $this->oauthAccessToken = $oauthAccessToken;
  }


}
