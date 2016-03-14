<?php
class Mongo
{
	protected $db;
	protected $config;
	protected $query;
	protected $dbName;
	
	public function __construct($connection, $config = array())
	{
		$this->dbName = $connection['database'];
		$con_str = 'mongodb://'.$connection['host'].':'.$connection['port'].'/'.$this->dbName;
		$this->db = new MongoDB\Driver\Manager($con_str);
		$this->config = array_merge(array(
			'client_table' => 'oauth_clients',
			'access_token_table' => 'oauth_access_tokens',
			'refresh_token_table' => 'oauth_refresh_tokens',
			'code_table' => 'oauth_authoriztion_code',
			'user_table' => 'oauth_users',
			'jwt_table' => 'oauth_jwt',
		), $config);
	}
	
	public function checkClientCredentials($client_id,$client_secret = null)
	{
		$filter = array('client_id' => $client_id);
		$query = new MongoDB\Driver\Query($filter);
		if($result = $this->db->executeQuery($this->dbName.'.oauth_clients', $query)){ 
			$result = $result->toArray();
			return $result[0]->client_secret == $client_secret;
		}
		return false;
	}
	
	public function isPubliClient($client_id)
	{
		$filter = array("client_id"=>$client_id);
		$query = new MongoDB\Driver\Query($filter);
		if(!$result = $this->db->executeQuery($this->dbName.'.oauth_clients',$query)){
			return false;
		}
		$result = $result->toArray();
		return empty($result[0]->client_secret);
	}
	
	public function getClientDetails($client_id)
	{
		$filter = array("client_id"=>$client_id);
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_clients',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0];
	} 
	
	public function setClientDetails($client_id,$client_secret=null,$redirect_uri=null,$grant_types = null,$scope=null,$user_id = null)
	{
		if($this->getClientDetails($client_id)){
			$bulk = new MongoDB\Driver\BulkWrite;
			$bulk->update(
				 array('client_id' => $client_id),
				 array('$set' => array(
                    'client_secret' => $client_secret,
                    'redirect_uri'  => $redirect_uri,
                    'grant_types'   => $grant_types,
                    'scope'         => $scope,
                    'user_id'       => $user_id,
                ))
			);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_clients', $bulk, $writeConcern);						
		}
		else {
			$bulk = new MongoDB\Driver\BulkWrite;
			$client = array(
				'client_id' => $client_id,
				'client_secret' => $client_secret,
				'redirect_uri' => $redirect_uri,
				'grant_types' => $grant_types,
				'scope' => $scope,
				'user_id' => $user_id,
			);
			$bulk->insert($client);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_clients', $bulk, 	$writeConcern);

		}
		return true;
	}
	
	public  function checkRestrictedGrantType($client_id,$grant_type)
	{
		$details = $this->getClientDetails($client_id);
		if(isset($details->grant_types)) {
			$grant_types = explode(' ',$details->grant_types);
			return in_array($grant_type,$grant_types);
		}
		return true;
	}
	
	public function getAccessToken($access_token)
	{
		$filter = array("access_token"=>$access_token);
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_access_tokens',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0];
	}
	
	public function setAccessToken($access_token,$client_id,$user_id,$expires,$scope = null)
	{	
		if($this->getAccessToken($access_token)){
			$bulk = new MongoDB\Driver\BulkWrite;
			$bulk->update(
				 array('access_token' => $access_token),
				 array('$set' => array(
                    'client_id' => $client_id,
                    'expires' => $expires,
                    'user_id' => $user_id,
                    'scope' => $scope
                ))
			);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_access_tokens', $bulk, $writeConcern);
		} 
		else {
			$bulk = new MongoDB\Driver\BulkWrite;
			$token = array(
				'access_token' => $access_token,
				'client_id' => $client_id,
				'expires' => $expires,
				'user_id' => $user_id,
				'scope' => $scope
			);
			$bulk->insert($token);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_access_tokens', $bulk, 	$writeConcern);
		}
		return true;
	}
	
	public function unsetAccessToken($access_token)
	{
		$bulk = new MongoDB\Driver\BulkWrite;
		$bulk->delete(array('access_token'=>$access_token));
		
		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = $this->db->executeBulkWrite($this->dbName.'.oauth_access_tokens', $bulk, $writeConcern);
	}
	
	public function getAuthorizationCode($code)
	{
		$filter = array("authorization_code"=>$code);
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_authorization_codes',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0];
	}
	
