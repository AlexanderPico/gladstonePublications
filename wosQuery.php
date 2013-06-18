<?php

// $debug = 0;

// $pmids = array(8560215,
// 8524329,
// 8524330,
// 7557376,
// 7630414,
// 7618104,
// 7760833,
// 7828852,
// 7537144,
// 7988568,
// 8205611,
// 8070657,
// 8159768,
// 8152919,
// 8127914,
// 7506421,
// 8288130,
// 8413255,
// 8477448,
// 7956088,
// 1497307,
// 1934071,
// 1840508,
// 1785140,
// 1657703,
// 1708110,
// 2200120,
// 2325646,
// 2180936,
// 1689810,
// 2406024,
// 1689074,
// 2296304,
// 2152134,
// 2813408,
// 2776215,
// 2769603,
// 2474761,
// 2657657,
// 2710110,
// 2463488,
// 2698831,
// 2927395,
// 3215519,
// 2839832,
// 2838822,
// 3336360,
// 3277178,
// 3319189,
// 3690664,
// 3684591,
// 2819822,
// 3607876,
// 3768955,
// 3092184,
// 3001053,
// 3101582,
// 3000613,
// 3907856,
// 3018547,
// 3997824,
// 2986140,
// 3923439,
// 6330571,
// 6722877,
// 6319025,
// 6383193,
// 6308573,
// 6407801,
// 6286143,
// 6280878,
// 7287815,
// 6786754,
// 6941283,
// 7471208,
// 290442,
// 642006,
// 826641,
// 1088826,
// 1081600,
// 806693,
// 23582327,
// 23562073,
// 23420878,
// 23338236,
// 23348417,
// 23290520,
// 23298208,
// 22941735,
// 22768117,
// 22585735,
// 22456498,
// 22138715,
// 22101429,
// 21918195,
// 22093101,
// 21844510,
// 21824866,
// 21552267,
// 21436399,
// 21155838,
// 21149710,
// 20980996,
// 20980633,
// 20870945,
// 20534524,
// 20303868,
// 19801985,
// 19682930,
// 19638212,
// 19635860,
// 19454688,
// 19451398,
// 19435888,
// 19252490,
// 20948671,
// 19144314,
// 19088190,
// 19006696,
// 18832714,
// 18832694,
// 18854153,
// 18585064,
// 18400185,
// 18354198,
// 17909561,
// 17673664,
// 17617602,
// 17890126,
// 17450126,
// 17351619,
// 17420014,
// 17339609,
// 17158962,
// 16979571,
// 16886063,
// 16849470,
// 16637008,
// 16702603,
// 16680145,
// 16622012,
// 16617008,
// 16473830,
// 16311599,
// 16267405,
// 16226507);

// wosGetCitations($pmids);


function wosGetCitations($arr){
	$filename = date('Y-m-d_H:i:s')."-WOSQueryResults.xml";
	$FILE = fopen($outdir."/".$filename, "w");
	$output = array();
	$qryAry = array();
	$pmids = array();
	for( $i = 0 ; $i <= count($arr) ; $i++ ){
		if(count($pmids) < 50 && $i < count($arr)){
			array_push($pmids, $arr[$i]);
		} else{
			// echo count($pmids)."<br>";
			array_push($qryAry, makeQueryString($pmids));
			$pmids = array();
		}
	}
	foreach($qryAry as $qry){
		// echo "$qry<br>";
		array_push($output, executeQuery($qry));
	}
	$arr = array();
	foreach($output as $out ){
		// echo "Output: $out \n";
		fwrite($FILE,$out);
		$outObj = new SimpleXMLElement($out);
		array_push($arr , $outObj);
	}
	// print_r($arr);
	$pmidToCiteArr = array();

	foreach($arr as $ar){
		echo "AR:\n";
		var_dump($ar);
		echo "AR:\n";
		foreach($ar->fn->map->map as $id){
			// $keys = array_keys($id);
			// echo gettype($keys)."\n";
			// foreach($keys as $key ){
				// echo $key."\n";
			// }
			echo "id\n";
			var_dump($id);
			echo "id\n";
			$pmid = "pmid-".$id['name'];
			$citations = $id->map->val->{2};
			$pmidToCiteArr[$pmid] = $citations;
		}
	}
	// var_dump($pmidToCiteArr);
	fclose($FILE);
	return $pmidToCiteArr;
}


