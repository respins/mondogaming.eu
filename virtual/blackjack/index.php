 <?php
 session_start();
include_once('../../app-load.php');  
global $current_user;
get_currentuserinfo();
$cUser = $current_user->ID;
$spoint = get_user_meta( $cUser, 'sf_points', true );

//echo $spoint;
//die;

?>

<!DOCTYPE html>
<html>
    <head>
        <title></title>
        <link rel="stylesheet" href="css/reset.css" type="text/css">
        <link rel="stylesheet" href="css/main.css" type="text/css">
        <link rel="stylesheet" href="css/orientation_utils.css" type="text/css">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

        <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0,minimal-ui" />
	<meta name="msapplication-tap-highlight" content="no"/>

        <script type="text/javascript" src="js/jquery-3.2.1.min.js"></script>
        <script type="text/javascript" src="js/createjs-2015.11.26.min.js"></script>
        <script type="text/javascript" src="js/howler.min.js"></script>
        <script type="text/javascript" src="js/screenfull.js"></script>
        <script type="text/javascript" src="js/ctl_utils.js"></script>
        <script type="text/javascript" src="js/sprite_lib.js"></script>
        <script type="text/javascript" src="js/settings.js"></script>
        <script type="text/javascript" src="js/CLang.js"></script>
        <script type="text/javascript" src="js/CPreloader.js"></script>
        <script type="text/javascript" src="js/CMain.js"></script>
        <script type="text/javascript" src="js/CTextButton.js"></script>
        <script type="text/javascript" src="js/CGfxButton.js"></script>
        <script type="text/javascript" src="js/CToggle.js"></script>
        <script type="text/javascript" src="js/CMenu.js"></script>
        <script type="text/javascript" src="js/CGame.js"></script>
        <script type="text/javascript" src="js/CInterface.js"></script>
        <script type="text/javascript" src="js/CTweenController.js"></script>
        <script type="text/javascript" src="js/CSeat.js"></script>
        <script type="text/javascript" src="js/CFichesController.js"></script>
        <script type="text/javascript" src="js/CVector2.js"></script>
        <script type="text/javascript" src="js/CGameSettings.js"></script>
        <script type="text/javascript" src="js/CEasing.js"></script>
        <script type="text/javascript" src="js/CHandController.js"></script>
        <script type="text/javascript" src="js/CCard.js"></script>
        <script type="text/javascript" src="js/CInsurancePanel.js"></script>
        <script type="text/javascript" src="js/CGameOver.js"></script>
        <script type="text/javascript" src="js/CMsgBox.js"></script>
        <script type="text/javascript" src="js/CCreditsPanel.js"></script>
    </head>
    <body ondragstart="return false;" ondrop="return false;" >
	<div style="position: fixed; background-color: transparent; top: 0px; left: 0px; width: 100%; height: 100%"></div>
          <script>
            $(document).ready(function(){
                     var oMain = new CMain({
                                    win_occurrence: 10,          //WIN OCCURRENCE PERCENTAGE. VALUES BETWEEN 0-100
                                    min_bet: 1,                //MIN BET PLAYABLE BY USER. DEFAULT IS 0.1$
                                    max_bet: 100,                //MAX BET PLAYABLE BY USER. 
                                    bet_time: 10000,             //WAITING TIME FOR PLAYER BETTING
                                    money:<?= $spoint ?>,                 //STARING CREDIT FOR THE USER
                                    blackjack_payout: 1.5,       //PAYOUT WHEN USER WINS WITH BLACKJACK (DEFAULT IS 3 TO 2). BLACKJACK OCCURS WHEN USER GET 21 WITH FIRST 2 CARDS
                                    game_cash: 500,             //GAME CASH AVAILABLE WHEN GAME STARTS
                                    show_credits:true,          //ENABLE/DISABLE CREDITS BUTTON IN THE MAIN SCREEN
                                    fullscreen:true, //SET THIS TO FALSE IF YOU DON'T WANT TO SHOW FULLSCREEN BUTTON
                                    check_orientation:true,     //SET TO FALSE IF YOU DON'T WANT TO SHOW ORIENTATION ALERT ON MOBILE DEVICES
                                    //////////////////////////////////////////////////////////////////////////////////////////
                                    ad_show_counter: 3           //NUMBER OF HANDS PLAYED BEFORE AD SHOWN
                                    //
                                    //// THIS FUNCTIONALITY IS ACTIVATED ONLY WITH CTL ARCADE PLUGIN.///////////////////////////
                                    /////////////////// YOU CAN GET IT AT: /////////////////////////////////////////////////////////
                                    // http://codecanyon.net/item/ctl-arcade-wordpress-plugin/13856421 ///////////
                                });


					 
                    $(oMain).on("recharge", function(evt) {
                             alert("add your recharge script in index.html");
                    });
                    
                    $(oMain).on("bet_placed", function (evt, iTotBet) {
                        //...ADD YOUR CODE HERE EVENTUALLY
                    });
                
                    $(oMain).on("start_session", function(evt) {
                        if(getParamValue('ctl-arcade') === "true"){
                            parent.__ctlArcadeStartSession();
                        }
                        //...ADD YOUR CODE HERE EVENTUALLY
                    });

                    $(oMain).on("end_session", function(evt) {
                           if(getParamValue('ctl-arcade') === "true"){
                               parent.__ctlArcadeEndSession();
                           }
                           //...ADD YOUR CODE HERE EVENTUALLY
                    });
					
					
					$(oMain).on("bet_placed", function (evt, iTotBet) {
                            //...ADD YOUR CODE HERE EVENTUALLY
							//alert(iTotBet);
							
							window.localStorage.setItem("testdata", iTotBet);

                        
							
				
                });


                    $(oMain).on("save_score", function(evt, iMoney) {
						
                            if(getParamValue('ctl-arcade') === "true"){
                                parent.__ctlArcadeSaveScore({score:iMoney});
                            }
							var mu =  window.localStorage.getItem("testdata");
                            //...ADD YOUR CODE HERE EVENTUALLY
							//alert(iMoney);
							$.ajax({
							  type: "POST",
							  url: "process.php",
							  data: {
								  name:"update",
								  iMoney:iMoney,
								  stake: mu
							  },
							  success: function (response) {
							   // you will get response from your php page (what you echo or print)                 
								//alert(response);
							  },
							  
							});
                     });
					 
                    $(oMain).on("show_interlevel_ad", function(evt) {
                           if(getParamValue('ctl-arcade') === "true"){
                               parent.__ctlArcadeShowInterlevelAD();
                           }
                           //...ADD YOUR CODE HERE EVENTUALLY
                    });

                    $(oMain).on("share_event", function(evt, iScore) {
                           if(getParamValue('ctl-arcade') === "true"){
                               parent.__ctlArcadeShareEvent({   img: TEXT_SHARE_IMAGE,
                                                                title: TEXT_SHARE_TITLE,
                                                                msg: TEXT_SHARE_MSG1 + iScore + TEXT_SHARE_MSG2,
                                                                msg_share: TEXT_SHARE_SHARE1 + iScore + TEXT_SHARE_SHARE1});
                           }
                    });
                    
                    if (isIOS()) {
                        setTimeout(function () {
                            sizeHandler();
                        }, 200);
                    } else {
                        sizeHandler();
                    }
           });

        </script>
        
        <div class="check-fonts">
            <p class="check-font-1">test 1</p>
        </div> 
        
        <canvas id="canvas" class='ani_hack' width="1700" height="768"> </canvas>
        <div data-orientation="landscape" class="orientation-msg-container"><p class="orientation-msg-text">Please rotate your device</p></div>
	<div id="block_game" style="position: fixed; background-color: transparent; top: 0px; left: 0px; width: 100%; height: 100%; display:none"></div>
    </body>
</html>