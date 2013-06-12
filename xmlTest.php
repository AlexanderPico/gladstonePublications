<?php

require 'xmlExample.php';

$xmlobj = new SimpleXMLElement($xmlstr);
// $authors = getAuthors($xmlobj);
// $title = getTitle($xmlobj);

// echo getJournalTitle($xmlobj);
// echo '<br>';
// echo $title;
// echo '<br>';
// echo getVolIssuePage($xmlobj);
// echo '<br>';
// echo getYear($xmlobj);
// echo '<br>';
// echo getPubmedID($xmlobj);
// echo '<br>';
// echo getCitations($title);
// echo '<br>';
// echo makeAuthorField($authors);
// echo '<br>';
// echo makeAuthorFieldFullName($authors);
// echo '<br>';
// echo makeFirstAuthorField($authors);
// echo '<br>';
// echo makeLastAuthorField($authors);
// echo '<br>';
// echo getAffiliations($xmlobj);
// echo '<br>';
// echo getGrants($xmlobj);
$outdir = "out";
$filename = date('Y-m-d_H:i:s')."-pubTable.tsv";
if (!file_exists($outdir)) {
    mkdir($outdir);
}


$FILE = fopen($outdir."/".$filename, "w");

fwrite($FILE, makeheader());
fwrite($FILE, makerow($xmlobj));



fclose($FILE);

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
	$ids = $xmlobj->xpath('//GrantList/Grant/GrantID');
	$agencies = $xmlobj->xpath('//GrantList/Grant/Agency');
	$num = count($agencies);
	$out = '';
	if ( $num == 0 ){
		return "NA";
	}
	for ($i =0 ; $i < $num ; $i++ ){
		$out .= $agencies[$i]."; ".$ids[$i]."| ";
	}
	$out = substr($out, 0 , -2);
	return $out;
}

function getJournalTitle($xmlobj){
	$out = $xmlobj->xpath('//Journal/ISOAbbreviation');
}

function getYear($xmlobj){
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
	return $xmlobj->xpath('//Journal/Title');
}

function getVolIssuePage($xmlobj){
	$in = $xmlobj->xpath('//Journal//Volume');
	$output = $in[0];
	$output .= ":";
	$in = $xmlobj->xpath('//Journal//Issue');
	$output .= $in[0];
	$output .= ":";
	$in = $xmlobj->xpath('//MedlinePgn');
	$output .= $in[0];
	return $output;
}



function getTitle($xmlobj){
$result = $xmlobj->xpath('//ArticleTitle');

	return $result[0];

}

function getAuthors($xmlobj){
	$fnames = $xmlobj->xpath('//AuthorList/Author/ForeName');
	// foreach ($fnames as $fname){
	// 	echo "$fname <br>";
	// }
	$lnames = $xmlobj->xpath('//AuthorList/Author/LastName');
	$initials = $xmlobj->xpath('//AuthorList/Author/Initials');
	// print_r($fnames);
	// echo "<br>";
	// print_r($lnames);
	// echo "<br>";
	// print_r($initials);
	// echo "<br>";
	// print_r($result);
	$authors = array();
	$authors['fnames'] = $fnames;
	$authors['lnames'] = $lnames;
	$authors['initials'] = $initials; 
	return $authors;
}


function getAffiliations($xmlobj){
	$affils = $xmlobj->xpath('//Article/Affiliation');
	$out = '';
	for($i = 0 ; $i < count($affils) ; $i++){
		$out .= $affils[$i]."; ";
	}
	$out = substr($out, 0 , -2);
	return $out;
}




function getPubmedID($xmlobj){
	$result = $xmlobj->xpath('//ArticleIdList/ArticleId[@IdType="pubmed"]');
	// foreach ($result as $id){
	// 	echo $id."<br>";
	// }
	return $result[0];
}


function getCitations($title){
	exec("python scholar.py --csv $title", $output);
	foreach($output as $res){
		$arr = explode("|", $res);
		if ( levenshtein($arr[0], $title) < 5 ){
			// echo "title  $title  res[0]".$arr[0]." citations ". $arr[2]."<br>";
			return $arr[2];
		} else {
			return "Error Obtaining Citations";
		}
	}
}

