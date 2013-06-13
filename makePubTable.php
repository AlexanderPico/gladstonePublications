<?php


// 
$test = false;
// $test = true;

// if ($test == true) {
// 	$pubXMLs = array();
// 	require 'xmlExample.php';
// 	require 'xmlPasteTest.php';
// 	array_push($pubXMLs, $xmlstr);
// 	array_push($pubXMLs, $xmlpastestr);
// }

// if($test == true){

// 	require 'xmlExample.php';
// 	require 'xmlPasteTest.php';
// 	require 'bioXML2pubmedXML.php';
// 	// array_unshift($pubXMLs, $xmlstr);
// 	// array_unshift($pubXMLs, $xmlpastestr);
// 	echo "-START-".$pubXMLs[0]."-END-\n<br>";
// 	echo "-START-".$pubXMLs[1]."-END-\n<br>";

// }
// else{
// 	require 'bioXML2pubmedXML.php';
// }

require 'bioXML2pubmedXML.php';


makeTable($pubXML);


function makeTable($xmlStr){
	// echo $xmlStr."<br>";
	$xmlobj  =  new SimpleXMLElement($xmlStr); 
	$xmlArr = $xmlobj->xpath('//PubmedArticle');
	$outdir = "out";
	$filename = date('Y-m-d_H:i:s')."-pubTable.tsv";
	if (!file_exists($outdir)) {
	    mkdir($outdir);
	}
	$FILE = fopen($outdir."/".$filename, "w");
	fwrite($FILE, makeheader());
	$size = count($xmlArr);
	echo "size $size<br>";
	$count = 0;
	// for($i = 0 ; $i < $size; $i++){ 
	foreach( $xmlArr as $xml ){	
		echo "Processing Pub ".++$count." out of ".$size."\n<br>";
		// echo $xmlstr."\n<br>";
		// $xmlstr = trim($xmlArr[$i]);
		// $xmlobj = new SimpleXMLElement($xmlstr);
		// echo "xmlobj type: ".gettype($xmlobj)."\n<br>";
		$row = makerow($xml);
		fwrite($FILE, $row);
	}
	fclose($FILE);
}



function makeheader(){
	$out = '';
	$out.= "Authors";
	$out .= "\t";
	$out.= "Title";
	$out .= "\t";
	$out.= "Journal";
	$out .= "\t";
	$out.= "Vol:Issue:Page";
	$out .= "\t";
	$out.= "Year";
	$out .= "\t";
	$out .= "Pubmed_Id";
	$out .= "\t";
	$out.= "Citation_Number";
	$out .= "\t";
	$out.= "Authors_Full_Name";
	$out .= "\t";
	$out.= "First_Author";
	$out .= "\t";
	$out.= "LastAuthor";
	$out .= "\t";
	$out.= "Affiliations";
	$out .= "\t";
	$out.= "Grants";
	$out .= "\n";
	return $out;
}


function makeRow($xmlobj){
	// echo "xmlobj type: ".gettype($xmlobj)."\n<br>";
	$xmlstr = $xmlobj->asXML();
	// echo $xmlstr."\n<br>";
	$out = '';
	$authors = getAuthors($xmlobj);
	$title = getTitle($xmlobj);
	$out .= makeAuthorField($authors);
	$out .= "\t";
	$out .= $title;
	$out .= "\t";
	$out .= getJournalTitle($xmlobj);
	$out .= "\t";
	$out .= getVolIssuePage($xmlobj);
	$out .= "\t";
	$out .= getYear($xmlobj);
	$out .= "\t";
	$out .= getPubmedID($xmlobj);
	$out .= "\t";
	$out .= getCitations($title);
	$out .= "\t";
	$out .= makeAuthorFieldFullName($authors);
	$out .= "\t";
	$out .= makeFirstAuthorField($authors);
	$out .= "\t";
	$out .= makeLastAuthorField($authors);
	$out .= "\t";
	$out .= getAffiliations($xmlobj);
	$out .= "\t";
	$out .= getGrants($xmlobj);
	$out .= "\n";
	return $out;
}

function getGrants($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	
	if($ids = $xmlobj->xpath('//GrantList/Grant/GrantID')){
	} else{
		$ids[0] = "";
	}
	if($agencies = $xmlobj->xpath('//GrantList/Grant/Agency')){
	} else {
		$angencies[0]= "";
	}
	$num = count($agencies);
	$out = '';
	if ( $num == 0 ){
		return "NA";
	}
	for ($i =0 ; $i < $num ; $i++ ){
		$out = "";
		if(isset($agencies[$i])){
			$out .= $agencies[$i]."; " ;
		}
		if(isset($ids[$i])){
			$out .= $ids[$i];
		} 
		$out .= "| ";
	}
	$out = substr($out, 0 , -2);
	return $out;
}

