<?php
$biofilename= "bio.xml";
$bioxml=simplexml_load_file($biofilename);

$ids = getUCSFids($bioxml);

$ids = array_unique($ids);
// foreach ($ids as $id ){
// 	echo $id."/n<br>";
// }

$pubArr = queryProfiles($ids);

$pubmedURLs = $pubArr['pubmedURLs'];

$pubmedURLs = array_unique($pubmedURLs);

$pubXMLs = getPubXMLs($pubmedURLs);







function getPubXMLs($pubmedURLs){
	$XMLs = array();
	$size = count($pubmedURLs);
	$count = 0;
	$pat = "/[0-9]+$/";
	$pmids = preg_grep($pat, $pubmedURLs);
	$successfulPMIDs = array();
	$i=0;
	while(count($pmids) > 0 && $i<50){
		echo "Getting Pubs Attempt: ".++$i."/n<br>";
		foreach($pmids as $pub){
			$pubmedQuery = "https://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=".$pub."&retmode=xml";
			if ( $handle = fopen($pubmedQuery, 'r')){
				echo "Found xml for ".$pub."  number: ".++$count." out of ".$size."/n<br>";
				$xml = stream_get_contents($handle);
				// echo $xml."/n<br>";
				array_push($XMLs, $xml);
				array_push($successfulPMIDs, $pub);	
			}
		}
		$pmids = array_diff($pmids, $successfulPMIDs);
	}
	return $XMLs;
}



function getUCSFids($bioxml){
	$out = $bioxml->xpath('//field_ucsfid_value');
	return $out;
}


function queryProfiles( $ids ){

	$out = array();
	$pubmedURLs = array();
	$pubWOpubmedIDs = array();
	$num = 0;
	$i = 0;
	$successfulIDs = array();
	while(count($ids) > 1 && $i < 50 ){
		$size = count($ids);
		$num = 0;
		echo "Trying to query profiles for Ids: ".++$i."/n<br>";
		foreach($ids as $id){
			echo "Processing id: ".$id." number: ".++$num." out of ".$size."/n<br>";
			if($id != 0){
				$person_id = (int) ($id/10) + 2569307; 
				$apiQuery = "https://profiles.ucsf.edu/CustomAPI/v1/JSONProfile.aspx?source=Gladstone&Person=".$person_id."&publications=full";
				if ( $handle = fopen($apiQuery, 'r')){
					$jsonstr = stream_get_contents($handle);
					array_push($successfulIDs, $id);
				}
				$json = json_decode( $jsonstr , $assoc = true);
				$keys1 = array();
				if (isset($json["Profiles"][0]['Publications'])){
					$keys1 = array_keys( $json["Profiles"][0]['Publications']);
					foreach( $keys1 as $key ){
						if (isset($json["Profiles"][0]['Publications'][$key]['PublicationSource'][0]['PublicationSourceURL'] )){
							$URL = $json["Profiles"][0]['Publications'][$key]['PublicationSource'][0]['PublicationSourceURL'];
							echo "found URL: ".$URL."/n<br>";
							// echo $URL."/n<br>";
							array_push($pubmedURLs, $URL); 
						}
						else {
							array_push($pubWOpubmedIDs , $json["Profiles"][0]['Publications'][$key] );
						}
					}
				}
			}
		}
		$ids = array_diff($ids, $successfulIDs);
	}
	$out['pubmedURLs'] = $pubmedURLs;
	$out['pubWOpubmedIDs'] = $pubWOpubmedIDs;
	return $out;
}	