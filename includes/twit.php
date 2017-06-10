<?php

require_once(DRUPAL_ROOT . '/sites/all/libraries/twitter-api-php/TwitterAPIExchange.php');

function twit_submit_libraries_info() {

    $libraries['twitter-api-php'] = array(
        'name' => 'Twitter API Exchange',
        'vendor url' => 'https://github.com/J7mbo/twitter-api-php',
        'files' => array(
            'php' => array('TwitterAPIExchange.php'), //this can be a path to the file location like array('lib/simple.js')
        ),
    );

    return $libraries;
}

/**
*Add new hashtag to taxonomy as hashtag vocabulary type
*/
function twit_submit_block_taxonomy_add($term) {
  $taxTerm = new stdClass();
  $taxTerm->name = $term;
  $taxTerm->vid = 6;
  taxonomy_term_save($taxTerm);
  return $taxTerm->tid;
}

/**
*Check to see if hashtag is already in taxonomy before adding
*/
function twit_submit_block_taxonomy_check($term)  {
  $query = db_select('taxonomy_term_data', 'ttd')
    ->fields('ttd', array('tid', 'name'))
    ->condition('ttd.name', $term->text);
  $query = db_query('SELECT tid FROM taxonomy_term_data WHERE name = :name', array(
    ':name' => $term->text)
  );
  if ($query->rowCount() >= 2) {
    dpm($query->rowCount());
    $result = $query->fetchObject();
    dpm($result);
    $tid = $result->tid;
  } else {
    $tid = twit_submit_block_taxonomy_add($term->text);
  }
  return $tid;
}

