<?php
		//load neo4j REST API
		require('vendor/autoload.php');
		// connection to neo4j server and database
		$client = new Everyman\Neo4j\Client('localhost', 7474);
		
		$message =$_GET['message'];
		$message = strtolower($message);
		
		//sort out incoming message
	
		$words = explode ( " " , $message , 20) ;
		$sentence_len= sizeof($words);
		
		
		
		//INFORMATIVE QUESTIONS
		if ( $words[0]=="how" or $words[0]=="who" or $words[0]=="where" or $words[0]=="what" or $words[0]=="when" )
		{
			$wh=$words[0];
			$aux;
			$raw_subject;
			$subject;
			$object;
			$verb;
			$whdirective;
			// Will be used to pass a phrase and be matched in relationship properties 
			$key_phrase="";
			//spaced key phrase
			$skey_phrase="";
			//getting directive of WH
			$queryString ="MATCH (n)".
			"WHERE n.val={val}".
			"RETURN n.direct";
			$query = new Everyman\Neo4j\Cypher\Query($client, $queryString , array ( 'val' => $wh ));
			$directive_raw = $query->getResultSet();
			// lent counts number of rows in result 
			$lent=0;
			foreach ($directive_raw as $directive) 
			{
				 $directive['x'];
				 $lent++;
			}
			// this will determine which node should subject refer to	
			$whdirective=$directive['x'];
			
				//looking for auxiliary, verb, subject, and object
				for ( $i = 1;  $i < $sentence_len;  $i++)
				{
					//if the word is auxiliary
					
					if ($words[$i]=="is" or $words[$i]=="am" or $words[$i]=="are" or $words[$i]=="do" or $words[$i]=="does" )
					{
						$aux=$words[$i];
						//determine what is after auxiliary
						$postaux = $i+1;

						// if last word comes after the auxiliary then its the object of sentence note: lent of sentence if +1 of last index
						//Section for one word after WH
						if ($postaux==$sentence_len - 1)
						{
							$raw_subject=$words[$postaux];
							//I call the incoming subject "raw_subject" if you ask "Who are you"  you is raw_subject and will be converted into subject
							//subject conversion
								if ($raw_subject=="you")
								{
									$subject="I";
								}
								else if ($raw_subject=="i")
								{
									$subject="you";
								}
								else
								{
									$subject=$raw_subject;
								}
							//Auxiliary fix
								if ($aux=="am")
									$aux="are";
								else if ($aux=="are" and $raw_subject=="you")
									$aux="am";
							//Using auxiliary as key_phrase
							//$key_phrase=$aux;
							// when the subject is third party
							
							//holding WH directive asking the subject has relation to which node that holds the specific WH directive 
							$queryString2 ="MATCH (n)-[r:IS]->(m)".
							"WHERE n.val={val} AND r.val={rval} AND m.keyword={keyword}".
							"RETURN m.val";
							$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'val' => $subject , 'rval' => $key_phrase , 'keyword' => $whdirective  ));
							$answer_raw = $query2->getResultSet();
						
							
							//fetch the result from the REST API object
							foreach ($answer_raw as $key=>$answer) 
							{
							$answer['x'];
							}
							//Fetching preposition  
							$queryString2 ="MATCH (n)-[r:IS]->(m)".
							"WHERE n.val={val} AND m.keyword={keyword} AND r.val={rval}".
							"RETURN r.prep";
							$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'val' => $subject , 'keyword' => $whdirective , 'rval' => $key_phrase ));
							$prep_raw = $query2->getResultSet();
							//fetch the result from the REST API object
							foreach ($prep_raw as $prep) 
							{
							$prep['x'];
							}
							//if the return has at least one row 

							if (isset ($key))
							{
								
								echo $subject . " ".$aux. " " .$prep['x']. " " . $answer['x'];
								break;	
							}
							//When the return is empty
							else
							{
								//echo $subject. $aux ;
								echo "I dont know but I can <a href='https://www.google.com/search?q=$message' target='_blank' >Google</a> :) ";
								break;
							}
							
						}
						
						//If there are more than one words after auxiliary 
						else 
						{
							
							//assigning raw_subejct , what ever that is following the auxiliary is considered to be subject
							$raw_subject=$words[$postaux];
							//Subject conversion
							if ($raw_subject=="you")
							{
								$subject="I";
							}
							else if ($raw_subject=="i")
							{
								$subject="you";
							}
							else
							{
								$subject=$raw_subject;
							}
							//Auxiliary fix
							if ($aux=="are")
								$aux="am";
							else if ($subject=="you" and $aux=="am")
								$aux="are";
							//do and does aren't mentioned in statements 
							else if ($aux=="do" or $aux=="does")
								$aux="";
								
							// continue after auxiliary when the next word is not the last one 
							for ($x=$postaux+1 ; $x< $sentence_len ; $x++)
							{
								//to add space between words and not in the beginning 
								if ($skey_phrase!="")
								$skey_phrase=$skey_phrase." ";
								$skey_phrase=$skey_phrase.$words[$x];
								$key_phrase=$key_phrase.$words[$x];		
							}
							
							$queryString2 ="MATCH (n)-[r:IS]->(m)".
							"WHERE n.val={val} AND m.keyword={keyword} AND r.val={rval}".
							"RETURN m.val";
							$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'val' => $subject , 'keyword' => $whdirective , 'rval' => $key_phrase ));
							$answer_raw = $query2->getResultSet();
							$answer= array();
							
							//fetch the result from the REST API object
							foreach ($answer_raw as $key=>$answer_lazy) 
							{
								$answer[$key]=$answer_lazy['x'];
							}
							
							if (isset ($key))
							{
								//spaced key phrase is used in output
								echo $subject ." ".$aux ." ".$skey_phrase ." " . $answer[0];
								break;	
							}
							else
							{
								echo "I dont know but I can <a href='https://www.google.com/search?q=$message' target='_blank' >Google</a> :) ";
								break;
							}
						
						}
				
					}
			
				}
			
			//}
		
		}
		
		//YESNO QUESTIONS
		else if ($words[0]=="is" or $words[0]=="am" or $words[0]=="are" or $words[0]=="do" or $words[0]=="does" )
		
		{
		
			$aux=$words[0];
			$raw_subject=$words[1];
			$raw_object=$words[$sentence_len-1];
			$subject;
			$object;
			//Subject Conversion
			if ($raw_subject=="you")
			{
				$subject="I";
			}
			else if ($raw_subject=="i")
			{
				$subject="you";
			}
			else
			{
				$subject=$raw_subject;
			}
			//Object Conversion note: inside the database I and you are still object and subject ,, being object is the defined by direction of the relationship
			if ($raw_object=="you")
			{
				$object="I";
			}
			else if ($raw_object=="me")
			{
				$object="you";
			}
			else
			{
				$object=$raw_object;
			}
			//Auxiliary fix
			if ($aux=="are")
				$aux="am";
			else if ($subject=="you" and $aux=="am")
				$aux="are";
			 
			// Will be used to pass a phrase and be matched in relationship properties 
			$key_phrase="";
			//spaced key phrase
			$skey_phrase="";
			
			//looking for subject, key_phrase and object
			for ( $i = 2;  $i < $sentence_len-1;  $i++)
			{
				
				$key_phrase=$key_phrase.$words[$i];
				
			}
				
			//holding Subject, Object and relationship key_phrase between them we will check of the existence of the record
			$queryString2 ="MATCH (n)-[r:IS]->(m)".
			"WHERE n.val={val} AND m.val={oval} AND r.val={rval}".
			"RETURN r.val";
			$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'val' => $subject , 'oval' => $object , 'rval' => $key_phrase ));
			$answer_raw = $query2->getResultSet();
			//fetch the result from the REST API object
			foreach ($answer_raw as $key=>$answer) 
			{
				$answer['x'];
			}
			
			
			if (isset ($key))
			{
				//spaced key phrase is used in output
				echo "Yes ". $subject ." ".$aux ;
				
			}
			else
			{
				echo "No ". $subject ." ".$aux . " not" ;
				
			}
			
						
		
		}
		
		//STATEMENTS
		else 
		
		{
			if ( $sentence_len==1)
			{	
			
				$direct = $words[0];
				$queryString ="MATCH (n)".
				"WHERE n.val={val}".
				"RETURN n.direct";
				$query = new Everyman\Neo4j\Cypher\Query($client, $queryString , array ( 'val' => $direct ));
				$directive_raw = $query->getResultSet();
				// lent counts number of rows in result 
				$lent=0;
				foreach ($directive_raw as $directive) 
				{
					 $directive['x'];
					 $lent++;
				}
				// in here direct will convert to keyword and return all of elements that match designed for greeting 
				$direct = $directive['x'];
				
				$queryString2 ="MATCH (n)".
				"WHERE n.keyword={keyword}".
				"RETURN n.val";
				
				$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'keyword' => $direct ));
				$word2 = $query2->getResultSet();
				
				$val= array();
				foreach ($word2 as $key=>$value) 
				{
					$val[$key]=$value['x'];
				}
				
				
				if (isset($key))
				{
					$randkey = rand(0, $key);
					echo $val[$randkey];
				}
			}
		}
		
		/*
		//getting directive of temp
		$queryString ="MATCH (n)".
		"WHERE n.val={val}".
		"RETURN n.direct";
		$query = new Everyman\Neo4j\Cypher\Query($client, $queryString , array ( 'val' => $words[0] ));
		$directive_raw = $query->getResultSet();
		// lent counts number of rows in result 
		$lent=0;
		 foreach ($directive_raw as $direct_set) 
		{
		 $direct_set['x'];
		 $lent++;
		}
	

	if ($lent==1) {
	
	}
	
	*/
 
	
?>