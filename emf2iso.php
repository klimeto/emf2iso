<?php
ini_set('implicit_flush',1);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$now = date("Y-m-dTh:m:s");
$url = 'https://data.lter-europe.net/deims/emf/harvest_list';
$url2 = 'http://klimeto.com/projects/2017/uba/app/lib/emfjson2isoxml.php';
$xml = simplexml_load_file($url) or die("feed not loading");
$arr = json_decode(json_encode($xml), true);
$zwischen_var = $arr["site"];
echo("<h2>EMF2ISO START ON ".date("h:i:s")."</h2>");
echo("<p>RECORDS TO BE PROCESSED: " . count($zwischen_var));
echo("<ol>");
//count($zwischen_var)
for ($x = 0; $x < count($zwischen_var); $x++) {
	echo("<li>");
    $temp_record = $zwischen_var[$x];
	echo("EXTRACT FROM: ". $temp_record["path"]."<br>");
	// get current directory
	$file_name = __DIR__ . "/data/emf2gmd_".$temp_record["UUID"].".xml";
	$emf2iso_xml_file = file_get_contents($url2 . '?url=' . $temp_record["path"]);
	echo("TRANSFORM WITH: " . $url2 . "?url=" . $temp_record["path"]."<br>");
	file_put_contents($file_name, $emf2iso_xml_file);
	echo("LOAD TO: ".$file_name ."<br>");
	echo("</li>");
	ob_flush();
    flush();
    sleep(2);
}
echo("</ol>");
echo("<h2>EMF2ISO END ON ".date("h:i:s")."</h2>");
?>