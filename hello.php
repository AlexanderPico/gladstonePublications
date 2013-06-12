<?php


class User{
	public $fName;
	public $lName;
	public $ucsfID;
	public $email;
}
$users = array();

$user = new User();
$user->fName = "Sheng";
// echo $sding->fName;
$user->lName = "Ding";
$user->email = "sDing@gladstone.ucsf.edu";

$users[$user->email] = $user;

echo $users["sDing@gladstone.ucsf.edu"]->lName;



// 
// $xmlFile = "bio.xml";
// $xml = simplexml_load_file($xmlFile);

// if ($xml){
// 	echo "xml ". $xml->getName() . "<br>";

// }

// foreach ($xml->children() as $child)
//   {
//   // echo "Child node: " . $child . "<br />";
//   }


// $xmlDoc = new DOMDocument();
// $xmlDoc->load("bio.xml");
// $x = $xmlDoc->documentElement;

// foreach ($x->childNodes AS $item)
//   {
//   print $item->nodeName . " = " . $item->nodeValue . "<br>";
//   }