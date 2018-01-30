<?php
include_once 'lib/emfjson2isoxml.php';
$url = 'https://data.lter-europe.net/deims/emf/harvest_list';
$xml = simplexml_load_file($url) or die("feed not loading");
$arr = json_decode(json_encode($xml), true);
$md_records = $arr["site"];
$start = microtime(true);

echo("RECORDS TO BE PROCESSED: " . count($arr['site']));
echo "\r\n";
echo "PROCESSING ... \r\n";
$log_summary = [];
for ($x = 0; $x < count($md_records); $x++) {
    $temp_record = $md_records[$x];
	$file_name = __DIR__ . "/data/emf2iso/emf2gmd_".$temp_record["UUID"].".xml";

	$emf2iso_xml_file = emfXml2isoXml($temp_record["path"]);
	
	// progress bar
	show_status($x, count($md_records));
	usleep(100000);
	
	if(empty($emf2iso_xml_file)){
		$log_summary[] = "The conversion process failed for record: " . $temp_record["UUID"] . " URL: " . $temp_record["path"] ."\r\n";
	}
	else{
		file_put_contents($file_name, $emf2iso_xml_file);
	}
}
$end = microtime(true);
echo "\r\n";
echo "\r\n";
if (empty($log_summary)) {
	echo("*** Script successfully executed.");
}
else {
	foreach($log_summary as $item) {
		echo $item;
	}	
}

echo "\r\n";
echo("PROCESS DURATION: " . round(($end - $start),2) . "s");
echo "\r\n";

// function to provide progress of conversion process
// http://snipplr.com/view/29548/
function show_status($done, $total, $size=30) {
 
    static $start_time;
 
    // if we go over our bound, just ignore it
    if($done > $total) return;
 
    if(empty($start_time)) $start_time=time();
    $now = time();
 
    $perc=(double)($done/$total);
 
    $bar=floor($perc*$size);
 
    $status_bar="\r[";
    $status_bar.=str_repeat("=", $bar);
    if($bar<$size){
        $status_bar.=">";
        $status_bar.=str_repeat(" ", $size-$bar);
    } else {
        $status_bar.="=";
    }
 
    $disp=number_format($perc*100, 0);
 
    $status_bar.="] $disp%  $done/$total";
 
    $rate = ($now-$start_time)/$done;
    $left = $total - $done;
    $eta = round($rate * $left, 2);
 
    $elapsed = $now - $start_time;
 
    $status_bar.= " remaining: ".number_format($eta)." sec.  elapsed: ".number_format($elapsed)." sec.";
 
    echo "$status_bar  ";
 
    flush();
 
    // when done, send a newline
    if($done == $total) {
        echo "\n";
    }
 
}

?>
