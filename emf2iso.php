<?php
include_once 'lib/emfjson2isoxml.php';
$url = 'https://data.lter-europe.net/deims/emf/harvest_list';
$xml = simplexml_load_file($url) or die("feed not loading");
$arr = json_decode(json_encode($xml), true);
$zwischen_var = $arr["site"];
$start = microtime(true);
echo("*** EMF2ISO START ON " . $start );
echo "\r\n";
echo("RECORDS TO BE PROCESSED: " . count($arr['site']));
echo "\r\n";
echo "PROCESSING ... \r\n";
for ($x = 0; $x < count($zwischen_var); $x++) {
    $temp_record = $zwischen_var[$x];
	//echo("EXTRACT FROM: ". $temp_record["path"]."\r\n");
	$file_name = __DIR__ . "/data/emf2iso/emf2gmd_".$temp_record["UUID"].".xml";
	//$emf2iso_xml_file  = emfXml2isoXml("https://data.lter-europe.net/deims/node/8349/emf");
	$emf2iso_xml_file = emfXml2isoXml($temp_record["path"]);
	if(empty($emf2iso_xml_file)){
		echo("The conversion process failed for record: " . $temp_record["UUID"] . " URL: " . $temp_record["path"] ."\r\n");
	}
	else{
		file_put_contents($file_name, $emf2iso_xml_file);
		//echo("LOADED TO: ".$file_name ."\r\n");
	}
}
$end = microtime(true);
echo "\r\n";
echo "\r\n";
echo("*** EMF2ISO END ON ".$end);
echo "\r\n";
echo("PROCESS DURATION: " . round(($end - $start),2) . "s");
echo "\r\n";
?>