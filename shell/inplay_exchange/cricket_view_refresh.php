<?php error_reporting(0);
	include_once('../db.php');
	
	$bet_event_id = $_POST['evid'];
	$user_id = $_POST['usid'];
	$catid =$_POST['catid'];
	
	if(empty($bet_event_id)){
		die();
	}


	function getEventData($bet_event_id,$conn){
		$query="SELECT * FROM af_inplay_bet_events JOIN af_inplay_bet_events_cats ON af_inplay_bet_events.bet_event_id=af_inplay_bet_events_cats.bet_event_id JOIN af_inplay_bet_options ON af_inplay_bet_events_cats.bet_event_cat_id=af_inplay_bet_options.bet_event_cat_id WHERE af_inplay_bet_events.bet_event_id=$bet_event_id ORDER BY c_sort ASC";
		$event_data=mysqli_query($conn,$query);
		$structured_data=array();
		$temp_cat_id='';
		$event_name='';
		while($row=mysqli_fetch_assoc($event_data)){
			if($event_name==''){
				$event_name=$row['bet_event_name'];
				$spid=$row['spid'];
			}
			if($temp_cat_id==$row['bet_event_cat_id']){
				$option_name=$row['bet_option_name'];
				$option_id=$row['bet_option_id'];
				$odd=$row['bet_option_odd'];
				$o_sort=$row['o_sort'];
				$bet_option=['bet_option_name'=>$option_name,'bet_option_id'=>$option_id,'odd'=>$odd,'o_sort'=>$o_sort];
				array_push($structured_data[strval($temp_cat_id)]['bet_options'],$bet_option);
			}else{
				$temp_cat_id=$row['bet_event_cat_id'];
				$cat_name=$row['bet_event_cat_name'];
				$cat_id=$row['bet_event_cat_id'];
				$spid=$row['spid'];
				$option_name=$row['bet_option_name'];
				$option_id=$row['bet_option_id'];
				$odd=$row['bet_option_odd'];
				$c_sort=$row['c_sort'];
				$o_sort=$row['o_sort'];
				$bet_option=['bet_option_name'=>$option_name,'bet_option_id'=>$option_id,'odd'=>$odd,'o_sort'=>$o_sort];	
				$structured_data[strval($temp_cat_id)]=['cat_name'=>$row['bet_event_cat_name'],'c_sort'=>$c_sort,'spid'=>$spid,'bet_options'=>[$bet_option]];
			}
		}


		return array('event_id'=>$bet_event_id,'event_name'=>$event_name,'spid'=>$spid,'categories'=>$structured_data);
	}
	
	$bet_event_data=getEventData($bet_event_id,$conn);
	
 


$slip = "SELECT * FROM sh_sf_tickets_records WHERE user_id='$user_id' AND status='awaiting' AND event_id = $bet_event_id AND bet_info IS NULL AND type <> 'sbook'";
$shslips = mysqli_query($conn,$slip);

$avaiting_odd_list_back=array();
$avaiting_odd_list_lay=array();