/**
*Update tracking of hashtag trends
*/
function twithash_block_update($hashArray, $unixtime, $uid, $ip, $tweetId = NULL, $location = NULL) {
  $tmid = null;
  if ($location == NULL) {
    if ($ip == '127.0.0.1') {
      $geo = 1;
    } else {
      $geo = 1;
    }//Handling of non-local IPs to be added later
  } else {
    $checkLocQuery = db_query(
      ' SELECT id FROM twithash_geo
        WHERE country = :country
        AND city = :city
        AND region = :region',
        array(
          ':country' => 'Canada',
          ':city' => $location->city,
          ':region' => $location->province,
        )
    );
    $locResult = $checkLocQuery->fetchAll();
    if ($checkLocQuery->rowCount() > 0) {
      $geo = $locResult[0]->id;
    } else {
      $locInsert = db_insert('twithash_geo')
                      ->fields(array(
                        'country' => 'Canada',
                        'city' => $location->city,
                        'region' => $location->province,
                      ));
      $locId = $locInsert->execute();

      if ($locId != NULL && $locId > 0) {
        $geo = $locId;
      }
    }
  }
  $count = count($hashArray);
  //Populate twithash_term table with new terms or update number of hits for recurring terms. Simultaneously update twithash_term_update table which
  //tracks specific dates for each time a given term is searched.
  for ($i = 0; $i<$count; $i++)  {
    $keyword = $hashArray[$i];

    $transaction = db_transaction();
    try{
      $tID = db_query('insert into twithash_term (term, hits, start) values (:term, 1, :start) on DUPLICATE KEY UPDATE hits = hits + :hits',
        array(
          ':term'  => $keyword,
          ':start'  =>  $unixtime,
          ':hits' => 1
        ),
        array('return' => Database::RETURN_INSERT_ID));
        //Insert IDs are collected for use in the query_master table, which tracks which different terms were compared up to a maximum
        //of 5 terms (the maximum allowed by Google Trends)
        if ($tID != 0)
        db_insert('twithash_term_update')
          ->fields(array(
            't_id'  =>  $tID,
            'hit_time'  =>  $unixtime
          ))
          ->execute();
          $tIDs[] = $tID;
    }
    catch (Exception $e) {
    $transaction->rollback();
    throw $e;
    }
  }

    $numTerms = isset($tIDs) ? count($tIDs) : 0;//Get number of terms in query
    switch ($numTerms)  {//add overall query to twithash_master
      case 0:
      break;
      case 1:
        $tmid = db_query('
        INSERT INTO twithash_master (uid, query_date, geo, source, tid_1) VALUES (:uid, :query_date, :geo, :source, :tid_1)',
          array(
            ':uid'  => $uid,
            ':query_date'  =>  $unixtime,
            ':geo'  => $geo,
            ':source' => 1,
            ':tid_1'  =>  $tIDs[0]
          ),
        array('return' => Database::RETURN_INSERT_ID));
      break;
      case 2:
        $tmid = db_query('insert into twithash_master (uid, query_date, geo, source, tid_1, tid_2) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2)',
          array(
            ':uid'  => $uid,
            ':query_date'  =>  $unixtime,
            ':geo'  => $geo,
            ':source' => 1,
            ':tid_1'  =>  $tIDs[0],
            ':tid_2'  =>  $tIDs[1]
          ),
        array('return' => Database::RETURN_INSERT_ID));
      break;
      case 3:
        $tmid = db_query('insert into twithash_master (uid, query_date, geo, source, tid_1, tid_2, tid_3) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3)',
          array(
            ':uid'  => $uid,
            ':query_date'  =>  $unixtime,
            ':geo'  => $geo,
            ':source' => 1,
            ':tid_1'  =>  $tIDs[0],
            ':tid_2'  =>  $tIDs[1],
            ':tid_3'  =>  $tIDs[2]
          ),
        array('return' => Database::RETURN_INSERT_ID));
      break;
      case 4:
        $tmid = db_query('insert into twithash_master (uid, query_date, geo, source, tid_1, tid_2, tid_3, tid_4) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3, :tid_4)',
          array(
            ':uid'  => $uid,
            ':query_date'  =>  $unixtime,
            ':geo'  => $geo,
            ':source' => 1,
            ':tid_1'  =>  $tIDs[0],
            ':tid_2'  =>  $tIDs[1],
            ':tid_3'  =>  $tIDs[2],
            ':tid_4'  =>  $tIDs[3]
          ),
        array('return' => Database::RETURN_INSERT_ID));
      break;
      case 5:
        $tmid = db_query('insert into twithash_master (uid, query_date, geo, source, tid_1, tid_2, tid_3, tid_4, tid_5) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3, :tid_4, :tid_5)',
          array(
            ':uid'  => $uid,
            ':query_date'  =>  $unixtime,
            ':geo'  => $geo,
            ':source' => 1,
            ':tid_1'  =>  $tIDs[0],
            ':tid_2'  =>  $tIDs[1],
            ':tid_3'  =>  $tIDs[2],
            ':tid_4'  =>  $tIDs[3],
            ':tid_5'  =>  $tIDs[4]
          ),
        array('return' => Database::RETURN_INSERT_ID));
      break;
    }

    if ($tweetId != NULL && $tmid != NULL) {
      $tweetIdQuery = db_insert('twithash_tid')
        ->fields(array(
          'tweetId' => $tweetId,
          'thmid' => $tmid))
        ->execute();
    }
    return $tmid;
}

