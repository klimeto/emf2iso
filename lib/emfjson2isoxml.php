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
$cswApi = 'https://data.lter-europe.net/pycsw?';
$getRecById = $cswApi . 'service=CSW&version=3.0.0&request=GetRecordById&ElementSetName=full&outputSchema=http://www.isotc211.org/2005/gmd&outputFormat=application/json&id=';
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
	$gmdXML = '<?xml version="1.0" encoding="UTF-8"?><gmd:MD_Metadata xmlns:gmd="http://www.isotc211.org/2005/gmd" xmlns:gmx="http://www.isotc211.org/2005/gmx" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml/3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.isotc211.org/2005/gmd http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/apiso-inspire/apiso-inspire.xsd http://www.isotc211.org/2005/srv http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/srv/1.0/srv.xsd">';
	/***
		Metadata identifier 
		***/
	
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
				  </gmd:characterSet>';
				  
	/*** 
		Parent identifier == ef:broader
		***/
	$ef_broader = $json->{'EnvironmentalMonitoringFacility'}->{'ef:broader'};
	if(!empty($ef_broader)){
		$gmdXML .= '<gmd:parentIdentifier>
						<gmx:Anchor xlink:href="'.$getRecById.str_replace("Facility_","emf2gmd_",$ef_broader->{'ef:Hierarchy'}->{'@gml:id'}).'"/>
					</gmd:parentIdentifier>';
	} 
		$gmdXML.='<gmd:hierarchyLevel>
					  <gmd:MD_ScopeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="dataset">dataset</gmd:MD_ScopeCode>
				  </gmd:hierarchyLevel>
				  <gmd:hierarchyLevelName>
						<gco:CharacterString>Research site</gco:CharacterString>
					</gmd:hierarchyLevelName>';
	/*** 
		C.2.25 Metadata point of contact
		***/
	$ef_responsiblePartyArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:responsibleParty'};
	if(!empty($ef_responsiblePartyArray)){
		foreach($ef_responsiblePartyArray as $contact){
			$individualName = $contact->{'base2:RelatedParty'}->{'base2:individualName'}->{'gco:CharacterString'};
			$organisationName = $contact->{'base2:RelatedParty'}->{'base2:organisationName'}->{'gco:CharacterString'};
			$contactInstructions = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:contactInstructions'}->{'gco:CharacterString'};
			$contactRole = $contact->{'base2:RelatedParty'}->{'base2:role'}->{'@xlink:role'};
			$contactEmail = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:electronicMailAddress'};
			$gmdXML .= '<gmd:contact><gmd:CI_ResponsibleParty>';
			if(!empty($individualName)){
				$gmdXML .= '<gmd:individualName>
						 <gco:CharacterString>'.$individualName.'</gco:CharacterString>
						</gmd:individualName>';
			}
			if(!empty($organisationName)){
				$gmdXML .= '<gmd:organisationName>
							<gco:CharacterString>'.$organisationName.'</gco:CharacterString>
						 </gmd:organisationName>';
			}
			if(!empty($contactEmail) || !empty($contactInstructions)){
				$gmdXML .='<gmd:contactInfo>
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
						   </gmd:contactInfo>';
			}
			$gmdXML .= '<gmd:role>
							<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
						 </gmd:role>';
			$gmdXML .= '</gmd:CI_ResponsibleParty></gmd:contact>';
		}
	}
	else{
		$gmdXML .= '<gmd:contact/>';
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
		IDENTIFICATION METADATA SECTION START 
	***/
	$gmdXML .= '<gmd:identificationInfo><gmd:MD_DataIdentification><gmd:citation><gmd:CI_Citation>';
	
	
	/***
		C.2.1 Resource title
		***/
	$ef_name = $json->{'EnvironmentalMonitoringFacility'}->{'ef:name'};
	if(!empty($ef_name)){
		$gmdXML .= "<gmd:title>
						 <gco:CharacterString>".$ef_name."</gco:CharacterString>
					</gmd:title>";
	}
	/*** DATE - added empty element to be valid, but TBD!!!! ***/
	$gmdXML .= "<gmd:date/>";
	/***
		C.2.5 Unique resource identifier
		***/
	$base_localId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:inspireId'}->{'base:Identifier'}->{'base:localId'};
	$base_namespace = $json->{'EnvironmentalMonitoringFacility'}->{'ef:inspireId'}->{'base:Identifier'}->{'base:namespace'};
	if ($base_localId){
		//<gmx:Anchor xlink:href="http://data.demlas.geof.unizg.hr/'. $resIden .'">'. $resIden .'</gmx:Anchor>
		$gmdXML .= '<gmd:identifier>
					  <gmd:MD_Identifier>
						 <gmd:code>
							<gco:CharacterString>'.$base_namespace.'/'. $base_localId .'</gco:CharacterString>
						 </gmd:code>
					  </gmd:MD_Identifier>
				   </gmd:identifier>';
	}
	$gmdXML .= '</gmd:CI_Citation></gmd:citation>';
	/***
		C.2.2 Resource abstract
		***/
	$ef_additionalDescription = $json->{'EnvironmentalMonitoringFacility'}->{'ef:additionalDescription'};
	if(!empty($ef_additionalDescription)){
		$gmdXML .= '<gmd:abstract><gco:CharacterString>'. str_replace("<","smaller than",str_replace("&","and",$ef_additionalDescription)) .'</gco:CharacterString></gmd:abstract>';
	}
	else{
		$gmdXML .= '<gmd:abstract/>';
	}
	
	/***
		C.2.23 Responsible party
		***/
	foreach($ef_responsiblePartyArray as $contact){
		$individualName = $contact->{'base2:RelatedParty'}->{'base2:individualName'}->{'gco:CharacterString'};
		$organisationName = $contact->{'base2:RelatedParty'}->{'base2:organisationName'}->{'gco:CharacterString'};
		$contactInstructions = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:contactInstructions'}->{'gco:CharacterString'};
		$contactRole = $contact->{'base2:RelatedParty'}->{'base2:role'}->{'@xlink:role'};
		$contactEmail = $contact->{'base2:RelatedParty'}->{'base2:contact'}->{'base2:Contact'}->{'base2:electronicMailAddress'};
		$gmdXML .= '<gmd:pointOfContact><gmd:CI_ResponsibleParty>';
		if(!empty($individualName)){
			$gmdXML .= '<gmd:individualName>
					 <gco:CharacterString>'.$individualName.'</gco:CharacterString>
					</gmd:individualName>';
		}
		if(!empty($organisationName)){
			$gmdXML .= '<gmd:organisationName>
						<gco:CharacterString>'.$organisationName.'</gco:CharacterString>
					 </gmd:organisationName>';
		}
		if(!empty($contactEmail) || !empty($contactInstructions)){
			$gmdXML .='<gmd:contactInfo>
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
					   </gmd:contactInfo>';
		}
		$gmdXML .= '<gmd:role>
						<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
					 </gmd:role>';
		$gmdXML .= '</gmd:CI_ResponsibleParty></gmd:pointOfContact>';
	}
	/***
		C.2.10 Keyword value
		***/
	/**** OBSERVED PROPERTIES ****/ 
	$ef_observingCapabilityArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:observingCapability'};
	
	if ($ef_observingCapabilityArray){
		$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords>';
		foreach ($ef_observingCapabilityArray as $observingCapability){
			$observedProperty = $observingCapability->{'ef:ObservingCapability'}->{'ef:observedProperty'}->{'@_text'};
			$gmdXML .= '<gmd:keyword><gco:CharacterString>'. $observedProperty .'</gco:CharacterString></gmd:keyword>';
		}
		$gmdXML .= '<gmd:thesaurusName xlink:href="http://vocabs.ceh.ac.uk/evn/tbl/envthes.evn#http%3A%2F%2Fvocabs.lter-europe.net%2FEnvThes%2F10000" xlink:title="EnvThes"/>';
		$gmdXML .= '</gmd:MD_Keywords></gmd:descriptiveKeywords>';
	}
	/**** MEDIA MONITORED ****/
	$ef_mediaMonitored = $json->{'EnvironmentalMonitoringFacility'}->{'ef:mediaMonitored'};
	if(!empty($ef_mediaMonitored)){
		$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords>';
		foreach($ef_mediaMonitored as $media){
			$gmdXML .= '<gmd:keyword>
						  <gco:CharacterString>'. $ef_mediaMonitored->{'@xlink:title'} .'</gco:CharacterString>
					   </gmd:keyword>';
		}
		$gmdXML .= '<gmd:thesaurusName xlink:href="http://inspire.ec.europa.eu/codelist/MediaValue" xlink:title="Media"/>';
		$gmdXML .= '</gmd:MD_Keywords></gmd:descriptiveKeywords>';
	}
	/**** MEASUREMENT REGIME AND MOBILE *****/
	$ef_measurementRegime = $json->{'EnvironmentalMonitoringFacility'}->{'ef:measurementRegime'}->{'@xlink:href'};
	if(!empty($ef_measurementRegime)){
		$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
					 <gmx:Anchor xlink:href="'.$ef_measurementRegime.'">'.explode("/MeasurementRegimeValue/",$ef_measurementRegime)[1].'</gmx:Anchor>
					</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
	}
	$ef_mobile = $json->{'EnvironmentalMonitoringFacility'}->{'ef:mobile'};
	if($ef_mobile == 'true'){
		$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
					 <gco:CharacterString>mobile</gco:CharacterString>
					</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
	}
	/**** INSPIRE SPATIAL DATA THEMES(s) ****/
	/*"<gmd:descriptiveKeywords>
		<gmd:MD_Keywords>
		 <gmd:keyword>
			<gmx:Anchor xlink:href="http://inspire.ec.europa.eu/theme/ef">Environmental monitoring facilities</gmx:Anchor>
		 </gmd:keyword>
		 <gmd:thesaurusName>
		 <gmd:CI_Citation>
			 <gmd:title>
				<gmx:Anchor xlink:href="http://www.eionet.europa.eu/gemet/inspire_themes">GEMET -
			INSPIRE themes, version 1.0</gmx:Anchor>
			 </gmd:title>
			 <gmd:date>
				 <gmd:CI_Date>
					 <gmd:date>
						<gco:Date>2008-06-01</gco:Date>
					 </gmd:date>
					 <gmd:dateType>
						 <gmd:CI_DateTypeCode
						codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_DateTypeCode"
						codeListValue=""publication"" />
					 </gmd:dateType>
				 </gmd:CI_Date>
			 </gmd:date>
		 </gmd:CI_Citation>
		 </gmd:thesaurusName>
	</gmd:MD_Keywords>
	</gmd:descriptiveKeywords>"*/
	$gmdXML .= '<gmd:descriptiveKeywords>
		<gmd:MD_Keywords>
		 <gmd:keyword>
			<gco:CharacterString>Environmental monitoring facilities</gco:CharacterString>
		 </gmd:keyword>
		 <gmd:thesaurusName>
		 <gmd:CI_Citation>
			 <gmd:title>
				<gco:CharacterString>GEMET - INSPIRE themes, version 1.0</gco:CharacterString>
			 </gmd:title>
			 <gmd:date>
				 <gmd:CI_Date>
					 <gmd:date>
						<gco:Date>2008-06-01</gco:Date>
					 </gmd:date>
					 <gmd:dateType>
						 <gmd:CI_DateTypeCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_DateTypeCode" codeListValue="publication" />
					 </gmd:dateType>
				 </gmd:CI_Date>
			 </gmd:date>
		 </gmd:CI_Citation>
		 </gmd:thesaurusName>
	</gmd:MD_Keywords>
	</gmd:descriptiveKeywords>';
	
	/***
		C.2.21 Conditions applying to access and use - TO BE DISCUSSED!
		***/
	
	/***
		C.2.22 Limitations on public access - TO BE DISCUSSED!
		***/
	/***
		C.2.18 Spatial resolution - TO BE DISCUSSED!
		***/
	/***
		C.2.7 Resource language - TO BE DISCUSSED! ENGLISH AS DEFAULT
		***/
	//$euLanguages = array('Bulgarian' => 'bul','Irish' => 'gle','Croatian' => 'hrv','Italian' => 'ita','Czech' => 'cze','Latvian' => 'lav','Danish' => 'dan','Lithuanian' => 'lit','Dutch' => 'dut','Maltese' => 'mlt','English' => 'eng','Polish' => 'pol','Estonian' => 'est','Portuguese' => 'por','Finnish' => 'fin','Romanian' => 'rum','French' => 'fre','Slovak' => 'slo','German' => 'ger','Slovenian' => 'slv','Greek' => 'gre','Spanish' => 'spa','Hungarian' => 'hun','Swedish' => 'swe');
	$gmdXML .= '<gmd:language><gmd:LanguageCode codeList="http://www.loc.gov/standards/iso639-2/" codeListValue="eng">English</gmd:LanguageCode></gmd:language>';
	/***
		C.2.8 Topic category - http://inspire.ec.europa.eu/metadata-codelist/TopicCategory
		***/
	$gmdXML .= '<gmd:topicCategory><gmd:MD_TopicCategoryCode>environment</gmd:MD_TopicCategoryCode></gmd:topicCategory>';
	
	/***
		C.2.12 Geographic bounding box
		***/
	$ef_geometry_gmlMultiGeomArrayId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'@gml:id'};
	$ef_geometry_gmlMultiGeomArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'gml:geometryMember'};
	$ef_representativePointId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@gml:id'};
	$ef_representativePointSrs = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@srsName'};
	$ef_representativePointGmlPos = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'gml:pos'}->{'$'};
	if(!empty($ef_geometry_gmlMultiGeomArray)){
		foreach ($ef_geometry_gmlMultiGeomArray as $geometry){
			if (!empty($geometry->{'gml:Polygon'}->{'gml:exterior'}->{'gml:LinearRing'}->{'gml:posList'})){
				$posList = $geometry->{'gml:Polygon'}->{'gml:exterior'}->{'gml:LinearRing'}->{'gml:posList'};
				$posListArray = explode(" ", $posList);
				$lats = array();
				$longs = array();
				foreach ($posListArray as $k => $v) {
					if ($k % 2 == 0) {
						$lats[] = $v;
					}
					else {
						$longs[] = $v;
					}
				}
				$wblon = min($longs);
				$eblon = max($longs);
				$sblat = min($lats);
				$nblat = max($lats);
				$gmdXML .= '<gmd:extent>
						<gmd:EX_Extent>
						   <gmd:geographicElement>
							  <gmd:EX_GeographicBoundingBox>
								 <gmd:westBoundLongitude>
									<gco:Decimal>'. $wblon .'</gco:Decimal>
								 </gmd:westBoundLongitude>
								 <gmd:eastBoundLongitude>
									<gco:Decimal>'. $eblon .'</gco:Decimal>
								 </gmd:eastBoundLongitude>
								 <gmd:southBoundLatitude>
									<gco:Decimal>'. $sblat .'</gco:Decimal>
								 </gmd:southBoundLatitude>
								 <gmd:northBoundLatitude>
									<gco:Decimal>'. $nblat .'</gco:Decimal>
								 </gmd:northBoundLatitude>
							  </gmd:EX_GeographicBoundingBox>
						   </gmd:geographicElement>
						</gmd:EX_Extent>
					 </gmd:extent>';
			}
		}
	}
	if(!empty($ef_representativePointGmlPos)){
		$gmlPosArray = explode(" ",$ef_representativePointGmlPos);
		$wblon = $gmlPosArray[1];
		$eblon = $gmlPosArray[1];
		$sblat = $gmlPosArray[0];
		$nblat = $gmlPosArray[0];
		$gmdXML .= '<gmd:extent>
						<gmd:EX_Extent>
						   <gmd:geographicElement>
							  <gmd:EX_GeographicBoundingBox>
								 <gmd:westBoundLongitude>
									<gco:Decimal>'. $wblon .'</gco:Decimal>
								 </gmd:westBoundLongitude>
								 <gmd:eastBoundLongitude>
									<gco:Decimal>'. $eblon .'</gco:Decimal>
								 </gmd:eastBoundLongitude>
								 <gmd:southBoundLatitude>
									<gco:Decimal>'. $sblat .'</gco:Decimal>
								 </gmd:southBoundLatitude>
								 <gmd:northBoundLatitude>
									<gco:Decimal>'. $nblat .'</gco:Decimal>
								 </gmd:northBoundLatitude>
							  </gmd:EX_GeographicBoundingBox>
						   </gmd:geographicElement>
						</gmd:EX_Extent>
					 </gmd:extent>';
	}
	/***
		C.2.13 Temporal extent
		***/
	$ef_operationalActivityPeriod = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'};
	$ef_operationalActivityBegin = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:beginPosition'};
	$ef_operationalActivityGmlId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'@gml:id'};
	$ef_operationalActivityEnd = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:endPosition'};
	$ef_operationalActivityEndIndeterminate = $json->{'EnvironmentalMonitoringFacility'}->{'ef:operationalActivityPeriod'}->{'ef:OperationalActivityPeriod'}->{'ef:activityTime'}->{'gml:TimePeriod'}->{'gml:endPosition'}->{'@indeterminatePosition'};
	//var_dump($ef_operationalActivityEndIndeterminate). '<br>'; 
	if(!empty($ef_operationalActivityBegin)){
		$gmdXML .= '<gmd:extent>
						<gmd:EX_Extent>
							<gmd:temporalElement>
								<gmd:EX_TemporalExtent>
									<gmd:extent>
										<gml:TimePeriod gml:id="'.$ef_operationalActivityGmlId.'">
											<gml:beginPosition>'.$ef_operationalActivityBegin.'</gml:beginPosition>';
											if($ef_operationalActivityEndIndeterminate){
												$gmdXML .= '<gml:endPosition indeterminatePosition="'.$ef_operationalActivityEndIndeterminate.'"/>';
											}
											else if ($ef_operationalActivityEnd){
												$gmdXML .= '<gml:endPosition>'.$ef_operationalActivityEnd.'</gml:endPosition>';
											}
		$gmdXML .= '</gml:TimePeriod></gmd:extent></gmd:EX_TemporalExtent></gmd:temporalElement></gmd:EX_Extent></gmd:extent>';
	}
	
	$gmdXML .= '</gmd:MD_DataIdentification></gmd:identificationInfo>';
	/***
		IDENTIFICATION METADATA SECTION END 
	***/
	/***
		DISTRIBUTION  METADATA SECTION START 
	***/
	/***
		C.4.2 Resource locator
		***/
	$ef_onlineResourceArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:onlineResource'};
	if(!empty($ef_onlineResourceArray)){
		$gmdXML .='<gmd:distributionInfo><gmd:MD_Distribution>';
		foreach($ef_onlineResourceArray as $linkage){
			$gmdXML .= '<gmd:transferOptions>
                <gmd:MD_DigitalTransferOptions>
                    <gmd:onLine>
                        <gmd:CI_OnlineResource>
                            <gmd:linkage>
                                <gmd:URL>'.$linkage.'</gmd:URL>
                            </gmd:linkage>
                        </gmd:CI_OnlineResource>
                    </gmd:onLine>
                </gmd:MD_DigitalTransferOptions>
            </gmd:transferOptions>';
		}
	}
	/**** WMS GET MAP ****/
	
	/**** WFS GET FEATURE SHP ****/
	if(!empty($base_localId)){
		$gmdXML .= "<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>https://data.lter-europe.net/geoserver/deims/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=deims:lter_all_formal&CQL_FILTER=uuid='".$base_localId."'&outputFormat=SHAPE-ZIP</gmd:URL>
								</gmd:linkage>
								<gmd:name>
								<gco:CharacterString>WFS GetFeature request for downloading the data set in SHP format</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList='http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode' codeListValue='download' />
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>";
	}
	/**** WFS GET FEATURE GML 3.2 ****/
	if(!empty($base_localId)){
		$gmdXML .= "<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>https://data.lter-europe.net/geoserver/deims/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=deims:lter_all_formal&CQL_FILTER=uuid='".$base_localId."'&outputFormat=application%2Fgml%2Bxml%3B+version%3D3.2</gmd:URL>
								</gmd:linkage>
								<gmd:name>
								<gco:CharacterString>WFS GetFeature request for downloading the data set in GML 3.2 format</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList='http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode' codeListValue='download' />
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>";
	}
	/**** WFS GET FEATURE GEOJSON ****/
	if(!empty($base_localId)){
		$gmdXML .= "<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>https://data.lter-europe.net/geoserver/deims/ows?service=WFS&version=2.0.0&request=GetFeature&typeName=deims:lter_all_formal&CQL_FILTER=uuid='".$base_localId."'&outputFormat=application%2Fjson</gmd:URL>
								</gmd:linkage>
								<gmd:name>
								<gco:CharacterString>WFS GetFeature request for downloading the data set in GeoJSON format</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList='http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode' codeListValue='download' />
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>";
	}
	/**** LINKS TO RELATED DATASET (CSW GETRECORDBYID) ****/
	$ef_hasObservationArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:hasObservation'}; 
	if(!empty($ef_hasObservationArray) && count($ef_hasObservationArray) > 1){
		foreach($ef_hasObservationArray as $dataset){
			$datasetGmdArray = xmlToArray(simplexml_load_file($dataset->{'@xlink:href'}));
			$datasetGmdJSON = json_encode($datasetGmdArray);
			$jsonObject = json_decode($datasetGmdJSON);
			$uuid = $jsonObject->{'MD_Metadata'}->{'gmd:fileIdentifier'}->{'gco:CharacterString'};
			if (!empty($uuid)){
				$gmdURL = $getRecById . $uuid;
			}
			else{
				$gmdURL = $dataset->{'@xlink:href'};
			}
			$gmdXML .= '<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>'.$gmdURL.'</gmd:URL>
								</gmd:linkage>
								<gmd:protocol>
									<gco:CharacterString>HTTP</gco:CharacterString>
								</gmd:protocol>
								 <gmd:applicationProfile>
									<gco:CharacterString>Catalogue Service for the Web (CSW)</gco:CharacterString>
								</gmd:applicationProfile>
								<gmd:name>
								<gco:CharacterString>'.$dataset->{'@xlink:title'}.'</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="search"/>
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>';
		}
	}
	else if(count($ef_hasObservationArray) == 1){
		$datasetGmdArray = xmlToArray(simplexml_load_file($ef_hasObservationArray->{'@xlink:href'}));
		$datasetGmdJSON = json_encode($datasetGmdArray);
		$jsonObject = json_decode($datasetGmdJSON);
		$uuid = $jsonObject->{'MD_Metadata'}->{'gmd:fileIdentifier'}->{'gco:CharacterString'};
		if (!empty($uuid)){
			$gmdURL = $getRecById . $uuid;
		}
		else{
			$gmdURL = $dataset->{'@xlink:href'};
		}
		$gmdXML .= '<gmd:transferOptions>
				<gmd:MD_DigitalTransferOptions>
					<gmd:onLine>
						<gmd:CI_OnlineResource>
							<gmd:linkage>
								<gmd:URL>'.$gmdURL.'</gmd:URL>
							</gmd:linkage>
							<gmd:protocol>
								<gco:CharacterString>HTTP</gco:CharacterString>
							</gmd:protocol>
							 <gmd:applicationProfile>
								<gco:CharacterString>Catalogue Service for the Web (CSW)</gco:CharacterString>
							</gmd:applicationProfile>
							<gmd:name>
							<gco:CharacterString>'.$dataset->{'@xlink:title'}.'</gco:CharacterString>
							</gmd:name>
							<gmd:function>
								<gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="search"/>
							</gmd:function>
						</gmd:CI_OnlineResource>
					</gmd:onLine>
				</gmd:MD_DigitalTransferOptions>
			</gmd:transferOptions>';
	}
	$ef_belongsToArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:belongsTo'};
	if(!empty($ef_belongsToArray)){
		foreach($ef_belongsToArray as $belongsTo){
			if($belongsTo->{'@xlink:href'}){
				$gmdXML .= '<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>'.$belongsTo->{'@xlink:href'}.'</gmd:URL>
								</gmd:linkage>
								<gmd:name>
								<gco:CharacterString>'.$belongsTo->{'ef:NetworkFacility'}->{'gml:name'}.'</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="information"/>
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>';
			}
		}
	}
	$ef_involvedInArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:involvedIn'};
	if(!empty($ef_involvedInArray)){
		foreach($ef_involvedInArray as $involvedIn){
			if($involvedIn->{'ef:EnvironmentalMonitoringActivity'}->{'ef:onlineResource'}){
				$gmdXML .= '<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>'.$involvedIn->{'ef:EnvironmentalMonitoringActivity'}->{'ef:onlineResource'}.'</gmd:URL>
								</gmd:linkage>
								<gmd:name>
								<gco:CharacterString>'.$involvedIn->{'ef:EnvironmentalMonitoringActivity'}->{'gml:name'}.'</gco:CharacterString>
								</gmd:name>
								<gmd:function>
									<gmd:CI_OnLineFunctionCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_OnLineFunctionCode" codeListValue="information"/>
								</gmd:function>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>';
			}
		}
	}
	$gmdXML .='</gmd:MD_Distribution></gmd:distributionInfo>';
	/***
		DISTRIBUTION  METADATA SECTION END 
	***/
	/***
		DATA QUALITY INFO METADATA SECTION START 
	***/
	$gmdXML .= '<gmd:dataQualityInfo><gmd:DQ_DataQuality><gmd:scope/>';
	// INSPIRE DEAFAULT SPECIFICATION
	// <gmx:Anchor xlink:href="http://data.europa.eu/eli/reg/2010/1089">COMMISSION REGULATION (EU) No 1089/2010 of 23 November 2010 implementing Directive 2007/2/EC of the European Parliament and of the Council as regards interoperability of spatial data sets and services</gmx:Anchor>
	$gmdXML .= '<gmd:report><gmd:DQ_DomainConsistency><gmd:result>
				 <gmd:DQ_ConformanceResult>
					 <gmd:specification xlink:href="http://inspire.ec.europa.eu/id/citation/ir/reg-1089-2010">
						 <gmd:CI_Citation>
							 <gmd:title>
							 <gco:CharacterString>COMMISSION REGULATION (EU) No 1089/2010 of 23 November 2010 implementing Directive 2007/2/EC of the European Parliament and of the Council as regards interoperability of spatial data sets and services</gco:CharacterString>
							 </gmd:title>
							 <gmd:date>
								 <gmd:CI_Date>
									 <gmd:date>
										<gco:Date>2010-12-08</gco:Date>
									 </gmd:date>
									 <gmd:dateType>
										<gmd:CI_DateTypeCode codeList="http://standards.iso.org/iso/19139/resources/gmxCodelists.xml#CI_DateTypeCode" codeListValue="publication">publication</gmd:CI_DateTypeCode>
									 </gmd:dateType>
								 </gmd:CI_Date>
							 </gmd:date>
						 </gmd:CI_Citation>
					 </gmd:specification>
					 <gmd:explanation>
						<gco:CharacterString>This data set is conformant with the INSPIRE Implementing Rules for the interoperability of spatial data sets and services</gco:CharacterString>
					 </gmd:explanation>
					 <gmd:pass>
						<gco:Boolean>false</gco:Boolean>
					 </gmd:pass>
				 </gmd:DQ_ConformanceResult>
				</gmd:result></gmd:DQ_DomainConsistency></gmd:report>';
	//C.2.17 Lineage 
	$gmdXML .= '<gmd:lineage>
				<gmd:LI_Lineage>
				   <gmd:statement>
					  <gco:CharacterString>Dataset has been created by data transformation from the original record collected by the site managers using DEIMS site metadata editing form.</gco:CharacterString>
				   </gmd:statement>
				</gmd:LI_Lineage>
			 </gmd:lineage>';
	$gmdXML .= '</gmd:DQ_DataQuality></gmd:dataQualityInfo>';
	/***
		DATA QUALITY INFO METADATA SECTION END 
	***/
	
	
	
	/*** ELEMENTS NOT MAPPED YET ***/
	$ef_legalBacgroundArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:legalBackground'};
	$ef_purpose = $json->{'EnvironmentalMonitoringFacility'}->{'ef:purpose'}->{'@xlink:href'};
	
	$gmdXML .= '</gmd:MD_Metadata>';
	$gmdXML = str_replace("&","&amp;",$gmdXML);
	$xml = new SimpleXMLElement($gmdXML);
	echo $xml->asXML();
}

