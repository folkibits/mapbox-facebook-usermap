<?php
include_once('mapWriter.php');

$args = array(
        'appID' => '', //APP ID
        'appSecret' => '', //APP SECRET
        'appScope' => array("scope" => "user_groups,user_location"), //SCOPE
        'groupID' => '', // facebook groupID (will work without group id)
        'groupURL' => '', //Group url (need url)
        'groupName' => '', //Group name (need name)
        'logoURL' => '', //Logo url 
        'dbTable' => 'usermap_facebookgroup', //database table name
        'mapboxMapId' => '' //mapbox.com mapp id  structure: <username>.randomletters

);

mapWriter::SETUP($args);

$mw = new mapWriter;

?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <link rel="shortcut icon" href="/assets/favicon.ico">

    <title>Facebook Map - <?php $mw->showGroupName();?></title>

    <!-- Bootstrap core CSS -->
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <!-- Custom styles for this template -->
    <link href="assets/css/custom.css" rel="stylesheet">
  <link href='https://api.tiles.mapbox.com/mapbox.js/v1.6.3/mapbox.css' rel='stylesheet' />
    <script src='https://api.tiles.mapbox.com/mapbox.js/v1.6.3/mapbox.js'></script>

    <!-- Just for debugging purposes. Don't actually copy this line! -->
    <!--[if lt IE 9]><script src="../../assets/js/ie8-responsive-file-warning.js"></script><![endif]-->

    <!-- HTML5 shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
      <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>

  <body>

    <div class="navbar navbar-inverse navbar-fixed-top" role="navigation">
      <div class="container">
        <div class="navbar-header">
      
          <a class="navbar-brand" href="<?php echo $mw->url();?>">Facebook Map - <?php $mw->showGroupName();?></a>
        </div>
        
    <div class="btn-group btn-toggle pull-right toggle-button"> 
    <button class="btn btn-primary" data-toggle="collapse" data-target="#collapsible"><span class="glyphicon glyphicon-map-marker"></span>Pin Map</button>
    
  </div>
  
      <div class="pull-right toggle-button"><?php $mw->userLogin(); ?></div>
      <div class="top-info pull-right">
        
        <?php if($mw->userLogin(true)){
            

            echo '<p class="navbar-text">Logged in as <b>'. $mw->FB_UserProfile('name').'</b></p>';
            
            }
            ?>
       </div>
        </div><!--/.nav-collapse -->
          <div class="collapse text-color" id="collapsible">
          <div class="container">
        <div class="row">
                <?php if($mw->FB_GroupVerify()){
            if($mw->checkUserData($mw->userLogin(true))){
                echo '<p class="alert alert-info">You are already on this map!</p>';
            }else{
            echo '<div class="col-md-4">';
            echo '<div class="pull-left">';
            echo "<b>Name:</b> " .  $mw->FB_UserProfile('name');
            echo "<br><b>Link:</b> " .  $mw->FB_UserProfile('link');
            echo "<br><b>Location:</b> " .  $mw->FB_UserProfile('location','name');
            echo '<br>';
            echo '<form method="POST" role="form">';
	      echo '<div class="input-group">';
           echo '<button name="submit" class="btn btn-primary">';
           echo '<span class="glyphicon glyphicon-map-marker btn-lg"></span>Pin Map Now';
           echo '</button>';
       echo '</div>';
         echo '</form>';
            echo '</div></div>';
            echo '<div class="col-md-4">';
            echo '<div class="well">';
            echo 'By pressing <b>Pin Map Now</b>, you agree to us saving your first name, facebook id, facebook url and current location.';
            echo '</div>';    
            echo '</div>';

            
            if(isset($_POST['submit'])){
                echo "HURRAY";
                $ins_lat = $mw->getCordinates('lat',$mw->FB_UserProfile('location','name'));
                $ins_lon = $mw->getCordinates('lon',$mw->FB_UserProfile('location','name'));

                $ins_name = $mw->FB_UserProfile('name');
                $ins_fb_id = $mw->FB_UserProfile('id');
                $ins_link = $mw->FB_UserProfile('link');
                $ins_location = $mw->FB_UserProfile('location','name');
                if($mw->userLogin(true)){
                mapWriter::insertUser($ins_name,$ins_fb_id,$ins_link,$ins_location,$ins_lat,$ins_lon);
                }
            }

            }
            
         }else{
            $mw->FB_GroupVerifyError();
            }
            ?>
    </div>
      </div>
      </div>
    </div>
    </div>

  
    <div class="container-fluid">

<div id="map" class="row"></div>

      </div>

  <script>
   var map = L.mapbox.map('map', '<?php $mw->showMapboxID(); ?>', {
  zoomControl: false,
  minZoom: 2,
  worldCopyJump: true
});
  new L.Control.Zoom({ position: 'bottomleft' }).addTo(map);<?php

      foreach($mw->getMapData() as $key => $value){
         $address = $key;
         $geo = $value['geo'];
        ?> L.mapbox.featureLayer({ type: 'Feature', geometry: { type: 'Point', coordinates: [ <?php echo $geo; ?> ] }, properties: {  title: '<?php echo $key; ?>',description: '<?php
        foreach($value as $row => $value2){
            
            if($row == 'users'){
        
            foreach ($value2 as $id => $type){
                ?><?php if($id > 0){ echo " - "; } ?><a target="_blank" href="<?php echo $type['link']; ?>" data-toggle="modal"><?php echo $type['name']; ?></a>&nbsp;<?php
            }
            }
        
        }
    ?>','marker-size': 'small','marker-color': '#693065' }}).addTo(map);<?php
      }
    ?></script>
    <!-- Bootstrap core JavaScript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
    <script src="assets/js/bootstrap.min.js"></script>
  </body>
</html>
