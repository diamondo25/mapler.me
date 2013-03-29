<?php

require_once 'twitter.class.php';

//Required and meant to be private (our Mapler.Me's application keys)
$consumerKey = 'AeH4Ka2jIhiBWASIQUEQ';
$consumerSecret = 'RjHPE4FXqsznLGohdHzSDnOeIuEucnQ6fPc0aNq8sw';

//The @MaplerMe access token. To allow this to work for others,
//we'll need to retrieve their accessToken by requesting it from
//Twitter (which I can't figure out as there isn't any updated scripts).
$accessToken = '1096236908-Rj8JKo6ruO6AQnVS2U0rEZW60AsImyrQI3LzMV2';
$accessTokenSecret = 'BiqSjJ9m27MmEEZmB3bTM958X5In3F32bsD0pTe1e0';

$twitter = new Twitter($consumerKey, $consumerSecret, $accessToken, $accessTokenSecret);

try {
	//will send this tweet to @maplerme, so be aware.
	$tweet = $twitter->send('Testing statuses sent from @maplerme!');

} catch (TwitterException $e) {
	//gives error.
	echo 'Error: ' . $e->getMessage();
}

?>