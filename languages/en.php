<?php
/**
 * An english language definition file
 */

$english = array(
	'facebook_api' => 'Facebook Services',

	'facebook_api:requires_oauth' => 'Facebook Services requires the OAuth Libraries plugin to be enabled.',

	'facebook_api:consumer_key' => 'Application Key',
	'facebook_api:consumer_secret' => 'Application Secret',
        'facebook_api:message_string' => 'Enter a custom message you want to be posted on users wall when he/she registers on your site.Message will be displayed as username your message sitename',

	'facebook_api:settings:instructions' => 'You must obtain a client id and secret from <a href="http://www.facebook.com/developers/" target="_blank">Facebook</a>. Most of the fields are self explanatory, the one piece of data you will need is the callback url which takes the form http://[yoursite]/action/facebooklogin/return - [yoursite] is the url of your Elgg network.',

	'facebook_api:usersettings:description' => "Link your %s account with Facebook.",
	'facebook_api:usersettings:request' => "You must first <a href=\"%s\">authorize</a> %s to access your Facebook account.",
	'facebook_api:authorize:error' => 'Unable to authorize Facebook.',
	'facebook_api:authorize:success' => 'Facebook access has been authorized.',

	'facebook_api:usersettings:authorized' => "You have authorized %s to access your Facebook account: @%s.",
	'facebook_api:usersettings:revoke' => 'Click <a href="%s">here</a> to revoke access.',
	'facebook_api:revoke:success' => 'Facebook access has been revoked.',

	'facebook_api:login' => 'Allow existing users who have connected their Facebook account to sign in with Facebook?',
	'facebook_api:new_users' => 'Allow new users to sign up using their Facebook account even if manual registration is disabled?',
	'facebook_api:login:success' => 'You have been logged in.',
    	'facebook_api:registration:success' => 'You have successfully registered',
	'facebook_api:login:error' => 'Unable to login with Facebook.',
	'facebook_api:login:email' => "You must enter a valid email address for your new %s account.",
);

add_translation('en', $english);
