<?php
ini_set('implicit_flush',1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$now = date("Y-m-dTh:m:s");
$url = 'https://data.lter-europe.net/deims/data_product/harvesting_json';
$url2 = 'http://klimeto.com/projects/2017/uba/app/lib/productjson2isoxml.php';
//$xml = simplexml_load_file($url) or die("feed not loading");
$json = file_get_contents($url);
$arr = json_decode($json, true);
//print_r($arr['nodes']);
//$zwischen_var = $arr['nodes']['data_product'];
echo("<h2>PRODUCT2ISO START ON ".date("h:i:s")."</h2>");
echo("<p>RECORDS TO BE PROCESSED: " . count($arr['nodes']) . "</p>");
echo("<ol>");
//count($zwischen_var)

foreach($arr['nodes'] as $key => $value){
	$mdURL = $value['data_product']['url'];
	$mdTitle = $value['data_product']['title'];
	$mdDate = $value['data_product']['updated_date'];
	$getJSON = file_get_contents($mdURL);
	$mdJSON = json_decode($getJSON);
	$mdUUID = $mdJSON->{'nodes'}[0]->{'node'}->{'uuid'};
	echo("<li>");
	echo("EXTRACT FROM: ". $mdURL."<br>");
	// get current directory
	$file_name = __DIR__ . "/data/product2gmd_".$mdUUID.".xml";
	$product2iso_xml_file = file_get_contents($url2 . '?url=' . $mdURL);
	echo("TRANSFORM WITH: " . $url2 . "?url=" . $mdURL."<br>");
	file_put_contents($file_name, $product2iso_xml_file);
	echo("LOAD TO: ".$file_name ."<br>");
	echo("</li>");
	ob_flush();
	flush();
	sleep(2);
}

echo("</ol>");
echo("<h2>PRODUCT2ISO END ON ".date("h:i:s")."</h2>");
?>