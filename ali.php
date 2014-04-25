<?php
	//load neo4j REST API
    require('vendor/autoload.php');
	// connection to neo4j server and database
	$client = new Everyman\Neo4j\Client('localhost', 7474);
	
	
	$message =$_GET['message'];
	$words = explode ( " " , $message , 20) ;
	
	$word=$client-> getNodes('val' , 'Hi');
	$cat=$word->getProperty('cat');
	
	
	
	$lengt =0;
	 foreach ($words as $wor )
	 {
	$lengt ++;
	 }
 echo "your $cat ed ";	
//$word = $client->makeNode();
//$word->setProperty('word', 'eat')
//    ->save();


	
?>