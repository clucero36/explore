
<?php
// php script to be run on local machine
// 1) make sure folder is in /xammp/htdocs
// 2) make sure csv file is in /xammp/htdocs
// 3) manual entry csv file ---> fopen("<relative csv path>", "r")
// 4) manual entry second argument for getDistance(). The nutrishop address.
// 5) manual enter conditional in 
          // else
            // for
              // if ($c == 0) <--- column of address needed from csv
// 6) start apache server and run http://localhost:80/distance.php

$row = 1; // current row
$c = 12;  // csv address column
if (($handle = fopen("sales_report.csv", "r")) !== FALSE) {
  $fp = fopen('output.csv', 'w');
  while (($data = fgetcsv($handle)) !== FALSE) {
    if ($row == 1) {
      fputcsv($fp, $data);
    }
    else {
      $address_array =  explode("\n", $data[$c]);   // parse address column by new line into an indexed array
      $address_array_count = count($address_array); // number of entries in the address array
      $address_string = $address_array[1] . " " . $address_array[2] . " " . $address_array[3];

      // parse address string and pass as an argument to getDistance()
      $address_string = preg_replace('/(# \d*)\s/', "", $address_string);
      $address_string = preg_replace('/(#\d*)/', "", $address_string);
      $address_string = preg_replace('/(Apt)/', "", $address_string);
      $distance = getDistance($address_string, '690-5 Yamato Rd, Boca Raton, FL 33431');

      // if the distance fits our criteria, output value to new CSV file
      if ($distance < 30 && $distance > 7.5) {
        fputcsv($fp, $data);            
      }
    };
    $row++;
  };
  fclose($handle);
  fclose($fp);
};

// getDistance() 
// parameters:
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
<?php echo '<p>Working on producing a csv file in the project\'s directory</p>'; ?>
</body>
</html>