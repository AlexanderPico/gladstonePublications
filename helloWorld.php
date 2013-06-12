<?php
// require "UCSFtest.php";

$investigators = array( 
	array( "fname" => "Deepak",
	"lname" => "Srivastava",
	"ucsfid" => "24044604",
	"email" => "dsrivastava@gladstone.ucsf.edu",
	),
	array("fname" => "Katerina",
	"lname" => "Akassoglou",
	"ucsfid" => "24652877",
	"email" => "kakassoglou@gladstone.ucsf.edu",
	),
	array( "fname" => "Lennart",
	"lname" => "Mucke",
	"ucsfid" => "27557024",
	"email" => "lmucke@gladstone.ucsf.edu",
	),

); 


// echo "test<br>";


for ($x=0 ; $x< count($investigators) ; $x++ ){
	echo $investigators[$x]["fname"]." ";
	echo $investigators[$x]["lname"]."<br>";
	echo $investigators[$x]["ucsfid"]."<br>";
	echo $investigators[$x]["email"]."<br>";
	
	$investigators[$x] = queryProfiles($investigators[$x]);


}




for ($x=0 ; $x< count($investigators) ; $x++ )
{
	// echo &$guy."<br>";
	// print_r($guy);
	echo "<br>";
	echo $investigators[$x]["fname"]." ";
	echo $investigators[$x]["lname"]."<br>";
	echo gettype($investigators[$x]["pubmedURLs"])."<br>";
	$keys  = ($investigators[$x]['pubmedURLs']);
	foreach ( $keys as $pub){
		$pubmedQuery = $pub."?=text&report=xml&format=text";
		echo $pubmedQuery."<br>";
		if ( $handle = fopen($pubmedQuery, 'r')){
			$xml = stream_get_contents($handle);
			echo $xml."<br>";
			// echo "json type =\t".gettype($json)."<br>";
		}
	}
}

function queryProfiles( $guy ){
	$person_id  = (int) ($guy["ucsfid"]/10) + 2569307;
	echo $person_id."<br>";

	$apiQuery = "http://profiles.ucsf.edu/CustomAPI/v1/JSONProfile.aspx?source=Gladstone&Person=".$person_id."&publications=full";
	echo $apiQuery."<br>";
	if ( $handle = fopen($apiQuery, 'r')){
		$json = stream_get_contents($handle);
		// echo $json."<br>";
		// echo "json type =\t".gettype($json)."<br>";
	}
	$guy["PubJson"] = json_decode( $json , $assoc = true);

	$guy["pubmedURLs"] = array();

	$guy["pubWOpubmedID"] = array();


	$keys1 = array_keys ( $guy["PubJson"]["Profiles"][0]['Publications']);

		// [0]['PublicationSource'][0]['PublicationSourceURL'] );

	foreach ( $keys1 as $key ){
		// echo $key."<br>";
		if (isset($guy["PubJson"]["Profiles"][0]['Publications'][$key]['PublicationSource'][0]['PublicationSourceURL'] )){
			$URL = $guy["PubJson"]["Profiles"][0]['Publications'][$key]['PublicationSource'][0]['PublicationSourceURL'];
			// echo $URL."<br>";
			array_push($guy["pubmedURLs"], $URL); 
		}
		else {
			array_push($guy["pubWOpubmedID"] , $guy["PubJson"]["Profiles"][0]['Publications'][$key] );
		}

	}
	echo gettype($guy["pubmedURLs"])."<br>";

	$keys2 = array_keys ($guy['pubmedURLs']);
	foreach ( $keys2 as $key ){
		// echo $guy['pubmedURLs'][$key]."<br>";
	
	}
	$keys3 = array_keys ($guy["pubWOpubmedID"]);
	foreach ( $keys3 as $key ){
		// print_r($guy["pubWOpubmedID"][$key]);
		// echo "<br>";
	}
	// echo "<br><br><br>";
	// print_r($guy);
	return $guy;
}