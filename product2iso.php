<?php
include_once 'lib/productjson2isoxml.php';
$url = 'https://data.lter-europe.net/deims/data_product/harvesting_json';
$json = file_get_contents($url);
$arr = json_decode($json, true);
$start = microtime(true);
echo("PRODUCT2ISO START ON " . $start );
echo "\r\n";
echo("RECORDS TO BE PROCESSED: " . count($arr['nodes']));
echo "\r\n";
foreach($arr['nodes'] as $key => $value){
	$mdURL = $value['data_product']['url'];
	$mdTitle = $value['data_product']['title'];
	$mdDate = $value['data_product']['updated_date'];
	$getJSON = file_get_contents($mdURL);
	$mdJSON = json_decode($getJSON);
	$mdUUID = $mdJSON->{'nodes'}[0]->{'node'}->{'uuid'};
	echo("EXTRACT FROM: ". $mdURL);
	echo "\r\n";
	$file_name = __DIR__ . "/data/product2iso/product2gmd_".$mdUUID.".xml";
	//$product2iso_xml_file = productJson2isoXml("https://data.lter-europe.net/deims/node/10170/json");
	$product2iso_xml_file = productJson2isoXml($mdURL);
	if(empty($product2iso_xml_file)){
		echo("The conversion process failed for record: " . $mdUUID . " URL: " . $mdURL ."\r\n");
	}
	else{
		file_put_contents($file_name, $product2iso_xml_file);
		echo("LOADED TO: ".$file_name ."\r\n");
	}
	echo "\r\n";
}
$end = microtime(true);
echo "\r\n";
echo "\r\n";
echo("PRODUCT2ISO END ON ".$end);
echo "\r\n";
echo("PROCESS DURATION: " . round(($end - $start),2) . "s");
echo "\r\n";
?>