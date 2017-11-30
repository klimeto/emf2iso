<?php
/*
** PHP SCRIPT TO GENERATE JSON ENCODING FROM XML ENVIRONMENTAL MONITORING FACILTY DATASET
*/
//ini_set('display_errors', 1);
//ini_set('display_startup_errors', 1);
//error_reporting(E_ALL);
ini_set('date.timezone','Europe/Belgrade');
include_once 'xml2json.php';
header('Content-Type: text/xml');
if (empty($_GET['url'])){
	$emfXMLUrl = "https://data.lter-europe.net/deims/node/8611/emf";
}
else{
	$emfXMLUrl = $_GET['url'];
}
$siteEmfArray = xmlToArray(simplexml_load_file($emfXMLUrl));
$siteEmfJSON = json_encode($siteEmfArray);
//echo $siteEmfJSON;
$json = json_decode($siteEmfJSON);
if ($json){
	$gmdXML = '<?xml version="1.0" encoding="UTF-8"?><gmd:MD_Metadata xmlns:gmd="http://www.isotc211.org/2005/gmd" xmlns:gmx="http://www.isotc211.org/2005/gmx" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.isotc211.org/2005/gmd http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/apiso-inspire/apiso-inspire.xsd http://www.isotc211.org/2005/srv http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/srv/1.0/srv.xsd">';
	$gmlId = $json->{'EnvironmentalMonitoringFacility'}->{'@gml:id'};
	$gmdXML .= '<gmd:fileIdentifier><gco:CharacterString>'. $gmlId . '</gco:CharacterString></gmd:fileIdentifier>';
	/*** 
		C.2.27 Metadata language + C.3.4 Character Encoding + 3.1.1.1 Resource type
		***/
	$gmdXML .= '<gmd:language>
					  <gmd:LanguageCode codeList="http://www.loc.gov/standards/iso639-2/" codeListValue="eng">English</gmd:LanguageCode>
				  </gmd:language>
				  <gmd:characterSet>
					  <gmd:MD_CharacterSetCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/codelist/gmxCodelists.xml#MD_CharacterSetCode" codeListValue="utf8">utf8</gmd:MD_CharacterSetCode>
				  </gmd:characterSet>
				  <gmd:hierarchyLevel>
					  <gmd:MD_ScopeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="dataset">dataset</gmd:MD_ScopeCode>
				  </gmd:hierarchyLevel>';
	/*** 
		C.2.25 Metadata point of contact
		***/
	$ef_responsiblePartyArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:responsibleParty'};
	foreach($ef_responsiblePartyArray as $contact){
		$individualName = $contact->{'base2:RelatedParty'}->{'base2:individualName'}->{'gco:CharacterString'};
		$organisationName = $contact->{'base2:RelatedParty'}->{'base2:organisationName'}->{'gco:CharacterString'};
		$contactInstructions = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:contactInstructions'}->{'gco:CharacterString'};
		$contactRole = $contact->{'base2:RelatedParty'}->{'base2:role'}->{'@xlink:role'};
		$contactEmail = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:electronicMailAddress'};
		$gmdXML .= '<gmd:contact>
				  <gmd:CI_ResponsibleParty>
					<gmd:individualName>
					 <gco:CharacterString>'.$individualName.'</gco:CharacterString>
					</gmd:individualName>
					 <gmd:organisationName>
						<gco:CharacterString>'.$organisationName.'</gco:CharacterString>
					 </gmd:organisationName>
					 <gmd:contactInfo>
						<gmd:CI_Contact>
						 <gmd:address>
						  <gmd:CI_Address>
						   <gmd:electronicMailAddress>
							<gco:CharacterString>'.$contactEmail.'</gco:CharacterString>
						   </gmd:electronicMailAddress>
						  </gmd:CI_Address>
						 </gmd:address>
						 <gmd:contactInstructions>
						  <gco:CharacterString>'.$contactInstructions.'</gco:CharacterString>
						 </gmd:contactInstructions>
						</gmd:CI_Contact>
					   </gmd:contactInfo>
					 <gmd:role>
						<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
					 </gmd:role>
				  </gmd:CI_ResponsibleParty>
			   </gmd:contact>';
	}
	/***
		C.2.26 Metadata date
		***/
	$gmdXML .=  '<gmd:dateStamp>
					  <gco:Date>'. date("Y-m-d") . '</gco:Date>
				   </gmd:dateStamp>';
	/*** 
		C.3.1 Coordinate Reference System
		***/
	//<gmx:Anchor xlink:href="http://www.opengis.net/def/crs/EPSG/0/3045">EPSG:3045</gmx:Anchor>
	$ef_representativePointId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@gml:id'};
	//var_dump($ef_representativePointId). '<br>'; 
	$ef_representativePointSrs = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@srsName'};
	//var_dump($ef_representativePointSrs). '<br>'; 
	$ef_representativePointGmlPos = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'gml:pos'}->{'$'};
	//var_dump($ef_representativePointGmlPos). '<br>'; 
	if ($ef_representativePointSrs){
		$gmdXML .= '<gmd:referenceSystemInfo>
					  <gmd:MD_ReferenceSystem>
						 <gmd:referenceSystemIdentifier>
							<gmd:RS_Identifier>
							   <gmd:code>
								   <gco:CharacterString>'.$ef_representativePointSrs.'</gco:CharacterString>
							   </gmd:code>
							</gmd:RS_Identifier>
						 </gmd:referenceSystemIdentifier>
					  </gmd:MD_ReferenceSystem>
				   </gmd:referenceSystemInfo>';
	}
	/***
		IDENTIFICATION METADATA SECTION
	***/
	$gmdXML .= '<gmd:identificationInfo><gmd:MD_DataIdentification><gmd:citation><gmd:CI_Citation>';
	$ef_name = $json->{'EnvironmentalMonitoringFacility'}->{'ef:name'};
	if($ef_name){
		$gmdXML .= "<gmd:title>
						 <gco:CharacterString>".$ef_name."</gco:CharacterString>
					</gmd:title>";
	}
	
	$gmdXML .= '</gmd:CI_Citation></gmd:citation>';
	
	$gmdXML .= '</gmd:MD_DataIdentification></gmd:identificationInfo>';
	
	$base_localId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:inspireId'}->{'base:Identifier'}->{'base:localId'};
	//var_dump($base_localId); 
	$base_namespace = $json->{'EnvironmentalMonitoringFacility'}->{'ef:inspireId'}->{'base:Identifier'}->{'base:localId'};
	//var_dump($base_namespace). '<br>'; 
	
	//var_dump($ef_name). '<br>'; 
	$ef_additionalDescription = $json->{'EnvironmentalMonitoringFacility'}->{'ef:additionalDescription'};
	//var_dump($ef_additionalDescription). '<br>'; 
	$ef_mediaMonitored = $json->{'EnvironmentalMonitoringFacility'}->{'ef:mediaMonitored'};
	//var_dump($ef_mediaMonitored). '<br>'; 
	$ef_legalBacgroundArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:legalBackground'};
	//var_dump($ef_legalBacgroundArray). '<br>'; 
	$ef_responsibleParty = $json->{'EnvironmentalMonitoringFacility'}->{'ef:responsibleParty'};
	//var_dump($ef_responsibleParty). '<br>'; 
	$ef_geometry_gmlMultiGeomArrayId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'@gml:id'};
	//var_dump($ef_geometry_gmlMultiGeomArrayId). '<br>'; 
	$ef_geometry_gmlMultiGeomArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'gml:geometryMember'};
	//var_dump($ef_geometry_gmlMultiGeomArray). '<br>'; 
	$ef_onlineResourceArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:onlineResource'};
	//var_dump($ef_onlineResourceArray). '<br>'; 
	$ef_purpose = $json->{'EnvironmentalMonitoringFacility'}->{'ef:purpose'}->{'@xlink:href'};
	//var_dump($ef_purpose). '<br>'; 
	$ef_observingCapabilityArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:observingCapability'};
	//var_dump($ef_observingCapabilityArray). '<br>'; 
	$ef_broader = $json->{'EnvironmentalMonitoringFacility'}->{'ef:broader'}->{'@xlink:href'};
	//var_dump($ef_broader). '<br>'; 
	$ef_hasObservationArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:hasObservation'};
	//var_dump($ef_hasObservationArray). '<br>'; 
	$ef_involvedInArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:involvedIn'};
	//var_dump($ef_involvedInArray). '<br>'; 
	$ef_representativePointId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@gml:id'};
	//var_dump($ef_representativePointId). '<br>'; 
	$ef_representativePointSrs = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@srsName'};
	//var_dump($ef_representativePointSrs). '<br>'; 
	$ef_representativePointGmlPos = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'gml:pos'}->{'$'};
	//var_dump($ef_representativePointGmlPos). '<br>'; 
	$ef_measurementRegime = $json->{'EnvironmentalMonitoringFacility'}->{'ef:measurementRegime'}->{'@xlink:href'};
	//var_dump($ef_measurementRegime). '<br>'; 
	$ef_mobile = $json->{'EnvironmentalMonitoringFacility'}->{'ef:mobile'};
	//var_dump($ef_mobile). '<br>'; 
	$ef_operationalActivityBegin = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:beginPosition'};
	//var_dump($ef_operationalActivityBegin). '<br>'; 
	$ef_operationalActivityEnd = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:endPosition'};
	//var_dump($ef_operationalActivityEnd). '<br>'; 
	$ef_operationalActivityEndIndeterminate = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:endPosition'}->{'@indeterminatePosition'};
	//var_dump($ef_operationalActivityEndIndeterminate). '<br>'; 
	$ef_belongsToArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:belongsTo'};
	
	
	$gmdXML .= '</gmd:MD_Metadata>';
	$xml = new SimpleXMLElement($gmdXML);
	echo $xml->asXML();
}