while($shsl = $shslips->fetch_assoc()){
            $winnings = $shsl['winnings'];
			$option_id = $shsl['bet_option_id'];
			$option_name = $shsl['bet_option_name'];
			$bet_info = $shsl['bet_info'];
			$event_id = $shsl['event_id'];
			$type = $shsl['type'];
			$okk = $shsl['bet_option_name']. ' ' .$shsl['bet_option_id'];
			
			
              if($type == "back"){
                  $avaiting_odd_list_back[$okk]+=$winnings;
				  $c_back[$okk]=$shsl['stake'];
				  $c_odd[$okk]= $shsl['odd'];
				  $s_back[$okk]= $shsl['slip_id'];
				  //for laybet
              }else if($type == "lay"){
                  $avaiting_odd_list_lay[$okk]+=$winnings;
                  //$avaiting_odd_list_lay[$data[0]['bid_id'].'-count']=$data[0]['count']+1; for sort_order
              }
            
		};
	
	
	
	//function format update slip back
	function bgetOdd($bodk){
	 if(isset($_COOKIE["theme"]) && $_COOKIE['theme']== "american"){
	  $decimal_odd = $bodk;
	  if (2 > $decimal_odd) {
                $plus_minus = '-';
                $result = 100 / ($decimal_odd - 1);  
            } else {              
                $plus_minus = '+';
                $result = ($decimal_odd - 1) * 100;
            }       
            return ($plus_minus . round($result, 2));
     }else if(isset($_COOKIE['theme']) && $_COOKIE['theme']== "fraction" ){
	  //for back
	  $decimal_odd = $bodk;
	  if (2 == $decimal_odd) {
                return '1/1';
            }         
            $dividend = intval(strval((($decimal_odd - 1) * 100)));
            $divisor = 100;
            
            $smaller = ($dividend > $divisor) ? $divisor : $dividend;
            
            //worst case: 100 iterations
            for ($common_denominator = $smaller; $common_denominator > 0; $common_denominator --) {
                if ( (0 === ($dividend % $common_denominator)) && (0 === ($divisor % $common_denominator)) ) {              
                    $dividend /= $common_denominator;
                    $divisor /= $common_denominator;                 
                    return ($dividend . '/' . $divisor);
                }
            }           
            return ($dividend . '/' . $divisor);
	  
  }else{ 
  return $bodk;
  }
};

//function format update slip back
	function nbgetOdd($xbod){
	 if(isset($_COOKIE["theme"]) && $_COOKIE['theme']== "american"){
	  $decimal_odd = $xbod;
	  if (2 > $decimal_odd) {
                $plus_minus = '-';
                $result = 100 / ($decimal_odd - 1);  
            } else {              
                $plus_minus = '+';
                $result = ($decimal_odd - 1) * 100;
            }       
            return ($plus_minus . round($result, 2));
     }else if(isset($_COOKIE['theme']) && $_COOKIE['theme']== "fraction" ){
	  //for back
	  $decimal_odd = $xbod;
	  if (2 == $decimal_odd) {
                return '1/1';
            }         
            $dividend = intval(strval((($decimal_odd - 1) * 100)));
            $divisor = 100;
            
            $smaller = ($dividend > $divisor) ? $divisor : $dividend;
            
            //worst case: 100 iterations
            for ($common_denominator = $smaller; $common_denominator > 0; $common_denominator --) {
                if ( (0 === ($dividend % $common_denominator)) && (0 === ($divisor % $common_denominator)) ) {              
                    $dividend /= $common_denominator;
                    $divisor /= $common_denominator;                 
                    return ($dividend . '/' . $divisor);
                }
            }           
            return ($dividend . '/' . $divisor);
	  
  }else{ 
  return $xbod;
  }
};
	 
//function format update slip laybet
	function lgetOdd($lodk){
	 if(isset($_COOKIE["theme"]) && $_COOKIE['theme']== "american"){
	   $decimal_oddlay = $lodk;
	    if (2 > $decimal_oddlay) {
                $plus_minusl = '-';
                $resultl = 100 / ($decimal_oddlay - 1);  
            } else {              
                $plus_minusl = '+';
                $resultl = ($decimal_oddlay - 1) * 100;
            }       
            return ($plus_minusl . round($resultl, 2));
			
     }else if(isset($_COOKIE['theme']) && $_COOKIE['theme']== "fraction" ){
	  //for back
	  $decimal_odd = $lodk;
	  if (2 == $decimal_odd) {
                return '1/1';
            }         
            $dividend = intval(strval((($decimal_odd - 1) * 100)));
            $divisor = 100;
            
            $smaller = ($dividend > $divisor) ? $divisor : $dividend;
            
            //worst case: 100 iterations
            for ($common_denominator = $smaller; $common_denominator > 0; $common_denominator --) {
                if ( (0 === ($dividend % $common_denominator)) && (0 === ($divisor % $common_denominator)) ) {              
                    $dividend /= $common_denominator;
                    $divisor /= $common_denominator;                 
                    return ($dividend . '/' . $divisor);
                }
            }           
            return ($dividend . '/' . $divisor);
	  
  }else{
	  
  return $lodk;
  }
}


