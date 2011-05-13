<?php

elgg_register_event_handler('init', 'system', 'facebook_api_init');

function facebook_api_init() {
	global $CONFIG;

	$base = elgg_get_plugins_path() . 'facebook_api';
	elgg_register_library('facebook', "$base/vendors/facebookoauth/src/facebook.php");
	elgg_register_library('facebook_api', "$base/lib/facebook_api.php");

	elgg_load_library('facebook_api');

	elgg_extend_view('css/elgg', 'facebook_api/css');

	// sign on with facebook
	if (facebook_api_allow_sign_on_with_facebook()) {
		elgg_extend_view('login/extend', 'facebook_api/login');
	}

	// register page handler
	elgg_register_page_handler('facebook_api', 'facebook_api_pagehandler');

	// allow plugin authors to hook into this service
	elgg_register_plugin_hook_handler('post', 'facebook_service', 'facebookservice_post');
	elgg_register_plugin_hook_handler('viewnote', 'facebook_service', 'facebookservice_viewnote');
	elgg_register_plugin_hook_handler('postnote', 'facebook_service', 'facebookservice_postnote');
	elgg_register_plugin_hook_handler('viewwall', 'facebook_service', 'facebookservice_viewwall');
	elgg_register_plugin_hook_handler('viewstatus', 'facebook_service', 'facebookservice_viewstatus');
	elgg_register_plugin_hook_handler('viewfeed', 'facebook_service', 'facebookservice_viewfeed');
	elgg_register_plugin_hook_handler('viewfeedgraph', 'facebook_service', 'facebookservice_viewfeedgraph');
        elgg_register_plugin_hook_handler('viewcomment', 'facebook_service', 'facebookservice_viewcomment');
        elgg_register_plugin_hook_handler('viewusername', 'facebook_service', 'facebookservice_viewusername');
        elgg_register_plugin_hook_handler('viewlike', 'facebook_service', 'facebookservice_viewlike');
	elgg_register_plugin_hook_handler('postcomment', 'facebook_service', 'facebookservice_postcomment');
	elgg_register_plugin_hook_handler('postlike', 'facebook_service', 'facebookservice_postlike');
	elgg_register_plugin_hook_handler('friendrequest','facebook_service','facebookservice_friendrequest');
}

function facebook_api_pagehandler($page) {

	global $CONFIG;
	if (!isset($page[0])) {
		forward();
	}

	$_GET['session'] = $CONFIG->input['session'];
	switch ($page[0]) {
		case 'authorize':
			facebook_api_authorize();
			break;
		case 'revoke':
			facebook_api_revoke();
			break;
		case 'login':
			facebook_api_login();
			break;
		default:
			forward();
			break;
	}
}



