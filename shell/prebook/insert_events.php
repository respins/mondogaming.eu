<?php include_once('../db.php');
$xr = "SELECT serial FROM `www_token` ";
$result = mysqli_query($conn, $xr);
$c_row = $result->fetch_assoc();
$tk = $c_row['serial'];

mysqli_query($conn,"DELETE FROM af_pre_bet_events WHERE is_active = '1' AND feat = '0'");
mysqli_query($conn,"DELETE FROM af_pre_bet_events_cats WHERE yn IS NULL");
mysqli_query($conn,"DELETE FROM af_pre_bet_options");


//insert cricket bet365

$url = 'https://api.betsapi.com/v1/bet365/upcoming?token='.$tk.'&sport_id=3';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$data = curl_exec($ch) or die(curl_error($ch));
if ($data === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error occured during curl exec. Additioanl info: ' . var_export($info));
}
curl_close($ch);
$obj = json_decode($data, true);  

//echo $obj['results']['id'];
foreach($obj['results'] as $result){
    $bet_event_id = $result['id'];echo '</br>';
	$deadline = $result['time'];echo '</br>';
	$event_id = $result['league']['id'];echo '</br>';
	$event_name = $result['league']['name'];echo '</br>';
	$e_name =  $result['home']['name'].' - '. $result['away']['name'];
             $ev_name = addslashes($e_name);
             if (!empty($ev_name)){
            $bet_event_name = $ev_name;
             } else{
             $bet_event_name = addslashes($event_name);      
             };
	$bradar = 	$result['our_event_id'];
	 /*
			 if($sport_id == '22' && $event_name == 'Test Matches'){
             $deadline = strtotime($b[$i]['Date']) - 345600; //432000;
			 }else{
			 $deadline = strtotime($b[$i]['Date']);
			 }			 			 
			 
			 $date = new DateTime(); 
			 $now = $date->getTimestamp();
			 */
			 
			 ///////////////////INSERT STARTS//////////////////////
			 //insert sf_events
			 $bet_events = 'af_pre_bet_events';
			 $af_bet_events = "INSERT IGNORE INTO $bet_events (bet_event_id, bradar, bet_event_name, deadline, is_active, event_id, event_name, spid, cc, sname) VALUES('$bet_event_id', '$bradar', '$bet_event_name', '$deadline', '1', '$event_id', '$event_name', '3', 'World', 'Cricket')";
			 
			 if ($conn->query($af_bet_events) === TRUE) {
				// echo 'inserted';
			 } else {
				 echo "Error: " . $af_bet_events . "<br>" . $conn->error;		 
			 }

			 
	
    $url='https://api.betsapi.com/v2/bet365/prematch?token='.$tk.'&FI='.$bet_event_id;
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch) or die(curl_error($ch));
    if ($data === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($ch);
   // var_dump($data);
    $result = json_decode($data, true);
    foreach ($result['results'] as $j) {
		$b = $j['main']['sp'];
        foreach($b as $ca){
			
			 //var_dump($ca);
			 //INSERT BET EVENTS CATS
			  
			  //echo $ca['name'];
              //for($j =0; $j<count($c); $j++){
				  $c_sort = 1;
				  $bet_event_cat_id = $ca['id']+$bet_event_id;
				  $bet_event_cat_name = addslashes($ca['name']);

			 $cat_events = 'af_pre_bet_events_cats';
			 $c_count = "SELECT count(*) as count FROM $cat_events WHERE bet_event_id = '$bet_event_id'";
			 $c_result = $conn->query($c_count);
                            if ($c_result->num_rows > 0) {
                                $c_row = $c_result->fetch_assoc();
                                $c_sort = $c_row['count']+1;
                            } else {
                                $c_sort = 1;
                            }
							
			 $af_cat_events = "INSERT IGNORE INTO $cat_events (bet_event_cat_id, c_sort, bet_event_id, bet_event_cat_name, spid) VALUES('$bet_event_cat_id', '$c_sort', '$bet_event_id', '$bet_event_cat_name', '3')";
			 
			 if ($conn->query($af_cat_events) === TRUE) {
				// echo 'inserted';
			 } else {
				 echo "Error: " . $af_cat_events . "<br>" . $conn->error;		 
			 }
				  
 
			 
			 
			 //INSERT BET OPTIONS
			 
			   $d = $ca['odds'];
               foreach ($d as $e) {
				   $bet_option_id = $e['id'];
                   $odd_header = addslashes($e['header']);
                   $oname = addslashes($e['name']);
				   $team_header = addslashes($e['team']);
				   if(!empty($odd_header && $team_header)){
				     $bet_option_name = $odd_header.' '.$oname.' - '.$team_header;
				    }else if(!empty($odd_header)){
					 $bet_option_name = $odd_header.' '.$oname;
					}
					else{
				    $bet_option_name = $oname;
				    }
                   $bet_option_odd = $e['odds'];
				   
			 $opt_events = 'af_pre_bet_options';
			 $o_count = "SELECT count(*) as count FROM $opt_events WHERE bet_event_cat_id = '$bet_event_cat_id'";
			 $o_result = $conn->query($o_count);
                            if ($o_result->num_rows > 0) {
                                $o_row = $o_result->fetch_assoc();
                                $o_sort = $o_row['count']+1;
                            } else {
                                $o_sort = 1;
                            }
			//if($ovisible == 'Visible'){

				
			 $af_opt_events = "INSERT INTO $opt_events (bet_option_id, o_sort, bet_option_name, bet_option_odd, status, bet_event_cat_id) VALUES('$bet_option_id', '$o_sort', '$bet_option_name', '$bet_option_odd', 'awaiting', '$bet_event_cat_id') ON DUPLICATE KEY UPDATE bet_option_odd = '$bet_option_odd'";
			 
			 if ($conn->query($af_opt_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_opt_events . "<br>" . $conn->error;		 
			 }
				   

			    //} //if ovisible
			   } //bet options markets
			   // if visible..
		      //} //if category is
			  //} //cat markets
			 
		}
	}
};




	//events insert soccer page1
	
$url = 'https://api.betsapi.com/v1/bwin/prematch?token='.$tk.'&sport_id=4&page=1';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$data = curl_exec($ch) or die(curl_error($ch));
if ($data === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error occured during curl exec. Additioanl info: ' . var_export($info));
}
curl_close($ch);
$obj = json_decode($data, true);   
foreach($obj['results'] as $result){
    $output = $result['Id'];
    $url='https://api.betsapi.com/v1/bwin/event?token='.$tk.'&event_id='.$output;
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch) or die(curl_error($ch));
    if ($data === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($ch);
   // var_dump($data);
    $data = utf8_encode($data);
    $json = $data;	
    $a = explode('string', $json);
    for($k =0; $k<count($a); $k++){
        $w = trim($a[$k]);
        if(count($a) > 1){
            $w = strstr($w, '"');
        }
        $json = trim($w, '"');
        $obj = json_decode($json, true);
        $b = $obj['results'];
        for($i =0; $i<count($b); $i++){
            if(!empty($b[$i]['Id'])){
				
             $bet_event_id = $b[$i]['Id'];
			 $bradar = $b[$i]['BetRadarId'];
             $sport_id = $b[$i]['SportId'];
			 $sname = $b[$i]['SportName'];			 
             $event_id = $b[$i]['LeagueId'];
			 $cc = $b[$i]['RegionName'];
			 
             $empt = $b[$i]['HomeTeam']; 
             $event_name = addslashes($b[$i]['LeagueName']);
			 
             $e_name =  $b[$i]['HomeTeam'].' - '. $b[$i]['AwayTeam'];
             $ev_name = addslashes($e_name );
             if (!empty($empt)){
             $bet_event_name = $ev_name;
             } else{
             $bet_event_name = addslashes($b[$i]['LeagueName']);      
             }
			 $deadline = strtotime($b[$i]['Date']);
			 $date = new DateTime(); 
			 $now = $date->getTimestamp();
			 
			 ///////////////////INSERT STARTS//////////////////////
			 //insert sf_events
			 $bet_events = 'af_pre_bet_events';
			 $af_bet_events = "INSERT IGNORE INTO $bet_events (bet_event_id, bradar, bet_event_name, deadline, is_active, event_id, event_name, spid, cc, sname) VALUES('$bet_event_id', '$bradar', '$bet_event_name', '$deadline', '1', '$event_id', '$event_name', '$sport_id', '$cc', '$sname')";
			 
			 if ($conn->query($af_bet_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_bet_events . "<br>" . $conn->error;		 
			 }

			 
			 
			 
			 //INSERT BET EVENTS CATS
			  
			  $c = $b[$i]['Markets'];
              for($j =0; $j<count($c); $j++){
				  $c_sort = 1;
				  $bet_event_cat_id = $c[$j]['id'];
				  $bet_event_cat_name = addslashes($c[$j]['name']['value']);
				  $visible = $c[$j]['visibility'];			  
				  
				  if( $bet_event_cat_name == "Match Result" || $bet_event_cat_name == "Total Goals - Over/Under" || $bet_event_cat_name == "Double Chance" || $bet_event_cat_name == "Both Teams to Score" || $bet_event_cat_name == "Draw no bet" || $bet_event_cat_name == "Away No Bet" || $bet_event_cat_name == "Home No Bet" || $bet_event_cat_name == "Handicap 0:1" || $bet_event_cat_name == "Handicap 0:2" || $bet_event_cat_name == "Handicap 1:0" || $bet_event_cat_name == "Handicap 2:0" || $bet_event_cat_name == "Half Time result" || $bet_event_cat_name == "Half Time Double Chance" || $bet_event_cat_name == "1st Goal - 1st Half" || $bet_event_cat_name == "Total Goals O/U - 1st Half" || $bet_event_cat_name == "Total Goals O/U - 2nd Half" || $bet_event_cat_name == "Correct Score (Regular Time)" || $bet_event_cat_name == "1st Goal" || $bet_event_cat_name == "Team 1 to Score" || $bet_event_cat_name == "Team 2 to Score" || $bet_event_cat_name == "Both Teams to Score 1st Half" || $bet_event_cat_name == "Number of Corners (Regular Time)" || $bet_event_cat_name == "Red Card - Yes/No" || $bet_event_cat_name == "Total Goals - Exact"){

				  
				 
			 $cat_events = 'af_pre_bet_events_cats';
			 $c_count = "SELECT count(*) as count FROM $cat_events WHERE bet_event_id = '$bet_event_id'";
			 $c_result = $conn->query($c_count);
                            if ($c_result->num_rows > 0) {
                                $c_row = $c_result->fetch_assoc();
                                $c_sort = $c_row['count']+1;
                            } else {
                                $c_sort = 1;
                            }
			if($visible == 'Visible'){				
			 $af_cat_events = "INSERT IGNORE INTO $cat_events (bet_event_cat_id, c_sort, bet_event_id, bet_event_cat_name, spid) VALUES('$bet_event_cat_id', '$c_sort', '$bet_event_id', '$bet_event_cat_name', '$sport_id')";
			 
			 if ($conn->query($af_cat_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_cat_events . "<br>" . $conn->error;		 
			 }
				  
 
			 
			 
			 //INSERT BET OPTIONS
			 
			 $d = $c[$j]['results'];
               foreach ($d as $e) {
				   $bet_option_id = $e['id'];
                   $oname = $e['name']['value'];
                   $bet_option_name = addslashes($oname );
                   $bet_option_odd = $e['odds'];
				   $ovisible = $e['visibility'];
				   
				   
				   
			 $opt_events = 'af_pre_bet_options';
			 $o_count = "SELECT count(*) as count FROM $opt_events WHERE bet_event_cat_id = '$bet_event_cat_id'";
			 $o_result = $conn->query($o_count);
                            if ($o_result->num_rows > 0) {
                                $o_row = $o_result->fetch_assoc();
                                $o_sort = $o_row['count']+1;
                            } else {
                                $o_sort = 1;
                            }
			if($ovisible == 'Visible'){

				
			 $af_opt_events = "INSERT INTO $opt_events (bet_option_id, o_sort, bet_option_name, bet_option_odd, status, bet_event_cat_id) VALUES('$bet_option_id', '$o_sort', '$bet_option_name', '$bet_option_odd', 'awaiting', '$bet_event_cat_id') ON DUPLICATE KEY UPDATE bet_option_odd = '$bet_option_odd'";
			 
			 if ($conn->query($af_opt_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_opt_events . "<br>" . $conn->error;		 
			 }
				   

			    } //if ovisible
			   } //bet options markets
			  } // if visible..
		      } //if category is
			  } //cat markets
			 
			}
		}
	}
};











//events insert soccer page2
	
$url = 'https://api.betsapi.com/v1/bwin/prematch?token='.$tk.'&sport_id=4&page=2';
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
curl_setopt($ch, CURLOPT_TIMEOUT, 100);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 100);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
$data = curl_exec($ch) or die(curl_error($ch));
if ($data === false) {
    $info = curl_getinfo($ch);
    curl_close($ch);
    die('error occured during curl exec. Additioanl info: ' . var_export($info));
}
curl_close($ch);
$obj = json_decode($data, true);   
foreach($obj['results'] as $result){
    $output = $result['Id'];
    $url='https://api.betsapi.com/v1/bwin/event?token='.$tk.'&event_id='.$output;
    $ch = curl_init($url);
	curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    $data = curl_exec($ch) or die(curl_error($ch));
    if ($data === false) {
        $info = curl_getinfo($ch);
        curl_close($ch);
        die('error occured during curl exec. Additioanl info: ' . var_export($info));
    }
    curl_close($ch);
   // var_dump($data);
    $data = utf8_encode($data);
    $json = $data;	
    $a = explode('string', $json);
    for($k =0; $k<count($a); $k++){
        $w = trim($a[$k]);
        if(count($a) > 1){
            $w = strstr($w, '"');
        }
        $json = trim($w, '"');
        $obj = json_decode($json, true);
        $b = $obj['results'];
        for($i =0; $i<count($b); $i++){
            if(!empty($b[$i]['Id'])){
				
             $bet_event_id = $b[$i]['Id'];
             $bradar = $b[$i]['BetRadarId'];			
             $sport_id = $b[$i]['SportId'];
			 $sname = $b[$i]['SportName'];	 
             $event_id = $b[$i]['LeagueId'];
			 $cc = $b[$i]['RegionName'];
			 
             $empt = $b[$i]['HomeTeam']; 
             $event_name = addslashes($b[$i]['LeagueName']);
			 
             $e_name =  $b[$i]['HomeTeam'].' - '. $b[$i]['AwayTeam'];
             $ev_name = addslashes($e_name );
             if (!empty($empt)){
             $bet_event_name = $ev_name;
             } else{
             $bet_event_name = addslashes($b[$i]['LeagueName']);      
             }
			 $deadline = strtotime($b[$i]['Date']);		 			 
			 
			 $date = new DateTime(); 
			 $now = $date->getTimestamp();
			 
			 ///////////////////INSERT STARTS//////////////////////
			 //insert sf_events
			 $bet_events = 'af_pre_bet_events';
			 $af_bet_events = "INSERT IGNORE INTO $bet_events (bet_event_id, bradar, bet_event_name, deadline, is_active, event_id, event_name, spid, cc, sname) VALUES('$bet_event_id', '$bradar', '$bet_event_name', '$deadline', '1', '$event_id', '$event_name', '$sport_id', '$cc', '$sname')";
			 
			 if ($conn->query($af_bet_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_bet_events . "<br>" . $conn->error;		 
			 }

			 
			 
			 
			 //INSERT BET EVENTS CATS
			  
			  $c = $b[$i]['Markets'];
              for($j =0; $j<count($c); $j++){
				  $c_sort = 1;
				  $bet_event_cat_id = $c[$j]['id'];
				  $bet_event_cat_name = addslashes($c[$j]['name']['value']);
				  $visible = $c[$j]['visibility'];
				  
				  
				  
				  if( $bet_event_cat_name == "Match Result" || $bet_event_cat_name == "Total Goals - Over/Under" || $bet_event_cat_name == "Double Chance" || $bet_event_cat_name == "Both Teams to Score" || $bet_event_cat_name == "Draw no bet" || $bet_event_cat_name == "Away No Bet" || $bet_event_cat_name == "Home No Bet" || $bet_event_cat_name == "Handicap 0:1" || $bet_event_cat_name == "Handicap 0:2" || $bet_event_cat_name == "Handicap 1:0" || $bet_event_cat_name == "Handicap 2:0" || $bet_event_cat_name == "Half Time result" || $bet_event_cat_name == "Half Time Double Chance" || $bet_event_cat_name == "1st Goal - 1st Half" || $bet_event_cat_name == "Total Goals O/U - 1st Half" || $bet_event_cat_name == "Total Goals O/U - 2nd Half" || $bet_event_cat_name == "Correct Score (Regular Time)" || $bet_event_cat_name == "1st Goal" || $bet_event_cat_name == "Team 1 to Score" || $bet_event_cat_name == "Team 2 to Score" || $bet_event_cat_name == "Both Teams to Score 1st Half" || $bet_event_cat_name == "Number of Corners (Regular Time)" || $bet_event_cat_name == "Red Card - Yes/No" || $bet_event_cat_name == "Total Goals - Exact"){

				  
				 
			 $cat_events = 'af_pre_bet_events_cats';
			 $c_count = "SELECT count(*) as count FROM $cat_events WHERE bet_event_id = '$bet_event_id'";
			 $c_result = $conn->query($c_count);
                            if ($c_result->num_rows > 0) {
                                $c_row = $c_result->fetch_assoc();
                                $c_sort = $c_row['count']+1;
                            } else {
                                $c_sort = 1;
                            }
			if($visible == 'Visible'){				
			 $af_cat_events = "INSERT IGNORE INTO $cat_events (bet_event_cat_id, c_sort, bet_event_id, bet_event_cat_name, spid) VALUES('$bet_event_cat_id', '$c_sort', '$bet_event_id', '$bet_event_cat_name', '$sport_id')";
			 
			 if ($conn->query($af_cat_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_cat_events . "<br>" . $conn->error;		 
			 }
				  
 
			 
			 
			 //INSERT BET OPTIONS
			 
			 $d = $c[$j]['results'];
               foreach ($d as $e) {
				   $bet_option_id = $e['id'];
                   $oname = $e['name']['value'];
                   $bet_option_name = addslashes($oname );
                   $bet_option_odd = $e['odds'];
				   $ovisible = $e['visibility'];
				   
				   
				   
			 $opt_events = 'af_pre_bet_options';
			 $o_count = "SELECT count(*) as count FROM $opt_events WHERE bet_event_cat_id = '$bet_event_cat_id'";
			 $o_result = $conn->query($o_count);
                            if ($o_result->num_rows > 0) {
                                $o_row = $o_result->fetch_assoc();
                                $o_sort = $o_row['count']+1;
                            } else {
                                $o_sort = 1;
                            }
			if($ovisible == 'Visible'){

				
			 $af_opt_events = "INSERT INTO $opt_events (bet_option_id, o_sort, bet_option_name, bet_option_odd, status, bet_event_cat_id) VALUES('$bet_option_id', '$o_sort', '$bet_option_name', '$bet_option_odd', 'awaiting', '$bet_event_cat_id') ON DUPLICATE KEY UPDATE bet_option_odd = '$bet_option_odd'";
			 
			 if ($conn->query($af_opt_events) === TRUE) {
				 echo 'inserted';
			 } else {
				 echo "Error: " . $af_opt_events . "<br>" . $conn->error;		 
			 }
				   

			    } //if ovisible
			   } //bet options markets
			  } // if visible..
		      } //if category is
			  } //cat markets
			 
			}
		}
	}
};




include_once('insert_events_more.php');
include_once('team_img_update.php');

exit;
?>