//function format update slip laybet
	function nlgetOdd($xlod){
	 if(isset($_COOKIE["theme"]) && $_COOKIE['theme']== "american"){
	   $decimal_oddlay = $xlod;
	    if (2 > $decimal_oddlay) {
                $plus_minusl = '-';
                $resultl = 100 / ($decimal_oddlay - 1);  
            } else {              
                $plus_minusl = '+';
                $resultl = ($decimal_oddlay - 1) * 100;
            }       
            return ($plus_minusl . round($resultl, 2));
			
     }else if(isset($_COOKIE['theme']) && $_COOKIE['theme']== "fraction" ){
	  //for back
	  $decimal_odd = $xlod;
	  if (2 == $decimal_odd) {
                return '1/1';
            }         
            $dividend = intval(strval((($decimal_odd - 1) * 100)));
            $divisor = 100;
            
            $smaller = ($dividend > $divisor) ? $divisor : $dividend;
            
            //worst case: 100 iterations
            for ($common_denominator = $smaller; $common_denominator > 0; $common_denominator --) {
                if ( (0 === ($dividend % $common_denominator)) && (0 === ($divisor % $common_denominator)) ) {              
                    $dividend /= $common_denominator;
                    $divisor /= $common_denominator;                 
                    return ($dividend . '/' . $divisor);
                }
            }           
            return ($dividend . '/' . $divisor);
	  
  }else{
	  
  return $xlod;
  }
}




	
	
	
	//function to get unsubmitted bets(to send data to front-end in json form)
	/*function UnsubmittedOdds($user_id,$conn){
		$query="SELECT * FROM sh_sf_tickets_records WHERE user_id='$user_id' AND status='unsubmitted'";
		$unsubmitted_slip=mysqli_query($conn,$query);
		$unsubmitted_slip_array=mysqli_fetch_assoc($unsubmitted_slip);
		if($unsubmitted_slip_array['bet_info']){
			return unserialize($unsubmitted_slip_array['bet_info']);
		}else{
			return [$unsubmitted_slip_array];
		}
	}
*/

	


	
	
	//event view
	echo "<div class='evidf' id=".$bet_event_data['event_id'].">".$bet_event_data['event_name']."</div>";
	
	
	
	echo '<div class="cricid" id="3"></div>';
	
    foreach ($bet_event_data['categories'] as $key => $category) {
		
	 if($category['c_sort'] == '1'):?>
       <div class="cs modelwrap ms">
		<div class="catTop1 xp"><i class="icon timeline"></i> <?php echo $category['cat_name'];?> <span class="sfright xp"><i class="icon checkbox checked alt" title="Cashout Available"></i> <i class="icon star full" title="Top Category"></i><i class="icon ellipsis vertical"></i><a href="/page/live-betting-terms/" id="rul">Rules</a></span></div>
		<div class="matvhrf xp">Matched: <span class="oftrack xp"><?php echo rand(100000,999000);?></span> <span class="laback xp top"><a id="bkall">Back All</a> <a id="lyall">Lay All</a></span></div>
		<?php foreach ($category['bet_options'] as $key_options => $option) {
			//for odd format
			$bodk = $option['odd'];
			$lodk = $bodk + 0.02;
			$bod = bgetOdd($bodk);
			$lod = lgetOdd($lodk);
			//for ext
			if($bodk < 1){
				$nbod = $bodk;
				$nlod = $bodk;
				$lod = $bodk;
			}else{
			$xbod = $bodk-0.01;
			$nbod = nbgetOdd($xbod);
			
			$xlod = $lodk + 0.01;
			$nlod = nlgetOdd($xlod);
			}
			
			//others
			$backrm = rand(10000,100000);
			$layrm = rand(100,1000);
			$okid = $option['bet_option_name']. ' ' .$option['bet_option_id'];
			$cstake = $c_back[$okid];
		    $curod = $option['odd'];	//current odd		  
			$codd = $c_odd[$okid];
			?>
        <div class="b_option_wrapper xp">
       <span class="b_option_name xp"><div class="onamebg">.</div> 
	   <span class="ocg-<?php echo $option['bet_option_id'];?>" id="<?php echo $option['bet_option_id'] ?>"><?php echo $option['bet_option_name'] ?></span>
		  <span class="cashwrapper">
		  <a><?php echo $avaiting_odd_list_back[$okid];?></a> 
		   <?php if(!empty($cstake)):
		   $ult = $avaiting_odd_list_back[$okid]/$curod; $nt = $ult * 20/100; $ut = $ult - $nt;
		   echo '<span class="casout " id="slip-'.$s_back[$okid].'">Cash '.round($ut, 2).'</span>'; endif;?>		  
		  <span class="lyod"><?php $ck = $avaiting_odd_list_lay[$okid]; if(!empty($ck)){echo '-'.$ck.'';};?></span>
		  </span>
	  </span>
		  
		  
		      <div class="b_option_odd evn-<?php echo $option['bet_option_name'];?>" id="bet__option__btn__<?php echo $option['bet_option_id'] ?>__<?php echo $bet_event_id;?>__<?php echo $key ?>__<?php echo $bet_event_data['event_name']; ?>__<?php echo $category['cat_name'];?>__<?php echo $bodk;?>__<?php echo $lodk;?>__<?php echo $bet_event_data['spid'];?>__<?php echo $bod;?>__<?php echo $lod;?>">
          
		  
		  <span class="bbackk" id="cork-<?php echo $option['bet_option_id'];?>">
		   <?php echo $nbod;?></br><k class="kbm" style="font-size:10px;pointer-events:none"><?php echo $backrm - 9;?></k>
		   </span>
		  <span class="bback" id="cor-<?php echo $option['bet_option_id'];?>">
		    <?php echo $bod;?><ft class="bm"><?php echo $backrm;?></ft>
		   </span> 
		   
		   
		   <span class="blayy" id="corkk-<?php echo $option['bet_option_id'];?>">
		    <?php echo $nlod;?></br><k class="klm" style="font-size:10px;pointer-events:none"><?php echo $layrm - 9;?></k>
		   </span>
		   <span class="blay" id="corx-<?php echo $option['bet_option_id'];?>" style="display:block!important">
		    <?php echo $lod;?><ft class="lm"><?php echo $layrm;?></ft>
		   </span>	

		  
           </div> 
         </div>
       <?php }?>		
	</div>
<?php endif;?>
	<?php } ?>	
		
		
		<?php if(!empty($category['c_sort'])):?><h3 class="otm"><i class="icon maple leaf"></i> Other Markets</h3><div id="evrefresh"><i class="icon refresh"></i></div><?php else:?><div class="evsus"><i class="icon warning sign"></i> Event Suspended</div><?php endif;?>
		
	
		<ul class="crimarkers">
		<?php if($catid=='Popularc'){
		$catip = array("Player of the Match","Player Performance","Player to Score Most Sixes","Player to Score Most Fours");
			echo '<li class="comk pxxactive" id="Popularc">Popular</li>
		 <li class="comk" id="Runsc">Runs</li>
		 <li class="comk" id="Wicketsc">Bowler</li>
		 <li class="comk" id="Scoresc">Batsman</li>
		 <li class="comk" id="Oversc">Others</li>';
		} else if($catid=='Runsc'){
			$catip = array('Runs');
			echo '<li class="comk" id="Popularc">Popular</li>
		 <li class="comk pxxactive" id="Runsc">Runs</li>
		 <li class="comk" id="Wicketsc">Bowler</li>
		 <li class="comk" id="Scoresc">Batsman</li>
		 <li class="comk" id="Oversc">Others</li>';
		}else if($catid=='Wicketsc'){
			$catip = array("Bowler");
			echo '<li class="comk" id="Popularc">Popular</li>
		 <li class="comk" id="Runsc">Runs</li>
		 <li class="comk pxxactive" id="Wicketsc">Bowler</li>
		 <li class="comk" id="Scoresc">Batsman</li>
		 <li class="comk" id="Oversc">Others</li>';
		}else if($catid=='Scoresc'){
			$catip = array("Batsman");
			echo '<li class="comk" id="Popularc">Popular</li>
		 <li class="comk" id="Runsc">Runs</li>
		 <li class="comk" id="Wicketsc">Bowler</li>
		 <li class="comk pxxactive" id="Scoresc">Batsman</li>
		 <li class="comk" id="Oversc">Others</li>';
		} else if($catid=='Oversc'){
		$catip = array("Player of the Match","Player Performance","Player to Score Most Sixes","Player to Score Most Fours");
			echo '<li class="comk" id="Popularc">Popular</li>
		 <li class="comk" id="Runsc">Runs</li>
		 <li class="comk" id="Wicketsc">Bowler</li>
		 <li class="comk" id="Scoresc">Batsman</li>
		 <li class="comk pxxactive" id="Oversc">Others</li>';
		}
		 
		?> </ul>

		<?php // $category['cat_name'] == 'Player of the Match' && $category['cat_name'] == 'Player Performance' && $category['cat_name'] == 'Player to Score Most Sixes' && $category['cat_name'] == 'Player to Score Most Fours';
		?>
		 
	<div id="fetchcat"> 
	<div class="masterwrapper">
	<?php
	foreach ($bet_event_data['categories'] as $key => $category) {
		if($catid=='Popularc'){
		$fcat = $category['c_sort'] !== '1' && (!in_array($category['cat_name'], $catip));
		}else{
		$fcat = $category['c_sort'] !== '1' && (str_replace($catip, '', $category['cat_name']) != $category['cat_name']);
		}
		 if ($fcat){
			 
	 ?>	
		<div class="cs modelwrap xp">
		<div class="catTop1 xp"><i class="icon timeline"></i> <?php echo $category['cat_name'];?> <span class="sfright xp"><i class="icon checkbox checked alt" title="Cashout Available"></i> <i class="icon star full" title="Top Category"></i><i class="icon ellipsis vertical"></i><a href="/page/live-betting-terms/" id="rul">Rules</a></span></div>
		<div class="matvhrf xp">Matched: <span class="oftrack xp"><?php echo rand(10000,99000);?></span> <span class="laback xp">Back Lay</span></div>
		<?php foreach ($category['bet_options'] as $key_options => $option) {
			//for odd format
			$bodk = $option['odd'];
			$lodk = $bodk + 0.02;
			$bod = bgetOdd($bodk);
			$lod = lgetOdd($lodk);
			if($bodk < 1){
				$lod = $bodk;
			}
			
			$backrm = rand(1000,9000);
			$layrm = rand(10,100);
			$okid = $option['bet_option_name']. ' ' .$option['bet_option_id'];
			$cstake = $c_back[$okid];
		    $curod = $option['odd'];	//current odd		  
			$codd = $c_odd[$okid];?>
        <div class="b_option_wrapper xp">		
        <span class="b_option_name xp">
		  <div class="onamebg">.</div> <?php echo $option['bet_option_name']; ?>
		  <span class="cashwrapper">
		  <a><?php echo $avaiting_odd_list_back[$okid];?></a> 
		   <?php if(!empty($cstake)):
		   $ult = $avaiting_odd_list_back[$okid]/$curod; $nt = $ult * 20/100; $ut = $ult - $nt;
		   echo '<span class="casout " id="slip-'.$s_back[$okid].'">Cash '.round($ut, 2).'</span>'; endif;?>		  
		  <span class="lyod"><?php $ck = $avaiting_odd_list_lay[$okid]; if(!empty($ck)){echo '-'.$ck.'';};?></span>
		  </span>
	  </span>
		  <div class="b_option_odd evn-<?php echo $option['bet_option_name'];?>" id="bet__option__btn__<?php echo $option['bet_option_id'] ?>__<?php echo $bet_event_id;?>__<?php echo $key ;?>__<?php echo $bet_event_data['event_name']; ?>__<?php echo $category['cat_name'];?>__<?php echo $bodk;?>__<?php echo $lodk;?>__<?php echo $bet_event_data['spid'];?>__<?php echo $bod;?>__<?php echo $lod;?>">        
		  <span class="bback" id="cor-<?php echo $option['bet_option_id'];?>"><?php echo $bod;?><ft class="bm"><?php echo $backrm;?></ft></span> <span class="blay" id="corx-<?php echo $option['bet_option_id'];?>" style="display:block!Important"><?php echo $lod;?><ft class="lm"><?php echo $layrm;?></ft></span>
           </div>   
        </div>		
        <?php }?>
	</div>
    
	<?php } ;?>
	<?php } ?>
	</div>
	</div>

