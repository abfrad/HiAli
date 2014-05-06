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
		$subject;
		$object;
		$verb;
		$whdirective;
		
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
		// this will deter mine which node should subject refer to	
		$whdirective=$directive['x'];
		//if ($wh="how") 
		//{
		//echo "you said how!";
		
		//}
		//else 
		//{
			//looking for auxiliary, verb, subject, and object
			for ( $i = 1;  $i < $sentence_len;  $i++)
			{
				//if the word is aux
				
				if ($words[$i]=="is" or $words[$i]=="am" or $words[$i]=="are" or $words[$i]=="do" or $words[$i]=="does" or $words[$i]=="have" or $words[$i]=="has")
				{
					$aux=$words[$i];
					//determine what is after aux
					$postaux = $i+1;

					// if last word comes after the aux then its the object of sentence note: lent of sentence if +1 of last index
					//Section for one word after WH
					if ($postaux==$sentence_len - 1)
					{
						$object=$words[$postaux];
						//swapping I and You and preparing the right to be
						if ($object=="i") 
						{
							$subject="You";
							$aux="are";
						}
						else if ($object=="you")
						{
							$subject="I";
							$aux="am";
						}
						// when the subject is third party
						else
						{
						$subject=$object;
						}
						//holding WH directive asking the subject has relation to which node that holds the specific WH directive 
						$queryString2 ="MATCH (n)-[r:IS]->(m)".
						"WHERE n.val={val} AND m.keyword={keyword}".
						"RETURN r.val + ' ' + m.val";
						$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'val' => $subject , 'keyword' => $whdirective ));
						$answer_raw = $query2->getResultSet();
						if ($answer_raw) 
						{
							$answer= array();
							
							foreach ($answer_raw as $key=>$answer_lazy) 
							{
							$answer[$key]=$answer_lazy['x'];
							}
							//if they return has at least one row 
							if (isset ($key))
							{
								$randkey = rand(0, $key);
								echo $subject . " " . $answer[$randkey];
								break;	
							}
							//When the return is empty
							else
							{
								echo "I dont know :( ";
								break;
							}
						}
					}
			
				}
		
			}
		
		//}
	
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
	// in here direct will convert to keyword and return all of elements that match designed for greeting 
	$direct = $direct_set['0'];
	
	$queryString2 ="MATCH (n)".
	"WHERE n.keyword={keyword}".
    "RETURN n.val";
	
	$query2 = new Everyman\Neo4j\Cypher\Query($client, $queryString2 , array ( 'keyword' => $direct ));
	$word2 = $query2->getResultSet();
	
	$val= array();
	foreach ($word2 as $key=>$value) {
    $val[$key]=$value['x'];
	}
	$randkey = rand(0, $key);
	echo $val[$randkey];
	}
	
	*/
 
	
?>