/**
*Provide simple form for visitor to submit tweets
*/
function twit_submit_block_form() {
  $form = array();
  $form['twit_fieldset'] = array(
    '#type' => 'fieldset',
    '#title' => t('SUBMIT TWEET'),
    '#prefix' => '<div id="terms-fieldset-wrapper">',
    '#suffix' => '</div>',
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
    '#ajax' => array(
      'callback' => 'twit_submit_block_submit_callback',
    )
//    '#attributes' => array('onkeypress' => 'if(event.keyCode==13){ this.form.submit;}'),
  );
  $form['twit_fieldset']['tweet'] = array(
    '#type' => 'textarea',
    '#title' => t('Tweet'),
    '#description' => t('Please enter Tweet URL'),
    '#maxlength' => 80,
    '#size' => 25,
    '#attributes' => array('onchange' => 'tweetCheck(this.form.tweet.value)',),

  );

  $form['twit_fieldset']['submit'] = array(
    '#type' => 'submit',
    '#value' => 'Submit',
    '#id' => 'twitSubmitBtn',
    '#ajax' => array(
        'callback' => 'twit_submit_block_submit_callback',
      )
  );

  $form['twit-notification'] = array(
    '#prefix' => '<div id="twit-notification">',
    '#suffix' => '</div>',
  );
  $form['twit-check'] = array(
    '#prefix' => '<div id="twit-check">',
    '#suffix' => '</div>',
  );

  $form['#attached']['js'][] = array(
    'type' => 'inline',
    'data' => drupal_get_path('module', 'twit_submit') . '/twit_submit.js',
    'type' => 'file',
  );


  return $form;
}

/**
*Form submit handler
*/

function twit_submit_form_submit($form, &$form_state) {
    twit_submit_block_submit_callback($form, $form_state);
}

