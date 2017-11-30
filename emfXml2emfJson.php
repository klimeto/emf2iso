<?php
/*
** PHP SCRIPT TO GENERATE JSON ENCODING FROM XML ENVIRONMENTAL MONITORING FACILTY DATASET
*/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('date.timezone','Europe/Belgrade');
include_once 'xml2json.php';
header('Content-Type: application/json');
if (empty($_GET['url'])){
	$emfXMLUrl = "https://data.lter-europe.net/deims/node/8611/emf";
}
else{
	$emfXMLUrl = $_GET['url'];
}
$siteEmfArray = xmlToArray(simplexml_load_file($emfXMLUrl));
$siteEmfJSON = json_encode($siteEmfArray);
echo $siteEmfJSON;