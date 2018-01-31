<?php
include_once 'lib/productjson2isoxml.php';
$url = 'https://data.lter-europe.net/deims/data_product/harvesting_json';
$json = file_get_contents($url) or exit("Can't connect to harvest list");

$arr = json_decode($json, true);
$start = microtime(true);

echo("RECORDS TO BE PROCESSED: " . count($arr['nodes']));
echo "\r\n";
echo "PROCESSING ... \r\n";

// empty folder before new files are put there; only executed when harvest list was successfully loaded
$files = glob(__DIR__ . "/data//product2iso/*"); // get all file names
foreach($files as $file){ // iterate files
  if(is_file($file))
    unlink($file); // delete file
}

foreach($arr['nodes'] as $key => $value){
	$mdURL = $value['data_product']['url'];
	$mdTitle = $value['data_product']['title'];
	$mdDate = $value['data_product']['updated_date'];
	$getJSON = file_get_contents($mdURL);
	$mdJSON = json_decode($getJSON);
	$mdUUID = $mdJSON->{'nodes'}[0]->{'node'}->{'uuid'};
	
	$file_name = __DIR__ . "/data/product2iso/product2gmd_".$mdUUID.".xml";

	$product2iso_xml_file = productJson2isoXml($mdURL);
	if(empty($product2iso_xml_file)){
		echo("The conversion process failed for record: " . $mdUUID . " URL: " . $mdURL ."\r\n");
	}
	else{
		//file_put_contents($file_name, $product2iso_xml_file);
		$product2iso_xml_file->save($file_name);
	}

}
$end = microtime(true);
echo "\r\n";
echo "\r\n";
echo("PRODUCT2ISO END ON ".$end);
echo "\r\n";
echo("*** PROCESS DURATION: " . round(($end - $start),2) . "s");
echo "\r\n";
?>
