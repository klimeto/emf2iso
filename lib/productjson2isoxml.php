<?php
/*
** PHP SCRIPT TO GENERATE JSON ENCODING FROM XML ENVIRONMENTAL MONITORING FACILTY DATASET
*/
function productJson2isoXml($productJsonUrl){
	ini_set('date.timezone','Europe/Belgrade');
	//header('Content-Type: text/xml');
	//$productJsonUrl = $_GET['url'];
	$cswApi = 'https://data.lter-europe.net/pycsw?';
	$getRecById = $cswApi . 'service=CSW&amp;version=3.0.0&amp;request=GetRecordById&amp;ElementSetName=full&amp;outputSchema=http://www.isotc211.org/2005/gmd&amp;outputFormat=application/json&amp;id=';
	$json = json_decode(file_get_contents($productJsonUrl));
	if ($json){
		$gmdXML = '<?xml version="1.0" encoding="UTF-8"?><gmd:MD_Metadata xmlns:gmd="http://www.isotc211.org/2005/gmd" xmlns:gmx="http://www.isotc211.org/2005/gmx" xmlns:gco="http://www.isotc211.org/2005/gco" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns:gts="http://www.isotc211.org/2005/gts" xmlns:gml="http://www.opengis.net/gml/3.2" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.isotc211.org/2005/gmd http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/apiso-inspire/apiso-inspire.xsd http://www.isotc211.org/2005/srv http://inspire.ec.europa.eu/draft-schemas/inspire-md-schemas/srv/1.0/srv.xsd">';
		/***
			Metadata identifier 
			***/
		$gmlId = $json->{'nodes'}[0]->{'node'}->{'uuid'};
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
			Parent identifier == related site
			***/
		$product_related_site_title = $json->{'nodes'}[0]->{'node'}->{'related_site_title'};
		$product_related_site_uuid = $json->{'nodes'}[0]->{'node'}->{'related_site_uuid'};
		if($product_related_site_uuid){
			$gmdXML .= '<gmd:parentIdentifier>
							<gmx:Anchor xlink:href="'.$getRecById.'emf2gmd_'.$product_related_site_uuid.'" xlink:title="'.$product_related_site_title.'"/>
						</gmd:parentIdentifier>';
		} 
			$gmdXML.= '<gmd:hierarchyLevel>
							<gmd:MD_ScopeCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#MD_ScopeCode" codeListValue="series">series</gmd:MD_ScopeCode>
						</gmd:hierarchyLevel>
						<gmd:hierarchyLevelName>
							<gco:CharacterString>Data product</gco:CharacterString>
						</gmd:hierarchyLevelName>';
			
			/*** 
			C.2.25 Metadata point of contact
			***/
		$product_reporter = $json->{'nodes'}[0]->{'node'}->{'reporter'};
		if(!empty($product_reporter)){
			$gmdXML .= '<gmd:contact>
					  <gmd:CI_ResponsibleParty>
						<gmd:individualName>
						 <gco:CharacterString>'.$product_reporter.'</gco:CharacterString>
						</gmd:individualName>
						 <gmd:role>
							<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
						 </gmd:role>
					  </gmd:CI_ResponsibleParty>
				   </gmd:contact>';
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
			IDENTIFICATION METADATA SECTION START 
		***/
		$gmdXML .= '<gmd:identificationInfo><gmd:MD_DataIdentification><gmd:citation><gmd:CI_Citation>';
		
		
		/***
			C.2.1 Resource title
			***/
		$product_title = $json->{'nodes'}[0]->{'node'}->{'title'};
		if($product_title){
			$gmdXML .= "<gmd:title>
							 <gco:CharacterString>".$product_title."</gco:CharacterString>
						</gmd:title>
						<gmd:date/>";
		}
		
		/***
			C.2.5 Unique resource identifier
			***/
		$product_uuid = $json->{'nodes'}[0]->{'node'}->{'uuid'};
		if ($product_uuid){
			//<gmx:Anchor xlink:href="http://data.demlas.geof.unizg.hr/'. $resIden .'">'. $resIden .'</gmx:Anchor>
			$gmdXML .= '<gmd:identifier>
						  <gmd:MD_Identifier>
							 <gmd:code>
								<gco:CharacterString>https://data.lter-europe.net/deims/'. $product_uuid .'</gco:CharacterString>
							 </gmd:code>
						  </gmd:MD_Identifier>
					   </gmd:identifier>';
		}
		$gmdXML .= '</gmd:CI_Citation></gmd:citation>';
		/***
			C.2.2 Resource abstract
			***/
		$product_abstract = $json->{'nodes'}[0]->{'node'}->{'abstract'};
		$gmdXML .= '<gmd:abstract><gco:CharacterString>'. str_replace("<","smaller than",str_replace("&","and",$product_abstract)) .'</gco:CharacterString></gmd:abstract>';
		/***
			C.2.23 Responsible party
			***/
		if($product_reporter){
			$gmdXML .= '<gmd:pointOfContact>
					  <gmd:CI_ResponsibleParty>
						<gmd:individualName>
						 <gco:CharacterString>'.$product_reporter.'</gco:CharacterString>
						</gmd:individualName>
						 <gmd:role>
							<gmd:CI_RoleCode codeList="http://standards.iso.org/ittf/PubliclyAvailableStandards/ISO_19139_Schemas/resources/Codelist/gmxCodelists.xml#CI_RoleCode" codeListValue="pointOfContact"></gmd:CI_RoleCode>
						 </gmd:role>
					  </gmd:CI_ResponsibleParty>
				   </gmd:pointOfContact>';
		}
		/*** DATA PRODUCT TEMPORAL RESOLUTION ***/
		$product_temp_res = $json->{'nodes'}[0]->{'node'}->{'temporal_resolution'};
		if($product_temp_res){
			$gmdXML .= '<gmd:resourceMaintenance>
							<gmd:MD_MaintenanceInformation>
								<gmd:maintenanceAndUpdateFrequency>
									<gmd:MD_MaintenanceFrequencyCode codeList="https://data.lter-europe.net/deims/admin/structure/taxonomy/temporal_resolution_data_products_" codeListValue="'.$product_temp_res.'">'.$product_temp_res.'</gmd:MD_MaintenanceFrequencyCode>
								</gmd:maintenanceAndUpdateFrequency>
							</gmd:MD_MaintenanceInformation>
						</gmd:resourceMaintenance>';
		}
		
		
		/***
			C.2.10 Keyword value
			***/
		/**** DATA PRODUCT TYPE ****/
		/* NOTE: SHALL WE ADD LINK TO DEIMS PRODUCT TYPE TAXONOMY? IT IS AVAILABLE ONLY FOR REGISTERED USERS? */
		$product_type = $json->{'nodes'}[0]->{'node'}->{'data_product_type'};
		if($product_type){
			$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
						 <gco:CharacterString>'.$product_type.'</gco:CharacterString>
						</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
		}
		/**** PARAMETERS AND PRODUCT KEYWORDS ****/
		$product_paramsArray = explode(", ", $json->{'nodes'}[0]->{'node'}->{'parameters'});
		$product_kwdsArray = explode(", ", $json->{'nodes'}[0]->{'node'}->{'keywords'});
		
	if ($product_paramsArray || $product_kwdsArray){
			$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords>';
			foreach ($product_paramsArray as $param){
				$gmdXML .= '<gmd:keyword><gco:CharacterString>'. $param .'</gco:CharacterString></gmd:keyword>';
			}
			foreach ($product_kwdsArray as $kwd){
				$gmdXML .= '<gmd:keyword><gco:CharacterString>'. $kwd .'</gco:CharacterString></gmd:keyword>';
			}
			$gmdXML .= '<gmd:thesaurusName xlink:href="http://vocabs.ceh.ac.uk/evn/tbl/envthes.evn#http%3A%2F%2Fvocabs.lter-europe.net%2FEnvThes%2F10000" xlink:title="EnvThes"/>';
			$gmdXML .= '</gmd:MD_Keywords></gmd:descriptiveKeywords>';
		}
		/**** OPEN DATA *****/
		$product_opendata = $json->{'nodes'}[0]->{'node'}->{'open_data'};
		if ($product_opendata == 'Yes'){
			$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
						 <gco:CharacterString>Open data</gco:CharacterString>
						</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
		}
		/**** DIGITAL DATA *****/
		$product_digitaldata = $json->{'nodes'}[0]->{'node'}->{'data_digitally_available'};
		if ($product_digitaldata == 'Yes'){
			$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
						 <gco:CharacterString>Digital data</gco:CharacterString>
						</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
		}
		/**** ECOPOTENTIAL DATA *****/
		$product_ecopotentialdata = $json->{'nodes'}[0]->{'node'}->{'available_for_ecopotential'};
		if ($product_ecopotentialdata == 'Yes'){
			$gmdXML .= '<gmd:descriptiveKeywords><gmd:MD_Keywords><gmd:keyword>
						 <gco:CharacterString>Ecopotential project</gco:CharacterString>
						</gmd:keyword></gmd:MD_Keywords></gmd:descriptiveKeywords>';
		}
		/**** INSPIRE SPATIAL DATA THEMES(s) ****/
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
		
		/*** RELATED DATASETS AggregatedInfo https://geo-ide.noaa.gov/wiki/index.php?title=ISO_AggregationInformation ***/
		$length = count($json->{'nodes'});
		$product_related_dataset_uuid = $json->{'nodes'}[0]->{'node'}->{'related_dataset_uuid'};
		//print_r($product_related_datasetArray);
		//print_r($json->{'nodes'}[0]->{'node'});
		if($product_related_dataset_uuid && $length == 1){
				$gmdXML .= ' <gmd:aggregationInfo>
					<gmd:MD_AggregateInformation>
						<gmd:aggregateDataSetIdentifier>
							<gmd:MD_Identifier>
								<gmd:code><gco:CharacterString>'.$getRecById.$product_related_dataset_uuid.'</gco:CharacterString></gmd:code>
							</gmd:MD_Identifier>
						</gmd:aggregateDataSetIdentifier>
						<gmd:associationType>
							<gmd:DS_AssociationTypeCode codeList="http://www.isotc211.org/2005/resources/Codelist/gmxCodelists.xml" codeListValue="crossReference">crossReference</gmd:DS_AssociationTypeCode>
						</gmd:associationType>
					</gmd:MD_AggregateInformation>
				</gmd:aggregationInfo>';
		}
		else if($length > 1){
			foreach($json->{'nodes'} as $node){
				$gmdXML .= ' <gmd:aggregationInfo>
					<gmd:MD_AggregateInformation>
						<gmd:aggregateDataSetIdentifier>
							<gmd:MD_Identifier>
								<gmd:code><gco:CharacterString>'.$getRecById.$node->{'node'}->{'related_dataset_uuid'}.'</gco:CharacterString></gmd:code>
							</gmd:MD_Identifier>
						</gmd:aggregateDataSetIdentifier>
						<gmd:associationType/>
					</gmd:MD_AggregateInformation>
				</gmd:aggregationInfo>';
			}
		}
		
		
		
		/***
			C.2.21 Conditions applying to access and use - TO BE DISCUSSED!
			***/
		
		/***
			C.2.22 Limitations on public access - TO BE DISCUSSED!
			***/
		/***
			C.2.18 Spatial resolution - spatial representation type
			***/
			$product_spatial_resolution = $json->{'nodes'}[0]->{'node'}->{'spatial_resolution'};
			if($product_spatial_resolution){
				$gmdXML .= '<gmd:spatialRepresentationType>
								<gmd:MD_SpatialRepresentationTypeCode codeList="https://data.lter-europe.net/deims/admin/structure/taxonomy_manager/voc/spatial_resolution_data_products_" codeListValue="'.$product_spatial_resolution.'">'.$product_spatial_resolution.'</gmd:MD_SpatialRepresentationTypeCode>
							</gmd:spatialRepresentationType>';
			}
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
		/*
		$ef_geometry_gmlMultiGeomArrayId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'@gml:id'};
		$ef_geometry_gmlMultiGeomArray = $json->{'EnvironmentalMonitoringFacility'}->{'ef:geometry'}->{'gml:MultiGeometry'}->{'gml:geometryMember'};
		$ef_representativePointId = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@gml:id'};
		$ef_representativePointSrs = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'@srsName'};
		$ef_representativePointGmlPos = $json->{'EnvironmentalMonitoringFacility'}->{'ef:representativePoint'}->{'gml:Point'}->{'gml:pos'}->{'$'};
		if($ef_geometry_gmlMultiGeomArray){
			foreach ($ef_geometry_gmlMultiGeomArray as $geometry){
				if ($geometry->{'gml:Polygon'}){
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
		if($ef_representativePointGmlPos){
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
		*/
		/***
			C.2.13 Temporal extent
			***/
		$product_date_range_array = explode(" to ",$json->{'nodes'}[0]->{'node'}->{'date_range'});
		//print_r($product_date_range_array);
		//var_dump($ef_operationalActivityEndIndeterminate). '<br>'; 
		if(count($product_date_range_array) == 2){
			$gmdXML .= '<gmd:extent>
							<gmd:EX_Extent>
								<gmd:temporalElement>
									<gmd:EX_TemporalExtent>
										<gmd:extent>
											<gml:TimePeriod gml:id="deims_data_product_'.$gmlId.'">
												<gml:beginPosition>'.$product_date_range_array[0].'</gml:beginPosition>
												<gml:endPosition>'.$product_date_range_array[1].'</gml:endPosition>
											</gml:TimePeriod>
										</gmd:extent>
									</gmd:EX_TemporalExtent>
								</gmd:temporalElement>
							</gmd:EX_Extent>
						</gmd:extent>';
		}else if (count($product_date_range_array) == 1){
			$gmdXML .= '<gmd:extent>
							<gmd:EX_Extent>
								<gmd:temporalElement>
									<gmd:EX_TemporalExtent>
										<gmd:extent>
											<gml:TimePeriod gml:id="deims_data_product_'.$gmlId.'">
												<gml:beginPosition>'.$product_date_range_array[0].'</gml:beginPosition>
												<gml:endPosition indeterminatePosition="now"/>
											</gml:TimePeriod>
										</gmd:extent>
									</gmd:EX_TemporalExtent>
								</gmd:temporalElement>
							</gmd:EX_Extent>
						</gmd:extent>';
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
		/*** DATA PRODUCT DEIMS LANDING PAGE ***/
		$gmdXML .= '<gmd:distributionInfo><gmd:MD_Distribution>';
		$gmdXML .= '<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>https://data.lter-europe.net/deims/activity/'.$gmlId.'</gmd:URL>
								</gmd:linkage>
							</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>';
		$product_source_url = $json->{'nodes'}[0]->{'node'}->{'source_url'};
		$product_source_title = $json->{'nodes'}[0]->{'node'}->{'source_title'};
		if(!empty($product_source_url)){
			$gmdXML .= '<gmd:transferOptions>
					<gmd:MD_DigitalTransferOptions>
						<gmd:onLine>
							<gmd:CI_OnlineResource>
								<gmd:linkage>
									<gmd:URL>'.$product_source_url.'</gmd:URL>
								</gmd:linkage>';
								if (!empty($product_source_title) && $product_source_url != $product_source_title){
									$gmdXML .= '<gmd:name>
													<gco:CharacterString>'.$product_source_title.'</gco:CharacterString>
												</gmd:name>';
								}
			$gmdXML .= '</gmd:CI_OnlineResource>
						</gmd:onLine>
					</gmd:MD_DigitalTransferOptions>
				</gmd:transferOptions>';
			}
		$gmdXML .='</gmd:MD_Distribution></gmd:distributionInfo>';
		/***
			DISTRIBUTION  METADATA SECTION END 
		***/
		/***
			DATA QUALITY INFO METADATA SECTION START 
		***/
		$gmdXML .= '<gmd:dataQualityInfo><gmd:DQ_DataQuality><gmd:scope/>';
		//C.2.17 Lineage
		$product_data_notes = $json->{'nodes'}[0]->{'node'}->{'data_notes'};
		if($product_data_notes){
			$gmdXML .= '<gmd:lineage>
						<gmd:LI_Lineage>
						   <gmd:statement>
							  <gco:CharacterString>'.$product_data_notes.'</gco:CharacterString>
						   </gmd:statement>
						</gmd:LI_Lineage>
					 </gmd:lineage>';
		}
		else{
			$gmdXML .= '<gmd:lineage>
						<gmd:LI_Lineage>
						   <gmd:statement>
							  <gco:CharacterString>Data product is based on requirements defined by research projects (e.g. EcoPotential) as well as target stakeholder groups (e.g. LTER) in order to allow a summarised description of a series of data.</gco:CharacterString>
						   </gmd:statement>
						</gmd:LI_Lineage>
					 </gmd:lineage>';
		}
		$gmdXML .= '</gmd:DQ_DataQuality></gmd:dataQualityInfo>';
		/***
			DATA QUALITY INFO METADATA SECTION END 
		***/
		
		
		$gmdXML .= '</gmd:MD_Metadata>';
		$gmdXML = str_replace("&","&amp;",$gmdXML);
		$xml = new SimpleXMLElement($gmdXML);
		return $xml->asXML();
	}
}
?>