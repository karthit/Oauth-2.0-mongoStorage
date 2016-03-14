                                       Mongo Storage Class for Oauth2.0 (PHP 7.0.4) 
                                       
                                       
  Replace Mongo.php inside, 
  
  ..\oauth2-server-php\src\OAuth2\Storage 
  
  This provides updated connection string from  the  Mongo Legacy drivers. 
  
  Requirements:
     
     1. PHP 7.0.4
     2. Oauth 2.0 
     3. MongoDB driver greater than 1.0.0 

  Unit Test:
     
     include('oauth2-server-php/src/OAuth2/Storage/Mongo.php');
     $db = new MongoTest('test');
     $cursor = $db->getUserDetails('username');
     
Supporting Dlls and config file will be added soon.
