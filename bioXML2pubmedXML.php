<?php
echo "starting bioXML2pubmedXML.php<br>";
$biofilename= "biosmall.xml";

$bioxml=simplexml_load_file($biofilename);

$ids = getUCSFids($bioxml);

$ids = array_unique($ids);
// foreach ($ids as $id ){
// 	echo $id."\n<br>";
// }
echo "ids ".gettype($ids)." count ".count($ids)." id[0]".$ids[0]."<br>";

$pubArr = queryProfiles($ids);

echo "pubarr ".gettype($pubArr)." count ".count($pubArr)."<br>";

$pubmedURLs = $pubArr['pubmedURLs'];

echo "pubmedURLs ".gettype($pubmedURLs)." count ".count($pubmedURLs)."<br>";

$pubmedURLs = array_unique($pubmedURLs);

$pubXML = getPubXML($pubmedURLs);







function getPubXML($pubmedURLs){
	$XMLs = array();
	$size = count($pubmedURLs);
	$count = 0;
	$pat = "/(\d+)$/";
	$pmids = array();
	foreach( $pubmedURLs as $url){
		preg_match($pat, $url, $matches);
		array_push($pmids, $matches[1]);
	}
	// $pmids = preg_grep($pat, $pubmedURLs);
	$successfulPMIDs = array();
	$i=0;
	
	$csids = "";
	for ($i = 0 ; $i < count($pmids) ; $i++){
		$csids .= $pmids[$i].",";
	}
	$csids = substr($csids, 0 , -1);
	// echo $csids."<br>";
	// $apiQuery = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?db=pubmed&id=".$csids."&retmode=xml";
	$apiQuery = "http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi";

	// echo  $apiQuery."<br>";
	//set curl options
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => false,    
		CURLOPT_POST           => 1, 
		CURLOPT_VERBOSE        => 1,
		CURLOPT_POSTFIELDS     =>  "db=pubmed&id=$csids&retmode=xml"
	);

	$ch = curl_init($apiQuery);
	curl_setopt_array($ch, $options);
	if( $content = curl_exec($ch) ){
		echo "curl success<br>";
	}else {
		die("curl failure"); 
	}

	return $content;
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
		echo "Trying to query profiles for Ids: ".++$i."\n<br>";
		foreach($ids as $id){
			echo "Processing id: ".$id." number: ".++$num." out of ".$size."\n<br>";
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
							echo "found URL: ".$URL."\n<br>";
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