function getJournalTitle($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	if($out = $xmlobj->xpath('//Journal/ISOAbbreviation')){

	} elseif($out = $xmlobj->xpath('//MedlineTA')) {

	} else {
		$out[0] = "NA";
	}
	return $out[0];
}

function getYear($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	$out = $xmlobj->xpath('//PubDate/Year');
	return $out[0];
}


function makeFirstAuthorField($authors){
	return $authors['fnames'][0]." ".$authors['lnames'][0];
}

function makeLastAuthorField($authors){
	$x = count($authors['lnames'])-1;
	return $authors['fnames'][$x]." ".$authors['lnames'][$x];
}

function makeAuthorFieldFullName($authors){
	$out = '';
	for ($x = 0 ; $x < count($authors['fnames']) ; $x++ ){
		
		$out .= $authors['fnames'][$x]." ";
		$out .= $authors['lnames'][$x];
		$out .= ', ';
	}
	$out = substr($out, 0 , -2);
	return $out;
}


function makeAuthorField($authors){
$out = "";
for ($x = 0 ; $x < count($authors['fnames']) ; $x++ ){
	
	$out .= $authors['lnames'][$x]." ".$authors['initials'][$x].", ";
}
$out = substr($out, 0 , -2);
return $out;
}




function getJournalName($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	return $xmlobj->xpath('//Journal/Title');
}

function getVolIssuePage($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	if($in = $xmlobj->xpath('//Journal//Volume')){
		$output = $in[0];
		$output .= ":";
	}else $output = '';

	if($in = $xmlobj->xpath('//Journal//Issue')){
		$output .= $in[0];
		$output .= ":";
	}
	
	$in = $xmlobj->xpath('//MedlinePgn');
	$output .= $in[0];
	return $output;
}



function getTitle($xmlobj){
	// echo "xmlobj type: ".gettype($xmlobj)."\n<br>";
	// $xmlstr = $xmlobj->asXML();
	// echo $xmlstr."\n<br>";
	// $xmlobj = new SimpleXMLElement($xmlstr);
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	$result = $xmlobj->xpath('//ArticleTitle');
	// echo gettype($result)."\n<br>";
	// echo count($result)."\n<br>";

	return $result[0];

}

function getAuthors($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	if($fnames = $xmlobj->xpath("//AuthorList/Author/ForeName")){
		// echo "fnames obtained\n<br>";
	} else echo "error getting fnames\n<br>";
	foreach ($fnames as $fname){
		// echo "fname: $fname \n<br>";
	}
	$lnames = $xmlobj->xpath('//AuthorList/Author/LastName');
	$initials = $xmlobj->xpath('//AuthorList/Author/Initials');
	// print_r($fnames);
	// echo "\n<br>";
	// print_r($lnames);
	// echo "\n<br>";
	// print_r($initials);
	// echo "\n<br>";
	// print_r($result);
	$authors = array();
	$authors['fnames'] = $fnames;
	$authors['lnames'] = $lnames;
	$authors['initials'] = $initials; 
	return $authors;
}


function getAffiliations($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	$affils = $xmlobj->xpath('//Article/Affiliation');
	$out = '';
	for($i = 0 ; $i < count($affils) ; $i++){
		$out .= $affils[$i]."; ";
	}
	$out = substr($out, 0 , -2);
	return $out;
}




function getPubmedID($xmlobj){
	$xmlobj = new SimpleXMLElement($xmlobj->asXML());
	if($result = $xmlobj->xpath('//ArticleIdList/ArticleId[@IdType="pubmed"]')){
		// echo "pubmedId obtained\n<br>";
	} else {
		$result = "NA";
	}
	// foreach ($result as $id){
	// 	echo $id."\n<br>";
	// }
	return $result[0];
}


function getCitations($title){
	echo "Title = $title<br>";
	$scholarQry = "python scholar.py --csv $title";
	echo "ScholarQry = $scholarQry<br>";
	exec($scholarQry, $output);
	echo "output length: ".count($output)."<br>";
	foreach($output as $res){
		echo "$res<br>";
		$arr = explode("|", $res);
		if ( levenshtein($arr[0], $title) < 5 ){
			echo "title  $title  res[0]".$arr[0]." citations ". $arr[2]."\n<br>";
			return $arr[2];
		} else {
			echo "Could not find title<br>";
			return "NA";
		}
	}
}

