<?php
/*
  By Folkibits <https://gist.github.com/folkibits>.
  This code is released in the public domain under GPL 2.0.

  Requirements for this class is a dev account for Mapbox.com and an active map code & a facebook app.

  This class is used to showcase a worldmap where users can pin their current location pulled by facebook.

*/
require_once('APPdatabase.php');
require_once('facebook/facebook.php');




class mapWriter{

    
      public static $appID;
      public static $appSecret;
      public static $appScope;


      public static $groupID;
      public static $groupURL;
      public static $groupName;

      public static $logoURL;
      public static $dbTable;
      public static $mapboxMapId;

      
      private $facebook; 
      private $user_id;
      private $access_token;
      
  public function __construct(){
        
    if((self::$appID > 0) && (self::$appSecret > 0)){

         $config = array(
        'appId' => self::$appID,
        'secret' => self::$appSecret,
      'allowSignedRequest' => false // optional but should be set to false for non-canvas apps
    );
      
              $this->facebook = new Facebook($config);
    }else{
        echo '<p><b>ERROR:</b>Your appID or appSecret is not defined. Please definde for facebook\'s API to work.</p>';
        exit;
    }      
                     
 
        
    }
    
    public static function setup($setup_args) {
        
        self::$appID = $setup_args['appID'];
        self::$appSecret = $setup_args['appSecret'];
        self::$appScope = $setup_args['appScope'];
        self::$groupID = $setup_args['groupID'];
        self::$groupURL = $setup_args['groupURL'];
        self::$groupName = $setup_args['groupName'];
        self::$logoURL = $setup_args['logoURL'];;
        self::$dbTable = $setup_args['dbTable'];
        self::$mapboxMapId = $setup_args['mapboxMapId'];


    }
  

    public function userLogin($boleen = false){
        
        $this->user_id = $this->facebook->getUser();
        $this->access_token = $this->facebook->getAccessToken();
    
        if(!$boleen){
            
        
            if($this->user_id) {
                
                if(isset($_POST['logout'])){
                    $this->facebook->destroySession();
                    
                   //echo('<script> top.location.href = "";</script>');
        
                }
                
                // We have a user ID, so probably a logged in user.
                // If not, we'll get an exception, which we handle below.
            
                try {
                    $user_profile = $this->facebook->api('/me?fields=id,groups,first_name,link,location','GET');
                    $logout_url = $this->facebook->getLogoutUrl();
                     echo '<form method="post"><input type="submit" value="logout" name="logout" class="btn btn-primary"></form>';
        
                }  catch(Exception $e) {
                    // If the user is logged out, you can have a 
                  // user ID even though the access token is invalid.
                  // In this case, we'll get an exception, so we'll
                  // just ask the user to login again here.
                  $login_url = $this->facebook->getLoginUrl(self::$appScope); 
           echo '<a href="' . $login_url . '" class="btn btn-primary">Login with facebook.</a>';
                  error_log($e->getType());
                  error_log($e->getMessage());
                }   
              } else {
        
                // No user, print a link for the user to login
                $login_url = $this->facebook->getLoginUrl(self::$appScope);
           echo '<a href="' . $login_url . '" class="btn btn-primary">Login with facebook.</a>';
        
              }
        }else{
            
            return $this->user_id;
            
        } 
        
        
    }



    public function FB_UserProfile($string, $name = false){
    
        
        if($this->user_id){
            
            $user_profile = $this->facebook->api('/me?fields='.$string,'GET');
            
            if(!$name){
                return $user_profile[$string];   
            }else{
                return $user_profile[$string][$name];
            }
    
        
            
        }else{
          echo "<b>INFO:</b> You need to be logged in first";
             
        }
            
    }

  public function FB_GroupVerify(){
        if(self::$groupID > 0){
            if($this->user_id){
                
                $user_profile = $this->facebook->api('/me?fields=groups','GET');
                
              
                 foreach((array)$user_profile['groups']['data'] as $group){
                  if($group['id']== self::$groupID){ $fbVerify = true; }
                    }
                    
                  if($fbVerify){
                      $groupVerify = TRUE;
                     }else{
                    $groupVerify = FALSE;
                  }

            }else{  $groupVerify = false;    }
        }else{  $groupVerify = true;    }
        
        
        
        
        return $groupVerify;
        
    }
    
 public function FB_GroupVerifyError(){
     
  echo '<p class="navbar-text alert alert-info">I\'m sorry, but you need to be a part of our';
  echo '<a href="'. self::$groupURL.'" target="_BLANK" class="color-text"> ';
  echo self::$groupName.'</a> group on facebook to pin your location.</p>';
;
     
 }
   



     public function getUserData($id){
         
        $conn = dbConn::getConnection();
        $table = self::$dbTable;
        $sth=$conn->prepare("SELECT * from $table where fb_id = :fb_id"); 
        $sth->bindParam(":fb_id", $id);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        return $result;
    }

   public function CheckUserData($id){
         
        $conn = dbConn::getConnection();
        $table = self::$dbTable;
        $sth=$conn->prepare("SELECT fb_id from $table where fb_id = :fb_id"); 
        $sth->bindParam(":fb_id", $id);
        $sth->execute();
        $result = $sth->fetch(PDO::FETCH_ASSOC);
        if($result['fb_id'] > 0){
            return true;
        }
    }


     public static function insertUser($name,$fb_id,$link,$location,$lat,$lon){
         
        $conn = dbConn::getConnection();
        $table = self::$dbTable;
        $sth=$conn->prepare("INSERT INTO $table (name,fb_id,link,location,lat,lon ) values (:name, :fb_id,  :link,  :location, :lat,  :lon )"); 
        $sth->bindParam(":name", $name);
        $sth->bindParam(":fb_id", $fb_id);
        $sth->bindParam(":link", $link);
        $sth->bindParam(":location", $location);
        $sth->bindParam(":lat", $lat);
        $sth->bindParam(":lon", $lon);
        $sth->execute();
        
        if(!$sth){
            
            echo "<b>WARNING:</b> Something happened while trying to input your data. Please try again.";
            
        }
        
        
    
}

    public function getMapData(){
         
        $conn = dbConn::getConnection();
        $table = self::$dbTable;
        $mapArray = array();
        $sql = "SELECT name, link, location, lat, lon from $table"; 
        
       foreach ($conn->query($sql) as $row) {
              $location_temp = $row['location'];
    
         if(!in_array($location_temp, $mapArray,true)){
             
             $mapArray[$location_temp] = array('geo' => $row['lon'] .','.$row['lat']);
               }
    
          }
            
          foreach ($conn->query($sql) as $row) {
          $location_temp = $row['location'];
    
             $mapArray[$location_temp]['users'][] = array('name' => $row['name'], 'link' => $row['link']);
    
         }
           
           return $mapArray;
            
    }


    public function url(){
      return sprintf(
        "%s://%s%s",
        isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off' ? 'https' : 'http',
        $_SERVER['SERVER_NAME'],
        $_SERVER['REQUEST_URI']
      );
    }
    
    public function showGroupName(){
            
         echo self::$groupName;
        
    }

     public function showMapboxID(){
            
         echo self::$mapboxMapId;
        
    }
    public function getCordinates($loc,$place){
        
         $coordinates = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address=' . urlencode($place) . '&sensor=true');
        $coordinates = json_decode($coordinates);
          $lat  = $coordinates->results[0]->geometry->location->lat;
          $lon = $coordinates->results[0]->geometry->location->lng;
            
        if($loc === "lon"){
            $geo = $lon;
        }
        if($loc === "lat"){
            $geo = $lat;
        }
        return $geo;
    }


} //end class.mapWriter
?>