/**
 * Post to a facebook users wall.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_post($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');
    $site = elgg_get_site_entity();

    if(!$params['name']) {
	   $site_name = $site->name;
    }
    else {
	   $site_name = $params['name'];
    }
    
    if(!$params['logo']) {
	    $logo = elgg_get_site_url() .'_graphics/elgg_logo.png' ;
    }
    else {
	    $logo = $params['logo'] ;
    }

    if(!$params['link']) {
	   $link = elgg_get_site_url();
    }
    else {
	   $link = $params['link'];
    }

    $attachment =  array(
	'access_token' => $access_token,
	'message' => $params['message'],
	'name' => $site_name,
	'link' => $link,
	'description' => $params['description'],
	'picture' => $logo,
    );
		
    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();
    $ret_code=$facebook->api('/me/feed', 'POST', $attachment);
    
    return TRUE;
}


/**
 * Retrieve a facebook user's notes.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewnote($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    if(!$params['limit']) {
	   $limit = 10;
    }
    else {
	   $limit = $params['limit'];
    }
    
    $attachment =  array(
	'access_token' => $access_token,
	'limit' => $limit,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();

    //you can also use the facebook uid($target) in the request in place of me

    $fbnotes=$facebook->api('/me/notes', 'GET', $attachment);
	
    return $fbnotes;
}

/**
 * Retrieve a facebook user's notes.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_postnote($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');
    
    $attachment =  array(
	'access_token' => $access_token,
	'message' => $params['message'],
	'subject' => $params['subject'],
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();

    //you can also use the facebook uid($target) in the request in place of me

    $fbnotes=$facebook->api('/me/notes', 'POST', $attachment);
	
    return $fbnotes;
}

/**
 * Retrieve a facebook user's wall.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewwall($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    if(!$params['limit']) {
	   $limit = 10;
    }
    else {
	   $limit = $params['limit'];
    }
    
    $attachment =  array(
	'access_token' => $access_token,
	'limit' => $limit,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();

    //you can also use the facebook uid($target) in the request in place of me

    $fbwall=$facebook->api('/me/feed', 'GET', $attachment);
	
    return $fbwall;
}

/**
 * Retrieve a facebook user's statuses.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewstatus($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');


    if(!$params['limit']) {
	   $limit = 10;
    }
    else {
	   $limit = $params['limit'];
    }

    //limit = 1 will give the latest status
    $attachment =  array(
	'access_token' => $access_token,
	'limit' => $limit,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();

    //you can also use the facebook uid($target) in the request in place of me

    $fbstatus=$facebook->api('/me/statuses', 'GET', $attachment);
	
    return $fbstatus;
}

/**
 * Retrieve a facebook user's home feed.Uses fql(facebook query language) as it gives a greater level of flexibility like filtering the feeds 
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewfeed($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }


    $facebook = facebookservice_api();

    //how would you like to filter your feed.Available options are by network,all or applications(Applications option returns feed from pages,applications,etc)
    $filter = $params['choice'];
     
    switch ($filter) {

	case "network":
	    $fbhome = $facebook->api(array('method' => 'fql.query' , 'query'=> "SELECT post_id,viewer_id,source_id ,created_time,attachment,likes,comments,actor_id, target_id, message FROM stream WHERE filter_key in (SELECT filter_key FROM stream_filter WHERE uid = $target AND type = 'network')",'access_token' => $access_token,
	    ));
	    break;
	case "application":
	    $fbhome = $facebook->api(array('method'=>'fql.query','query'=> "SELECT post_id,viewer_id,source_id ,created_time,attachment,likes,comments,actor_id, target_id, message FROM stream WHERE filter_key in (SELECT filter_key FROM stream_filter WHERE uid = $target AND type = 'application')",'access_token' => $access_token,
	    ));
	    break;
	case "newsfeed":
	default:
	    $fbhome = $facebook->api(array('method'=>'fql.query','query'=> "SELECT post_id,viewer_id,source_id ,created_time,attachment,likes,comments,actor_id, target_id, message FROM stream WHERE filter_key in (SELECT filter_key FROM stream_filter WHERE uid = $target AND type = 'newsfeed')",'access_token' => $access_token,
	    ));
	break;
      }

    return $fbhome;
}

/**
 * Retrieve a facebook user's home feed using graph api.For more the powerful fql use the function above
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewfeedgraph($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');


    if(!$params['limit']) {
	   $limit = 10;
    }
    else {
	   $limit = $params['limit'];
    }

    $attachment =  array(
	'access_token' => $access_token,
	'limit' => $limit,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //you can also use the facebook uid($target) in the request in place of me

    $facebook = facebookservice_api();
    $fbhome=$facebook->api('/me/home', 'GET', $attachment);
	
    return $fbhome;
}


/**
 * Retrieve facebook comments.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewcomment($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //the post id.shouldn't be mistaken with the user's id
    $id = $params['id'];

    $facebook = facebookservice_api();
    $fbcomments=$facebook->api('/' .$id . '/comments', 'GET', $attachment);

    return $fbcomments;
}

/**
 * Post comment to facebook.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_postcomment($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');


    $attachment =  array(
	'access_token' => $access_token,
	'message' => $params['message'],
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //the post id.shouldn't be mistaken with the user's id
    $id = $params['id'];

    $facebook = facebookservice_api();
    $returncomments=$facebook->api('/' .$id . '/comments', 'POST', $attachment);

    return $returncomments;
}

/**
 * Retrieve a post likes on facebook.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewlike($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //the post id or comment id.shouldn't be mistaken with the user's id
    $id = $params['id'];

    $facebook = facebookservice_api();
    $fblikes=$facebook->api('/' .$id . '/likes', 'GET', $attachment);
    
    return $fblikes;
}

/**
 * Like a post on facebook.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_postlike($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //the post id or comment id.shouldn't be mistaken with the user's id
    $id = $params['id'];

    $facebook = facebookservice_api();
    $returnlikes=$facebook->api('/' .$id . '/likes', 'POST', $attachment);

    return $returnlikes;
}

/**
 * Retrieve a facebook user's username.
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_viewusername($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    //the id of the facebook user's whose user name you want to retrieve
    $id = $params['id'];

    $facebook = facebookservice_api();
    $fbuser = $facebook->api(array('method'=>'fql.query','query'=> "SELECT name FROM profile WHERE id = $id",'access_token' => $access_token,
    ));

    return $fbuser;
}

/**
 * Retrieve a user's friendrequest on facebook.There isn't any graph api endpoint to retrieve a users friend request at the time of writing this code
 *
 * @param unknown_type $hook
 * @param unknown_type $entity_type
 * @param unknown_type $returnvalue
 * @param unknown_type $params
 */

function facebookservice_friendrequest($hook, $entity_type, $returnvalue, $params) {

    $user_id = $params['userid'];
    $access_token = elgg_get_plugin_user_setting('access_token', $user_id, 'facebook_api');
    $target = elgg_get_plugin_user_setting('uid', $user_id, 'facebook_api');

    $attachment =  array(
	'access_token' => $access_token,
    );

    if (!($access_token && $target)) {
	return NULL;
    }

    $facebook = facebookservice_api();
    $fbrequest = $facebook->api(array(	'method'=>'fql.query','query'=> "SELECT uid_from FROM friend_request WHERE uid_to=$target ",'access_token' => $access_token,
    ));

    return $fbrequest;
}