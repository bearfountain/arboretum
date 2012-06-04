<?php
$dbconn = pg_connect("host=localhost dbname=arboretum user=postgres password=19alligators") or die('could not connect');

$query = pg_query("SELECT * FROM trees");

?>

<!DOCTYPE html> 
<html> 
	<head> 
		<title>Roemer Arboretum</title> 
    <meta http-equiv="Content-type" content="text/html; charset=utf-8" /> 

    <link rel="stylesheet" href="css/styles.css" type="text/css" /> 
    <link rel="stylesheet" href="css/ui-lightness/jquery-ui-1.8.20.custom.css" type="text/css" /> 

		<script type='text/javascript' src='https://ajax.googleapis.com/ajax/libs/jquery/1.7.2/jquery.min.js'></script>
		<script type='text/javascript' src='js/jquery-ui-1.8.20.custom.min.js'></script>
    <script type="text/javascript" src="http://maps.googleapis.com/maps/api/js?key=AIzaSyC28tHjBUO3IVN8onu8XD8v4IxVeGDRhv0&sensor=false"></script>

    <style type='text/css'>

    #map {
      width: 880px;
      height: 500px;
    }

    label {
      display: block;
    }

    input {
      border: 1px solid #ccc;
      padding: 5px;
      font-size: 14pt;
    }

    .ui-autocomplete {
      height: 200px;
      overflow-y: scroll;
      overflow-x: hidden;
    }

    .viewall {
      cursor: pointer;
    }

    #findatree {
      margin-top: 50px; 
      border: 1px solid #ccc;
      box-shadow: inset 1px 1px 1px white;
      padding: 10px;
    }

    input[type=button] {
      box-shadow: 0px 0px 3px black;
      border: 1px solid white;
      border-radius: 3px;
    } 

    </style>

    <script type='text/javascript'>

    $(document).ready(function() {
      var map = new google.maps.Map(document.getElementById('map'), {
        center: new google.maps.LatLng(42.789811, -77.823522),
        zoom: 17,
        mapTypeId: google.maps.MapTypeId.SATELLITE
      });

      var infowindow = new google.maps.InfoWindow({
        content: ''
      });

      var donors = [];
      var names = [];
      var markers = [];

      <?php while(null != ($tree = pg_fetch_assoc($query))): ?>

      <?php

      // Some of the names are in the format "Ash, Mountain" or the like. This checks to see
      // if that is the case, and will reorder them to read "Mountain Ash"

      $tree_name_components = explode(',', $tree['name']);
      
      if(count($tree_name_components) == 1) {
        $name = $tree['name'];
      }
      else {
        $name = $tree_name_components[1] . ' ' . $tree_name_components[0];
        $name = trim($name);
      }

      ?>

      // Add the donor and name to the master arrays that are used for autocompletion
      if(-1 == jQuery.inArray("<?php echo addslashes($tree['donor']) ?>", donors)) {
        donors.push('<?php echo $tree['donor'] ?>');
      }
      
      if(-1 == jQuery.inArray("<?php echo addslashes($name) ?>", names)) {
        names.push("<?php echo $name ?>");
      }
      
      var marker<?php echo $tree['id'] ?> = new google.maps.Marker({
        map: map,
        position: new google.maps.LatLng(<?php echo $tree['lat'] ?>, <?php echo $tree['long'] ?>),
        title: '<?php echo $name ?>'
      });

      marker<?php echo $tree['id'] ?>.donor = '<?php echo $tree['donor'] ?>';

      markers.push(marker<?php echo $tree['id'] ?>);

      google.maps.event.addListener(marker<?php echo $tree['id'] ?>, 'click', function() {
        infowindow.setContent('<h1><?php echo $name ?></h1>'
          <?php if($tree['donor']): ?>
          + '<p>Donated by <?php echo $tree['donor'] ?></p>'
          <?php endif; ?>
          );
        infowindow.open(map, marker<?php echo $tree['id'] ?>);
      });

      <?php endwhile; ?>


      donors.sort();
      names.sort();


      $(".treedonor").autocomplete({
        source: donors,
        minLength: 0,
        select: function(event, ui) {
          filterByDonor(ui.item.value);
        }
      }).keyup(function(e) {
        filterByDonor($(".treedonor").val());
      }).click(function() {
        $(".treedonor").autocomplete("search", "");
      });



      $(".treename").autocomplete({
        source: names,
        minLength: 0,
        select: function(event, ui) {
          filterByName(ui.item.value);
        }
      }).keyup(function(e) {
        filterByName($(".treename").val());
      }).click(function() {
        $(".treename").autocomplete("search", "");
      });


      $(".viewall").click(function() {
        $(".treename").val('');
        $(".treedonor").val('');

        showAllMarkers();
      });


      function showAllMarkers() {
        $.each(markers, function(index, marker) {
          marker.setMap(map);
        });
      }


      function filterByName(name) {
        name = name.toLowerCase();
        $.each(markers, function(index, marker) {
          if(-1 == marker.title.toLowerCase().indexOf(name)) {
            marker.setMap(null);
          }
          else {
            marker.setMap(map);
          }
        });
      }

      function filterByDonor(donor) {
        donor = donor.toLowerCase();
        $.each(markers, function(index, marker) {
          if(-1 == marker.donor.toLowerCase().indexOf(donor)) {
            marker.setMap(null);
          }
          else {
            marker.setMap(map);
          }
        });
      }
    });

    </script>

    <!--[if lt IE 9]>
    <script src="//html5shiv.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->
  </head>
  <body>
    <div id='wrapper'>
      <header>
        <img src="images/logo-horizontal.png" alt="Spencer J. Roemer Arboretum" id="logo"/>
        <nav id="nav1" class="">
          <ul>
            <li class="current"><a href="index.html">Home</a></li><!--
            --><li><a href="news.html">News & Events</a></li><!--
            --><li><a href="education.html">Research & Education</a></li><!--
            --><li><a href="history.html">History</a></li><!--
            --><li><a href="mission.html">Mission</a></li><!-- 
            --><li><a href="giving.html">Giving</a></li>
          </ul>
        </nav>
      </header>

      <div id="content" class="home">
       
        <div id="content_wrapper">
          <div id='map'></div>

          <div id='findatree'>
            <h1>Find a Tree</h1>
            <p>
              <label>By tree name:</label> <input type='text' class='treename' />
            </p>

            <p>
              <label>By donor:</label> <input type='text' class='treedonor' />
            </p>

            <p><input type='button' value='View All' class='viewall' /></p>
          </div>


        </div>
      </div>
      <footer>
        © 2012 Spencer J. Roemer Arboretum | State University of New York at Geneseo
      </footer>
    </div>
  </body>
</html>
