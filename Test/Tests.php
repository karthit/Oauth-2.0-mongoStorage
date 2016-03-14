<?php 
include('oauth2-server-php/src/OAuth2/Storage/Mongo.php');

$connection = array(
	'host' => 'localhost',
	'port' => 27017,
	'database' => 'oauth'
);

$db = new MongoTest($connection);

$cursor = $db->checkClientCredentials('testclient','testpass');
var_dump($cursor);

echo '<br/>';

$cursor = $db->isPubliClient('testclient');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getClientDetails('testclient');
var_dump($cursor);

echo '<br/>';

$cursor = $db->setClientDetails('newclient','passtest','http://notfound.in');
var_dump($cursor);

echo '<br/>';

$cursor = $db->checkRestrictedGrantType('newclient',null);
var_dump($cursor);

echo '<br/>';

$cursor = $db->getAccessToken('token_unique');
var_dump($cursor);

echo '<br/>';

$cursor = $db->setAccessToken('token_clone','test','userid',date('Y-m-d'));
var_dump($cursor);

echo '<br/>';

$cursor = $db->unsetAccessToken('token_clone');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getAuthorizationCode('token_id');
var_dump($cursor);

echo '<br/>';

$cursor = $db->setAuthorizationCode('token_id','clients','user','http://not.in',date('Y-m-d'));
var_dump($cursor);

echo '<br/>';

$cursor = $db->expireAuthorizationCode('token_id');
var_dump($cursor);

echo '<br/>';

$cursor = $db->checkUserCredentials('karthi','test');
var_dump($cursor);

echo '<br/>';		

$cursor = $db->getUserDetails('karthi');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getRefreshToken('token_id');
var_dump($cursor);

echo '<br/>';

$cursor = $db->setRefreshToken('token_id','client','user',date('Y-m-d'));
var_dump($cursor);

echo '<br/>';

$cursor = $db->unsetRefreshToken('token_id');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getUser('karthi');
var_dump($cursor);

echo '<br/>';

$cursor = $db->setUser('karthi','updated');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getClientKey('karthi','updated');
var_dump($cursor);

echo '<br/>';

$cursor = $db->getClientScope('karthi');
var_dump($cursor);

echo '<br/>';




