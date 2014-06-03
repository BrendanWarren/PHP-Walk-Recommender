<?php

	//Application Configurations
	$app_id		= "245117432302304";
	$app_secret	= "";
	$site_url	= "http://brenz.co.uk/uni/php/recommender/";
	 
	try{
		include_once "src/facebook.php";
	}catch(Exception $e){
		error_log($e);
	}
	// Create our application instance
	$facebook = new Facebook(array(
		'appId'		=> $app_id,
		'secret'	=> $app_secret,
		));

	// Get the current access token
	global $access_token;
	$access_token = $facebook->getAccessToken();
	global $app_access_token;

			$url = 'https://graph.facebook.com/oauth/access_token?client_id='.$app_id.'&client_secret='.$app_secret.'&grant_type=client_credentials';
	        //$myvars = 'access_token=' . $access_token . '&template=This is a test notification&href=settings.php';

	       	$ch = curl_init( $url );
	        curl_setopt( $ch, CURLOPT_POST, 1);
	        curl_setopt( $ch, CURLOPT_POSTFIELDS, $myvars);
	        curl_setopt( $ch, CURLOPT_FOLLOWLOCATION, 1);
	        curl_setopt( $ch, CURLOPT_HEADER, 0);
	        curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1);

	        $response = curl_exec( $ch );

	        //$app_access_token = $response;

	        parse_str($response, $response_arr);
			//var_dump($response_arr);
			$app_access_token = $response_arr['access_token'];
			//echo $app_access_token;

	 
	// Get User ID
	$user = $facebook->getUser();
	// We may or may not have this data based
	// on whether the user is logged in.
	// If we have a $user id here, it means we know
	// the user is logged into
	// Facebook, but we don’t know if the access token is valid. An access
	// token is invalid if the user logged out of Facebook.
	 
	if($user){
	//==================== Single query method ======================================
		try{
			// Proceed knowing you have a logged in user who's authenticated.
			$user_profile = $facebook->api('/me');
		}catch(FacebookApiException $e){
			error_log($e);
			$user = NULL;
		}
	//==================== Single query method ends =================================
	}
	 
	if($user){
		// Get logout URL
		$logoutUrl = $facebook->getLogoutUrl(array( 'next' => ('http://brenz.co.uk/uni/php/recommender/logout.php') ));
		$logoutUrl = str_replace('&', '&amp;', $logoutUrl);
	}else{
		// Get login URL
		$loginUrl = $facebook->getLoginUrl(array(
			'scope'		=> 'user_status',
			'redirect_uri'	=> $site_url."/",
			));
		$loginUrl = str_replace('&', '&amp;', $loginUrl);
	}
	 
	if($user){
		// Proceed knowing you have a logged in user who has a valid session.
	 
	//========= Batch requests over the Facebook Graph API using the PHP-SDK ========
		// Save your method calls into an array
		$queries = array(
			array('method' => 'GET', 'relative_url' => '/'.$user),
			);
	 
		// POST your queries to the batch endpoint on the graph.
		try{
			$batchResponse = $facebook->api('?batch='.json_encode($queries), 'POST');
		}catch(Exception $o){
			error_log($o);
		}
	 
		//Return values are indexed in order of the original array, content is in ['body'] as a JSON
		//string. Decode for use as a PHP array.
		$user_info		= json_decode($batchResponse[0]['body'], TRUE);
	//========= Batch requests over the Facebook Graph API using the PHP-SDK ends =====
	 
		// Update user's status using graph api
		if(isset($_POST['status'])){
			try{
				$statusUpdate = $facebook->api("/$user/feed", 'post', array('message'=> $_POST['status']));
			}catch(FacebookApiException $e){
				error_log($e);
			}
		}
	}


?>