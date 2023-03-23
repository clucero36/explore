
<?php
// php file to be run on local machine
// 1) make sure folder is in /xammp/htdocs
// 2) make sure csv file is in /xamp/htdocs
// 3) manual entry csv file ---> fopen("<relative csv path>", "r")
// 4) manual entry second argument for getDistance(). The nutrishop address.
// 5) manual enter conditional in 
          // else
            // for
              // if ($c == 0) <--- column of address needed from csv
// 6) start apache server and run http://localhost:80/distance.php

$row = 1;
if (($handle = fopen("Addresses.csv", "r")) !== FALSE) {
  while (($data = fgetcsv($handle)) !== FALSE) {
    // number of items in row of cvs
    $num = count($data);
    // print labels (first row of csv)
    if ($row == 1) {
      for ($c=0; $c < $num; $c++) {
        echo "$data[$c] "; 
      }
    }
    // if we're not on the row of labels
    else {
      for ($c=0; $c <= $num; $c++) {
        if ($c == 0) {
          $distance = getDistance($data[$c], '5310 E Big Sky Ln, Anaheim, CA, 92807');
          echo " <p> Distance between $data[$c] ------ 5310 E Big Sky Ln: </p>";
          echo "<p>$distance</p>";
        }
      }
    } 
    $row++;
  };
  fclose($handle);
};

// getDistance() 
// args:
//    $addressFrom: nutrishop buyer location
//    $addressTo: nutrishop store location
//    $unit: 'km' 'm' 'miles'
// returns:
//    distance between addressFrom & addressTo
// -------------------------------------------------------------
function getDistance($addressFrom, $addressTo, $unit = ''){
  // Google API key
  $apiKey = 'AIzaSyCVvSVVSL4IH4jMZtU5YHLP5konxnYlV0c';
  // Change address format
  $formattedAddrFrom    = str_replace(' ', '+', $addressFrom);
  $formattedAddrTo     = str_replace(' ', '+', $addressTo);
  // Geocoding API request with start address
  $geocodeFrom = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrFrom.'&sensor=false&key='.$apiKey);
  $outputFrom = json_decode($geocodeFrom);
  if(!empty($outputFrom->error_message)){
      return $outputFrom->error_message;
  }
  
  // Geocoding API request with end address
  $geocodeTo = file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$formattedAddrTo.'&sensor=false&key='.$apiKey);
  $outputTo = json_decode($geocodeTo);
  if(!empty($outputTo->error_message)){
      return $outputTo->error_message;
  }
  
  // Get latitude and longitude from the geodata
  $latitudeFrom    = $outputFrom->results[0]->geometry->location->lat;
  $longitudeFrom    = $outputFrom->results[0]->geometry->location->lng;
  $latitudeTo        = $outputTo->results[0]->geometry->location->lat;
  $longitudeTo    = $outputTo->results[0]->geometry->location->lng;
  
  // Calculate distance between latitude and longitude
  $theta    = $longitudeFrom - $longitudeTo;
  $dist    = sin(deg2rad($latitudeFrom)) * sin(deg2rad($latitudeTo)) +  cos(deg2rad($latitudeFrom)) * cos(deg2rad($latitudeTo)) * cos(deg2rad($theta));
  $dist    = acos($dist);
  $dist    = rad2deg($dist);
  $miles    = $dist * 60 * 1.1515;
  
  // Convert unit and return distance
  $unit = strtoupper($unit);
  if($unit == "K"){
      return round($miles * 1.609344, 2).' km';
  }elseif($unit == "M"){
      return round($miles * 1609.344, 2).' meters';
  }else{
      return round($miles, 2).' miles';
  }
}
?>

<html>
<head>
<title>PHP Test</title>
</head>
<body>
<?php echo '<p>Hello World</p>'; ?>
</body>
</html>