function executeQuery($qry){
	$apiURL = "https://ws.isiknowledge.com/cps/xrpc";
	$options = array(
		CURLOPT_RETURNTRANSFER => true,
		CURLOPT_HEADER         => false,    
		CURLOPT_POST           => 1, 
		CURLOPT_VERBOSE        => 1,
		CURLOPT_POSTFIELDS     =>  $qry
	);
	$ch = curl_init($apiURL);
	curl_setopt_array($ch, $options);
	if( $content = curl_exec($ch) ){
		echo "curl success<br>";
	}else {
		die("curl failure"); 
	}
	// echo "Content: $content<br>";
	return $content;
}




function makeQueryString($arr){
$xmlstr = "<?xml version=\"1.0\" encoding=\"UTF-8\" ?> 
<request xmlns=\"http://www.isinet.com/xrpc41\" 
src=\"app.id=GladstonePub,env.id=Gladstone,partner.email=timothy.laurent@gladstone.ucsf.edu\" > 
 <fn name=\"LinksAMR.retrieve\"> 
 <list> 
<!-- WHO'S REQUESTING --> 
 <map> 
 </map>
<!-- WHAT'S REQUESTED --> 
 <map> 
 <list name=\"WOS\"> 
 <val>timesCited</val> 
 <val>ut</val> 
 <val>doi</val> 
 <val>sourceURL</val> 
 <val>citingArticlesURL</val> 
 <val>relatedRecordsURL</val> 
 </list> 
 </map> <!--end \"return_data\" --> 
<!-- LOOKUP DATA --> 
  <map> 
<!-- QUERY \"cite_id\" -->
";

foreach($arr as $pmid){
	$xmlstr .= "<map name=\"$pmid\">
	<val name=\"pmid\">$pmid</val>
	</map> 
	";
}
$xmlstr .= "</map></list></fn></request>";
return $xmlstr;
}
/*
<?xml version="1.0" encoding="UTF-8" ?> 
<request xmlns="http://www.isinet.com/xrpc41" 
src="app.id=PartnerApp,env.id=PartnerAppEnv,partner.email=EmailAddress" > 
 <fn name="LinksAMR.retrieve"> 
 <list> 
<!-- WHO'S REQUESTING --> 
 <map> 
 <val name="username">username</val> 
 <val name="password">test</val> 
 </map> 
<!-- WHAT'S REQUESTED --> 
 <map> 
 <list name="WOS"> 
 <val>timesCited</val> 
 <val>ut</val> 
 <val>doi</val> 
 <val>sourceURL</val> 
 <val>citingArticlesURL</val> 
 <val>relatedRecordsURL</val> 
 </list> 
 </map> <!--end "return_data" --> 
<!-- LOOKUP DATA --> 
 <map> 
<!-- QUERY "cite_id" --> 
 <map name="cite_id"> 
 <val name="atitle">article title string</val> 
 <val name="stitle">full journal title</val> 
 <val name="issn">1234-5678</val> 
 <val name="vol">12</val> 
 <val name="issue">12</val> 
 <val name="year">2008</val> 
 <val name="doi">doi_string</val> 
 <val name="ut">isi_ut_num</val> 
 <val name="spage">1234</val> 
<!-- authors list can be used to specify multiple authors --> 
 <list name="authors"> 
 <val>First, AU</val> 
 <val>Second, AU</val> 
 <val>Third, AU</val> 
 </list> 
  </map> <!-- end of cite_id--> 
<-- QUERY "cite_id" 
 <map name="cite_id"> 
 ... 
 </map> 
--> 
 </map> <!-- end of citations --> 
 </list> 
 </fn> 
</request> 
*/