function twit_submit_block_submit_callback($form, &$form_state)  {
  $form_state['rebuild'] = TRUE;
  //define Tokens for REST API request
  $settings = array(
      'oauth_access_token' => "181287687-FNQLOpVXocD3gP46souGOj4FY1kMPlDzNw795MwQ",
      'oauth_access_token_secret' => "w9KuI6T44w2HX6P8OCcOUDePHdyT6l0iGRCljKuZF9AE9",
      'consumer_key' => "GULWdbfgUBicLUxEAD5a6KeE6",
      'consumer_secret' => "6ereWWc9nkTZCS0TtgCDfW4tcTO3E5Wy5LW1kXk8qSrxL2YBAD"
  );
  //Acquire tweet ID
  $tweetSubmit = $form_state['values']['tweet'];
  $tweetIdArr = explode('status/', $tweetSubmit);
  $tweetId = $tweetIdArr[1];
  if ($tweetId == null) {
      return null;
  }
  $url = 'https://api.twitter.com/1.1/statuses/show.json';
  $getfield = '?id='.$tweetId.'&tweet_mode=extended';
  $requestMethod = 'GET';

  $twitter = new TwitterAPIExchange($settings);
  $response = $twitter->setGetfield($getfield)
      ->buildOauth($url, $requestMethod)
      ->performRequest();
  //Decode request
  $tresponse_decoded = json_decode($response);
  dpm($tresponse_decoded);

  //Create datetime object for title, media file path and content date field
  $nowtime = new DateTime();
  $unixtime = $nowtime->getTimestamp();
  $today = date("m.d.y");
  //Get screen name for title, media file path and content screen name field
  $twit_user = $tresponse_decoded->user->screen_name;
  //Check access level of user submitting tweet
  global $user;
  $ip = $user->data['geoip_location']['ip_address'];//get user's IP
  $uid = $user->uid;
  //Handler to optionally disable publishing of tweets based on access level
  if (in_array('content administrator', $user->roles) || in_array('administrator', $user->roles))  {
    $articleStatus = 1;
    $articlePromote = 0;
  }else{
    $articleStatus = 1;
    $articlePromote = 0;
  }
  //Prepare node object
  $node = new stdClass();
  $node->type = 'tweet';
  $node->language = LANGUAGE_NONE;
  node_object_prepare($node);
  //Define node title
  $node->title = $twit_user . '_' . $nowtime->format('Y.m.d.Hi');
  //Set basic node data: language, status, promoted, uid, posted/created date and tweet ID
  $node->body[$node->language][0]['value'] = $tresponse_decoded->full_text;
  $node->body[$node->language][0]['format'] = 'filtered_html';
  $node->status = $articleStatus;
  $node->promote = $articlePromote;
  $node->workbench_access = array('twitcher_tweet' => 'twitcher_tweet');
  $node->uid = $uid;
  $node->date = date('Y-m-d');//***!This should be changed to previous date variable
  $node->created = time();
  $node->field_tweet_id[$node->language][0]['value'] = $tresponse_decoded->id;
  //Get hashtags, populate taxonomy and set content type to tid
  $hashArray = array();
  $i = 0;
  foreach($tresponse_decoded->entities->hashtags as $key => $h) {
    $hashArray[] = $h->text;
    $tid = twit_submit_block_taxonomy_check($h);
    $node->field_hashtag[$node->language][$i]['tid'] = $tid;
    $i++;
  }
  $twithashUpdate = twithash_block_update($hashArray, $unixtime, $uid, $ip, $tresponse_decoded->id);
  if (!empty($tresponse_decoded->entities->user_mentions)) {
    $userArray = array();

    foreach($tresponse_decoded->entities->user_mentions as $userName) {
      $userArray[] = $userName->screen_name;
    }
    $twitUserUpdate = twituser_block_update($userArray, $unixtime, $uid, $ip, $tresponse_decoded->id);
  }
  //Set content's link field to urls as displayed within the tweet
  $i = 0;
  if (!empty($tresponse_decoded->entities->urls)) {
    foreach ($tresponse_decoded->entities->urls as $url)  {
      $node->field_tweet_links[$node->language][$i]['value'] = $tresponse_decoded->entities->urls[$i]->display_url;
      $i++;
    }
  }
  if (!empty($tresponse_decoded->user->profile_image_url_https)) {
      $node->field_profile_pic[$node->language][0]['value'] = $tresponse_decoded->user->profile_image_url_https;
  }
  //Check for attached media and create a directory for saving
  if (isset($tresponse_decoded->extended_entities->media)) {
    if (!is_dir(DRUPAL_ROOT . '/sites/default/files/Tweet_Media/' . $today))  {
      mkdir(DRUPAL_ROOT . '/sites/default/files/Tweet_Media/' . $today);
    }
    //Save each media entity with a unique filename within directory
    $i = 0;
    foreach($tresponse_decoded->extended_entities->media as $media)  {
      $ext = substr($media->media_url, -3);
      $filename = 'public://Tweet_Media/' . $today . '/' . $twit_user . $unixtime . $i . '.' . $ext;
      file_put_contents($filename, file_get_contents($media->media_url));
      //Download file and save to Drupal filesystem
      $image = file_get_contents($media->media_url);
      $file = file_save_data($image, $filename,FILE_EXISTS_REPLACE);
      //Associate file with content image field
      $node->field_tweet_images[$node->language][$i] = array(
          'fid' => $file->fid,
          'filename' => $file->filename,
          'filemime' => $file->filemime,
          'uid' => 1,
          'uri' => $file->uri,
          'status' => 1
      );
      $i++;
    }
    if(!empty($tresponse_decoded->extended_entities->media[0]->video_info->variants)) {
      $z = null;
      $vidUrl = null;
      $bitrate = new stdClass();
      $bitrate->value = null;
      $bitrate->index = null;

      for ($z = 0; $z < $tresponse_decoded->extended_entities->media[0]->video_info->variants; $z++) {
        if (!empty($tresponse_decoded->extended_entities->media[0]->video_info->variants[$z]->bitrate) &&
          $tresponse_decoded->extended_entities->media[0]->video_info->variants[$z]->content_type === 'video/mp4') {
          if ($tresponse_decoded->extended_entities->media[0]->video_info->variants[$z]->bitrate > $bitrate->value) {
            $bitrate->value = $tresponse_decoded->extended_entities->media[0]->video_info->variants[$z]->bitrate;
            $bitrate->index = $z;
          }
        }
      }

      if ($bitrate->index !== null) {
        $vidUrl = $tresponse_decoded->extended_entities->media[0]->video_info->variants[$bitrate->index]->url;
      }

        $destination = 'public://Tweet_Media/' . $today;

        if ($vFile = system_retrieve_file($vidUrl, $destination, TRUE, FILE_EXISTS_REPLACE)) {
            $node->field_tweet_video[$node->language][0]['value'] = $vFile->uri;
        }
    }
  }
  //Set content screen name, date and tweet url
  $node->field_screen_name[$node->language][0]['value'] = $twit_user;
  $node->field_tweet_date[$node->language][0]['value'] = $unixtime;
  $node->field_tweet_date[$node->language][0]['timezone'] = 'America/New_York';
  $node->field_tweet_date[$node->language][0]['data_type'] = 'datestamp';
  $node->field_tweet_url[$node->language][0]['value'] = $tweetSubmit;
  //Set the node path and save
  $path = 'tweet/' . $tweetId;
  $node->path = array('alias' => $path);
  // if(node_save($node))  {
  //   $commands[] = ajax_command_html('#twit-notification', 'Tweet submitted to Twitcher!');
  // }else{
  //
  //   $commands[] = ajax_command_html('#twit-notification', 'Tweet could not be saved.');
  // }

  try {
    node_save($node);
    $commands[] = ajax_command_css('#twit-notification', array("display" => "block"));
    $commands[] = ajax_command_html('#twit-notification', 'Tweet submitted to Twitcher!');
  }catch(Exception $e)  {
    dpm($e->getMessage());
    $commands[] = ajax_command_css('#twit-notification', array("display" => "block"));
    $commands[] = ajax_command_html('#twit-notification', 'Tweet could not be saved.');
  }

  $commands[] = ajax_command_invoke('#edit-tweet', 'val', array(''));
  // $commands[] = ajax_command_invoke('#twit-notification', 'addClass', array('twit-active'));
  $commands[] = ajax_command_invoke('#twit-notification', 'delay', array(3000));
  $commands[] = ajax_command_invoke('#twit-notification', 'fadeOut', array('slow'));


  return array('#type' => 'ajax', '#commands' => $commands);
}

