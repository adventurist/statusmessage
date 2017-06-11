<?php
/**
 * Created by IntelliJ IDEA.
 * User: logicp
 * Date: 6/9/17
 * Time: 4:12 PM
 */

namespace Drupal\statusmessage;

//require_once DRUPAL_ROOT .'/vendor/autoload.php';
require_once __DIR__ . './../includes/TwitterAPIExchange.php';

use TwitterAPIExchange;
use Drupal\statusmessage\Entity;
use Drupal\taxonomy\Entity\Term;
use Drupal\node\Entity\Node;
use Drupal\file\Entity\File;


class StatusTwitter {

  protected $oauthAccessToken;
  protected $oauthAccessTokenSecret;
  protected $consumerKey;
  protected $consumerSecret;
  protected $parameter;

  private $twitterConfig;

  public function __construct($parameter) {
    $this->twitterConfig = \Drupal::config('twitter_api.settings');
    $this->parameter = $parameter;
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
  public function setConsumerKey($consumerKey) {
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

  private function getApiStatusParameter() {
    return 'https://api.twitter.com/1.1/statuses/show.json';
  }


  private function generateRequest($url) {

    $twid = $this->parseUrl($url);

    $settings = [
      'oauth_access_token' => $this->twitterConfig->get('oauth_access_token'),
      'oauth_access_token_secret' => $this->twitterConfig->get('oauth_access_token_secret'),
      'consumer_key' => $this->twitterConfig->get('consumer_key'),
      'consumer_secret' => $this->twitterConfig->get('consumer_secret'),
    ];

    $twitterApi = new TwitterAPIExchange($settings);
    $getField = '?id=' . $twid . '&tweet_mode=extended';
    return $twitterApi
      ->setGetfield($getField)
      ->buildOauth($this->getApiStatusParameter(), 'GET');
  }

  public function sendRequest() {

    if ($response = $this->generateRequest($this->parameter)->performRequest()) {

      $data = json_decode($response);
      $tweetNode = $this->setNodeData($data);

      $media = $this->getTweetMedia($data);

      if ($media->images) {
        $tweetNode->set('field_tweet_images', $media->images);
      }

      if ($media->video) {
        $tweetNode->set('field_tweet_video', $media->video);
      }


      if ($tweetNode->save()) {
        return $tweetNode->id();
      }
      return null;
    }
  }

  private function parseUrl ($text) {
    return explode('status/', $text)[1];
  }


  private function setNodeData($data) {

    //Create datetime object for title, media file path and content date field
    $nowTime = new \DateTime();
    $user = \Drupal::currentUser();
    $ip =  \Drupal::request()->getClientIp();//get user's IP

    $tags = [];
    $users = [];
    $links = [];

    $terms = $this->processTerms($data);

    if (!empty($data->entities->urls)) {
      foreach ($data->entities->urls as $url)  {
        $links[] = $url->display_url;
      }
    }
    //Check for attached media and create a directory for saving
    if (isset($data->extended_entities->media)) {
      $media = $this->getTweetMedia($data);
    }

    if ($data->user->profile_image_url_https) {
      //TODO get profile image
    }

    $node = Node::create([
      'type' => 'tweet',
      'title' => $data->user->screen_name . '_' . $nowTime->format('Y.m.d.Hi'),
      'uid' => $user->id(),
      'field_tags' => $tags,
      'field_tweet_url' => $this->parameter,
      'field_twit_id' => $data->id,
      'field_post_date' => strtok($data->created_at, ' +'),
      'field_username' => $data->username,
      'field_users' => $users,
      'field_links' => $links,
      'status' => 1,
    ]);

    $node->set('body', $data->full_text);
    return $node;

  }


  private function getTweetMedia($data) {
    $media = new \stdClass();

    foreach($data->extended_entities->media as $media)  {
      $image = file_get_contents($media->media_url);
      $file = file_save_data($image);
      $images[] = $file->id();
    }
    if(!empty($data->extended_entities->media[0]->video_info->variants)) {
      $z = null;
      $vidUrl = null;
      $bitrate = new stdClass();
      $bitrate->value = null;
      $bitrate->index = null;

      for ($z = 0; $z < $data->extended_entities->media[0]->video_info->variants; $z++) {
        if (!empty($data->extended_entities->media[0]->video_info->variants[$z]->bitrate) &&
          $data->extended_entities->media[0]->video_info->variants[$z]->content_type === 'video/mp4') {
          if ($data->extended_entities->media[0]->video_info->variants[$z]->bitrate > $bitrate->value) {
            $bitrate->value = $data->extended_entities->media[0]->video_info->variants[$z]->bitrate;
            $bitrate->index = $z;
          }
        }
      }

      if ($bitrate->index !== null) {
        $data->extended_entities->media[0]->video_info->variants[$bitrate->index]->url;
        $video = system_retrieve_file($vidUrl, null, TRUE);
        $file = File::create([
          'id' => 'id',
        ])->save();
      }
    }

    $media->images = $images;
    $media->video = $video;

    return $media;
  }

  private function processTerms($data) {
    $terms = new \stdClass();
    $terms->tags = [];
    $terms->users = [];

    foreach($data->entities->hashtags as $key => $h) {
      $term = \Drupal::entityQuery('taxonomy_term')->condition('name', $h->text)->execute();
      if (count($term) < 1) {
        $term = Term::create(['name' => $h->text, 'vid' => 'twitter']);
        if ($term->save()) {
          $terms->tags[] = $term->id();
        } else {
          \Drupal::logger('StatusTwitter')->warning('Could not save term with name %name', array('%name' => $h->text));
        }
      } else {
        $terms->tags[] = array_values($term)[0];
      }
    }
    foreach($data->entities->user_mentions as $u) {
      $terms->users[] = $u->screen_name;
      $term = \Drupal::entityQuery('taxonomy_term')->condition('name', $h->text)->execute();
      if (count($term) < 1) {
        $term = Term::create(['name' => $h->text, 'vid' => 'twitter_user']);
        if ($term->save()) {
          $terms->users[] = $term->id();
        } else {
          \Drupal::logger('StatusTwitter')->warning('Could not save term with name %name', array('%name' => $h->text));
        }
      } else {
        $terms->users[] = array_values($term)[0];
      }
    }

    return($terms);
  }
}