	public function setAuthorizationCode($code,$client_id,$user_id,$redirect_uri,$expires,$scope=null,$id_token=null)
	{
		if($this->getAuthorizationCode($code)){
			$bulk = new MongoDB\Driver\BulkWrite;
			$bulk->update(
				 array('authorization_code' => $code),
                 array('$set' => array(
                    'client_id' => $client_id,
                    'user_id' => $user_id,
                    'redirect_uri' => $redirect_uri,
                    'expires' => $expires,
                    'scope' => $scope,
                    'id_token' => $id_token,
                ))
			);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_authorization_codes', $bulk, $writeConcern);
		}
		else {
			$bulk = new MongoDB\Driver\BulkWrite;
			$token = array(
                'authorization_code' => $code,
                'client_id' => $client_id,
                'user_id' => $user_id,
                'redirect_uri' => $redirect_uri,
                'expires' => $expires,
                'scope' => $scope,
                'id_token' => $id_token,
            );
			$bulk->insert($token);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_authorization_codes', $bulk, $writeConcern);
		}
		return true;
	}
	
	public function expireAuthorizationCode($code)
	{
		$bulk = new MongoDB\Driver\BulkWrite;
		$bulk->delete(array('authorization_code'=>$code));
		
		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = $this->db->executeBulkWrite($this->dbName.'.oauth_authorization_codes', $bulk, $writeConcern);
	}
	
	public function checkUserCredentials($username,$password) 
	{
		if($user = $this->getUser($username)) {
			return $this->checkPassword($user,$password);
		}
		return false;
	}
	
	public function getUserDetails($username) 
	{
		if($user = $this->getUser($username)){
			$user->user_id = $user->username;
		}
		return $user;
	}
	
	public function getRefreshToken($refresh_token)
	{
		$filter = array('refresh_token' => $refresh_token);
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_refresh_tokens',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0];
	}
	
	public function setRefreshToken($refresh_token,$client_id,$user_id,$expires,$scope=null)
	{
		$bulk = new MongoDB\Driver\BulkWrite;
		$token = array(
            'refresh_token' => $refresh_token,
            'client_id' => $client_id,
            'user_id' => $user_id,
            'expires' => $expires,
            'scope' => $scope
        );
		$bulk->insert($token);
		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = $this->db->executeBulkWrite($this->dbName.'.oauth_refresh_tokens', $bulk, $writeConcern);
		return true;
	}
	
	public function unsetRefreshToken($refresh_token)
	{
		$bulk = new MongoDB\Driver\BulkWrite;
		$bulk->delete(array('refresh_token' => $refresh_token));
		
		$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
		$result = $this->db->executeBulkWrite($this->dbName.'.oauth_refresh_tokens', $bulk, $writeConcern);
	}
	
	protected function checkPassword($user,$password)
	{
		return $user->password == $password;
	}		
	
	public function getUser($username)
	{
		$filter = array('username' => $username);
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_users',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0];
	}
	
	public function setUser($username,$password,$firstName=null,$lastName=null)
	{
		if($this->getUser($username)){
			$bulk = new MongoDB\Driver\BulkWrite;
			$bulk->update(
				 array('username' => $username),
                array('$set' => array(
                    'password' => $password,
                    'first_name' => $firstName,
                    'last_name' => $lastName
                ))
			);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_users', $bulk, $writeConcern);
		}
		else{
			$bulk = new MongoDB\Driver\BulkWrite;
			$token = array(
                'username' => $username,
                'password' => $password,
                'first_name' => $firstName,
                'last_name' => $lastName
            );
			$bulk->insert($token);
			$writeConcern = new MongoDB\Driver\WriteConcern(MongoDB\Driver\WriteConcern::MAJORITY, 1000);
			$result = $this->db->executeBulkWrite($this->dbName.'.oauth_users', $bulk, $writeConcern);
		}
		return true;
	}
	
	public function getClientKey($client_id,$subject)
	{
		$filter = array(
            'client_id' => $client_id,
            'subject' => $subject
        );
		$query = new MongoDB\Driver\Query($filter);
		$result = $this->db->executeQuery($this->dbName.'.oauth_jwt',$query);
		$result = $result->toArray();
		
		return $result == null ? false : $result[0]->public_key;
	}
	
	public function getClientScope($client_id)
	{
		 if (!$clientDetails = $this->getClientDetails($client_id)) {
            return false;
        }

        if (isset($clientDetails->scope)) {
            return $clientDetails->scope;
        }

        return null;
	}	
		
	public function getJti($client_id,$subject,$audience,$expiration,$jti)
	{ 	
		//need to be implemented
		return false;
	}
	
	public function setJti($client_id, $subject, $audience, $expiration, $jti)
	{
		//need to be implemented
		return false;
	}
	
}
?> 