/*
 * Implements hook_block_info()
*/
/**
 * @return mixed
 */
function twit_submit_block_info() {
  $blocks['twit_submit'] = array(
    'info' => t('Submit tweet as article/node!'),
  );

  return $blocks;
}

/*
 * Implements hook_block_view()
*/
/**
 * @param $delta
 * @return array
 */
function twit_submit_block_view($delta) {
  $block = array();
  $twit_form = drupal_get_form('twit_submit_block_form');

  switch ($delta) {
    case 'twit_submit':
      $block['subject'] = t('Widget to submit tweets and save as node');
      $block['content'] = drupal_render($twit_form);
      break;
  }
  return $block;
}


/**
 * @param $userArray
 * @param $unixtime
 * @param $uid
 * @param $ip
 * @param null $tweetId
 * @param null $location
 * @return DatabaseStatementInterface|int|null
 * @throws Exception
 */
function twituser_block_update($userArray, $unixtime, $uid, $ip, $tweetId = NULL, $location = NULL) {
  $tumid = null;
  // watchdog('twituser', var_dump($userArray));
  if ($ip == '127.0.0.1' && $location == NULL) {
    $geo = 1;
  } else {
    $location = smart_ip_get_location($ip);
    $geo = 1;
  }//Handling of non-local IPs to be added later
  $count = count($userArray);
  //Populate twithash_term table with new terms or update number of hits for recurring terms. Simultaneously update twithash_term_update table which
  //tracks specific dates for each time a given term is searched.
  for ($i = 0; $i<$count; $i++)  {
    $userName = $userArray[$i];

    $transaction = db_transaction();
    try{
      $tID = db_query('insert into twituser_name (name, hits, start) values (:name, 1, :start) on DUPLICATE KEY UPDATE hits = hits + :hits',
        array(
          ':name'  => $userName,
          ':start'  =>  $unixtime,
          ':hits' => 1
        ),
        array('return' => Database::RETURN_INSERT_ID));
        //Insert IDs are collected for use in the query_master table, which tracks which different terms were compared up to a maximum
        //of 5 terms (the maximum allowed by Google Trends)
        if ($tID != 0)
        db_insert('twituser_name_update')
          ->fields(array(
            't_id'  =>  $tID,
            'hit_time'  =>  $unixtime
          ))
          ->execute();
          $tIDs[] = $tID;
    }
    catch (Exception $e) {
      $transaction->rollback();
    throw $e;
    }
  }
  $numTerms = isset($tIDs) ? count($tIDs) : 0;//Get number of terms in query
  switch ($numTerms)  {//add overall query to twithash_master
    case 0:
    break;
    case 1:
      $tumid = db_query('insert into twituser_master (uid, query_date, geo, source, tid_1) values (:uid, :query_date, :geo, :source, :tid_1)',
        array(
          ':uid'  => $uid,
          ':query_date'  =>  $unixtime,
          ':geo'  => $geo,
          ':source' => 1,
          ':tid_1'  =>  $tIDs[0]
        ),
        array('return' => Database::RETURN_INSERT_ID));
    break;
    case 2:
      $tumid = db_query('insert into twituser_master (uid, query_date, geo, source, tid_1, tid_2) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2)',
        array(
          ':uid'  => $uid,
          ':query_date'  =>  $unixtime,
          ':geo'  => $geo,
          ':source' => 1,
          ':tid_1'  =>  $tIDs[0],
          ':tid_2'  =>  $tIDs[1]
        ),
      array('return' => Database::RETURN_INSERT_ID));
    break;
    case 3:
      $tumid = db_query('insert into twituser_master (uid, query_date, geo, source, tid_1, tid_2, tid_3) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3)',
        array(
          ':uid'  => $uid,
          ':query_date'  =>  $unixtime,
          ':geo'  => $geo,
          ':source' => 1,
          ':tid_1'  =>  $tIDs[0],
          ':tid_2'  =>  $tIDs[1],
          ':tid_3'  =>  $tIDs[2]
        ),
        array('return' => Database::RETURN_INSERT_ID));
    break;
    case 4:
      $tumid = db_query('insert into twituser_master (uid, query_date, geo, source, tid_1, tid_2, tid_3, tid_4) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3, :tid_4)',
        array(
          ':uid'  => $uid,
          ':query_date'  =>  $unixtime,
          ':geo'  => $geo,
          ':source' => 1,
          ':tid_1'  =>  $tIDs[0],
          ':tid_2'  =>  $tIDs[1],
          ':tid_3'  =>  $tIDs[2],
          ':tid_4'  =>  $tIDs[3]
        ),
      array('return' => Database::RETURN_INSERT_ID));
    break;
    case 5:
      $tumid = db_query('insert into twituser_master (uid, query_date, geo, source, tid_1, tid_2, tid_3, tid_4, tid_5) values (:uid, :query_date, :geo, :source, :tid_1, :tid_2, :tid_3, :tid_4, :tid_5)',
        array(
          ':uid'  => $uid,
          ':query_date'  =>  $unixtime,
          ':geo'  => $geo,
          ':source' => 1,
          ':tid_1'  =>  $tIDs[0],
          ':tid_2'  =>  $tIDs[1],
          ':tid_3'  =>  $tIDs[2],
          ':tid_4'  =>  $tIDs[3],
          ':tid_5'  =>  $tIDs[4]
        ),
      array('return' => Database::RETURN_INSERT_ID));
    break;
  }


  if ($tweetId != NULL && $tumid != NULL) {
    $tweetIdQuery = db_insert('twituser_tid')
      ->fields(array(
        'tweetId' => $tweetId,
        'tumid' => $tumid))
      ->execute();
  }

  return $tumid > 0 ? $tumid : -1;
}
