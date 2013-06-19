<?php
echo "starting bioXML2pubmedXML.php<br>\n";
$biofilename= "bio.xml";
$bioxml=simplexml_load_file($biofilename);
$fullnames = getFullNameArray($bioxml);
// these are the pubmed ids based on the name search
$fnpmids = fullnameQuery($fullnames);

$fnPubXMLstr = getPubXML($fnpmids);
// var_dump($fnPubXML);
$fnPubXML = new SimpleXMLElement($fnPubXMLstr);
$fnArticles = $fnPubXML->xpath('//PubmedArticle'); 
$fnArticleCount = count($fnArticles);

$gladstoneAffiliatedArticles = gladstoneFilter($fnArticles);


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
$profilesPMIDs = pubmedUrlToPMID($pubmedURLs);
$pubXML = getPubXML($profilesPMIDs);


function gladstoneFilter($fnArticles){
	$gladArticles = array();
	foreach($fnArticles as $art){
		$art = new SimpleXMLElement($art->asXML());
		$affiliations = $art->xpath('//Affiliation');
		$glad = 0;
		foreach($affiliations as $aff){
			// echo "$aff\n";
			if (preg_match("/Gladstone/", $aff)){
				$glad = 1;
			}
		}
		if($glad == 1){
			array_push($gladArticles, $aff);
		}
	}
	// var_dump($gladArticles);
	echo count($gladArticles)."\n";
	echo count($fnArticles)."\n";
	return $gladArticles;
}



function fullnameQuery($fullnames){
	$pmids = array();
	$apiQuery="http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi";
	foreach($fullnames as $name){
		$ename = explode(" ", $name);
		$queryStr = "db=pubmed&term=".implode("+", $ename)."[Author+-+Full]&retmax=5000";
		echo $queryStr."\n";
		$options = array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_HEADER         => false,    
			CURLOPT_POST           => 1, 
			CURLOPT_VERBOSE        => 1,
			CURLOPT_POSTFIELDS     =>  $queryStr
		);
		$ch = curl_init($apiQuery);
		curl_setopt_array($ch, $options);
		if( $content = curl_exec($ch) ){
			echo "curl success<br>";
		}else {
			die("curl failure"); 
		}
		echo $content."\n";
		$contentxml = new SimpleXMLElement($content);
		echo gettype($contentxml);
		$ids  = $contentxml->xpath('//Id');
		foreach ( $ids as $id ){
			if($id != ""){
				array_push($pmids, $id);
			}
		}	
	}
	$pmids = array_unique($pmids);
	// var_dump($pmids);
	// readline();
	$pmids = array_filter($pmids);
	return $pmids;
}


function getFullNameArray( $bioxml ){
	$fullnames = array();
	$records  = $bioxml->xpath('//RECORD');
	// var_dump($records);
	foreach ($records as $record){
		$record = new SimpleXMLElement($record->asXML());
		$fname = $record->xpath('//field_bio_name_given');
		$lname = $record->xpath('//field_bio_name_family');
		$fullname = $fname[0]." ".$lname[0];
		array_push($fullnames, $fullname);
 	}
 	$fullnames = array_unique($fullnames);
 	// var_dump($fullnames);
	return $fullnames;
}

function makeCommaSepIds($pmids){
	echo count($pmids)."\n";
	// readline();
	$csids = "";
	for ($i = 0 ; $i < count($pmids) ; $i++){
		// echo "pmid: $pmids[$i]\t\t";
		if(isset($pmids[$i])){
			$csids .= $pmids[$i].",";
		}
	}
	// foreach($pmids as $pmid){
	// 	$csids .= $pmids[$i].",";
	// }
	// readline();
	$csids = substr($csids, 0 , -1);
	echo $csids."\n";
	// readline();
	return $csids;
}

function pubmedUrlToPMID($pubmedURLs){
	$size = count($pubmedURLs);
	$pat = "/(\d+)$/";
	$pmids = array();
	foreach( $pubmedURLs as $url){
		preg_match($pat, $url, $matches);
		array_push($pmids, $matches[1]);
	}
	return $pmids;
}

function getPubXML($pmids){
	$XMLs = array();
	$count = 0;
	// $pmids = preg_grep($pat, $pubmedURLs);
	$successfulPMIDs = array();
	$i=0;
	$csids = makeCommaSepIds($pmids);
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