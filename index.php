<?php

include ('config.php');

$objConn = getMsqliConnection();

$froom = new defenderGame($objConn);
$froom->splashScreen();

class defenderGame {

    public $m_objConn;
    public $user_id;
    public $game_id;
    public $map_id;
    public $selectedOption;
    public $currentUser;
    public $player1;
    public $player2;
    public $sessionUsername;
    public $sessionID;
    public $menuOption;

    function __construct($objConn) {
        $this->m_objConn = $objConn;


        $this->sessionUsername = isset($_SESSION['userName']) ? $_SESSION['userName'] : NULL;
        $this->sessionID = isset($_SESSION['userID']) ? $_SESSION['userID'] : NULL;

        $defaultValue = isset($_GET['displayCode']) ? $_GET['displayCode'] : NULL;

        $newQry = "SELECT * FROM tbl_users WHERE user_username='$this->sessionUsername' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRow = $newResult->fetch_object();

        $this->menuOption = (isset($_GET['menuOption'])) ? $_GET['menuOption'] : NULL;
        $this->user_id = (isset($newRow->user_id)) ? $newRow->user_id : NULL;
        $this->game_id = (isset($_GET['GameID'])) ? $_GET['GameID'] : NULL;
        $this->mapCoords = (isset($_GET['location'])) ? $_GET['location'] : NULL;

        $this->selectedOption = (isset($_GET['Select'])) ? $_GET['Select'] : 1;
        $this->currentUser = (isset($_GET['ID'])) ? $_GET['ID'] : NULL;
        $newQry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRow = $newResult->fetch_object();

        $mapCode = isset($newRow->map_id) ? $newRow->map_id : NULL;

        $this->player1 = (isset($_GET['ID1'])) ? $_GET['ID1'] : NULL;
        $this->player2 = (isset($_GET['ID2'])) ? $_GET['ID2'] : NULL;

        if ($mapCode)
            $this->map_id = $newRow->map_id;

        if (isset($this->sessionUsername) AND ( $defaultValue == NULL))
            $this->gameScreen();
    }

    public function displayFightScene() {


        $checkQry = "SELECT * FROM tbl_location, tbl_army_data WHERE tbl_location.user_id=tbl_army_data.user_id AND tbl_army_data.user_id='$this->player1' AND tbl_army_data.game_id='$this->game_id' AND location_army='Yes'";
        $checkResult = $this->m_objConn->query($checkQry);

        $checkRows = $checkResult->num_rows;
        $checkRow = $checkResult->fetch_object();

        $player1Soldiers = $checkRow->army_soldiers;
        $player1Knights = $checkRow->army_knights;

        $checkQry = "SELECT * FROM tbl_location, tbl_army_data WHERE tbl_location.user_id=tbl_army_data.user_id AND tbl_army_data.user_id='$this->player2' AND tbl_army_data.game_id='$this->game_id' AND location_army='Yes' ";
        echo 'E: ' . $checkQry . '<br>';

        $checkResult = $this->m_objConn->query($checkQry);

        $checkRows = $checkResult->num_rows;
        $checkRow1 = $checkResult->fetch_object();

        $player2Soldiers = $checkRow1->army_soldiers;
        $player2Knights = $checkRow1->army_knights;

        echo 'Q: ' . $player2Knights . '<br>';

        echo '<table width=100% border=1 cellspacing=10 >';
        echo '<tr><td colspan=4>';

        echo '<table width=100% cellspacing=0 cellpadding=0 border=1>';
        echo '<tr><td align=right bgcolor=green width=25%></td><td bgcolor=green align=right><img src="Images/soliderLeft.gif"></td><td bgcolor=green><img src="Images/soliderRight.gif"></td><td bgcolor=green>&nbsp;</td></tr>';


        echo '<tr><td align=right bgcolor=green>';
        if ($player1Knights >= 10)
            echo '<img src="Images/soliderLeft.gif">';
        echo '</td><td bgcolor=green align=right>';
        if ($player1Soldiers >= 25)
            echo '<img src="Images/soliderLeft.gif">';

        echo '</td><td bgcolor=green>';

        if ($player2Soldiers >= 25)
            echo '<img src="Images/soliderRight.gif">';
        echo '</td><td bgcolor=green>';
        if ($player2Knights >= 10)
            echo '<img src="Images/soliderRight.gif">';

        echo '</td></tr>';

        echo '<tr><td align=right bgcolor=green>';
        if ($player1Knights >= 20)
            echo '<img src="Images/soliderLeft.gif">';
        echo '</td><td bgcolor=green align=right>';
        if ($player1Soldiers >= 50)
            echo '<img src="Images/soliderLeft.gif">';

        echo '</td><td bgcolor=green>';

        if ($player2Soldiers >= 50)
            echo '<img src="Images/soliderRight.gif">';
        echo '</td><td bgcolor=green align=right>';
        if ($player2Knights >= 20)
            echo '<img src="Images/soliderRight.gif">';

        echo '</td></tr>';

        echo '<tr><td align=right bgcolor=green>';
        if ($player1Knights >= 30)
            echo '<img src="Images/soliderLeft.gif">';
        echo '</td><td bgcolor=green align=right>';
        if ($player1Soldiers >= 75)
            echo '<img src="Images/soliderLeft.gif">';

        echo '</td><td bgcolor=green>';

        if ($player2Soldiers >= 75)
            echo '<img src="Images/soliderRight.gif">';
        echo '</td><td bgcolor=green>';
        if ($player2Knights >= 30)
            echo '<img src="Images/soliderRight.gif">';

        echo '</td></tr>';
        echo '</table>';

        echo '</td></tr>';

        echo '<tr><td width=50% height=200 bgcolor=grey>&nbsp;</td><td bgcolor=grey></td></tr>';
        echo '</table>';
    }

    public function updatePlayerMovement() {

        $castlesPresent = 0;
        echo '<body bgcolor=white>';
        error_reporting(E_ALL);
        ini_set('display_errors', '1');

        #Get Turn Data.
        $mapQry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
        $mapResult = $this->m_objConn->query($mapQry);

        $mapRow = $mapResult->fetch_object();

        $mapID = $mapRow->map_id;
        $nextTurn = $mapRow->user_turn;

        echo 'Game No: ' . $this->game_id . ' :: Whos Turn is it: ' . $mapRow->user_turn . '<br><br>';

        if ($nextTurn != '3' OR $nextTurn != '4' OR $nextTurn != '5' OR $nextTurn != '6') {

            #  Obtain Old Location. 
            $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_army='Yes' ";
            $newResult = $this->m_objConn->query($newQry);

            $newRows = $newResult->num_rows;
            $newRow = $newResult->fetch_object();

            echo 'Player Movement Needs:<br><br>';

            $oldLocation = $newRow->location_coords;

            echo 'Old Location: <font color=blue>' . $oldLocation . '</font><br>';

            #   Obtain New Location.

            $newLocation = isset($_GET['location']) ? $_GET['location'] : NULL;


            echo 'New Location: <font color=blue>' . $newLocation . '</font><br><br>';

            echo '#Conditions of New Location.<br>';
            #   Check if location is Occupided. 
            #   Access Database to check if location is already taken

            $newQry = "SELECT * FROM tbl_location, tbl_users WHERE tbl_location.user_id=tbl_users.user_id AND location_coords='$newLocation' AND game_id='$this->game_id' ";

            #  echo 'Q: '.$newQry.'<br>';

            $newResult = $this->m_objConn->query($newQry);

            $newRows = $newResult->num_rows;
            $newRow = $newResult->fetch_object();

            if ($newRows)
                $oldOwner = $newRow->user_id;

            if ($newRows >= 1)
                echo 'If it Occupied: <font color=Red> Yes </font><br><br>';
            else
                echo 'If it Occupied: <font color=Blue> No </font><br><br>';


            #   Check who owns this location.
            #   Access Database to check if location is already taken
            $armyNumbers = 0;
            if ($newRows >= 1) {
                echo ' - check who Owns it. :: <font color=blue>' . $newRow->user_fullname . '</font><br>';

                $solQry = "SELECT * FROM tbl_location, tbl_users WHERE tbl_location.user_id=tbl_users.user_id AND location_coords='$newLocation' AND game_id='$this->game_id' ";
                $solResult = $this->m_objConn->query($solQry);

                $solRows = $solResult->num_rows;
                $solRow = $solResult->fetch_object();

                $castlesPresent = $solRow->location_castles;

                echo '- Local Soliders: ' . $solRow->location_soldiers . '<br>';
                echo '- Local Knights: ' . $solRow->location_knights . '<br>';
                echo '- Local Catapults: ' . $solRow->location_catapults . '<br>';
                echo '- Local Castles: ' . $solRow->location_castles . '<br>';

                echo 'Invader: ' . $nextTurn . ' Owner of Property: ' . $newRow->user_id . '<br>';

                if ($nextTurn == $newRow->user_id) {
                    echo ' => Move For Free: <font color=Blue> Yes </font><br><br>';
                    $AdjustQry1 = "UPDATE tbl_location SET location_army='No'  WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_coords='$oldLocation' ";
                    $AdjustQry2 = "UPDATE tbl_location SET location_army='Yes'  WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_coords='$newLocation' ";

                    mysqli_query($this->m_objConn, $AdjustQry1) or die(mysqli_error());
                    mysqli_query($this->m_objConn, $AdjustQry2) or die(mysqli_error());
                    # echo '<meta http-equiv="refresh" content="2;url=' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $this->game_id . '">';
                } else {

                    $armyQry = "SELECT * FROM tbl_location, tbl_army_data WHERE tbl_location.user_id=tbl_army_data.user_id AND tbl_location.game_id='$this->game_id' AND location_coords='$newLocation' AND location_army='Yes' ";
                    #       echo $armyQry . '<br>';

                    $armyResult = $this->m_objConn->query($armyQry);

                    $armyRows = $armyResult->num_rows;
                    $armyRow = $armyResult->fetch_object();

                    $armyNumbers += ($solRow->location_soldiers == 0) ? $armyNumbers : $armyNumbers++;
                    $armyNumbers += ($solRow->location_knights == 0) ? $armyNumbers : $armyNumbers++;
                    $armyNumbers += ($solRow->location_catapults == 0) ? $armyNumbers : $armyNumbers++;

                    if ($armyRows) {
                        $armyNumbers += ($armyRow->army_soldiers == 0) ? $armyNumbers : $armyNumbers++;

                        $armyNumbers += ($armyRow->army_knights == 0) ? $armyNumbers : $armyNumbers++;
                        $armyNumbers += ($armyRow->army_catapults == 0) ? $armyNumbers : $armyNumbers++;

                        echo '- Army Soliders: ' . $armyRow->army_soldiers . '<br>';
                        echo '- Army Knights: ' . $armyRow->army_knights . '<br>';
                        echo '- Army Catapults: ' . $armyRow->army_catapults . '<br>' . $armyNumbers . '<br>';
                    }
                }

                #Invading Army Details.
                $invadingQry = "SELECT * FROM tbl_army_data WHERE user_id='$nextTurn' AND game_id='$this->game_id' ";
                $invadingResult = $this->m_objConn->query($invadingQry);

                $invadingRow = $invadingResult->fetch_object();
                $catsPresent = $invadingRow->army_catapults;

                echo '<br><br><b>Invading Army: <br>';
                echo '- Army Soliders: </b>' . $invadingRow->army_soldiers . '<br>';
                echo '- Army Knights: ' . $invadingRow->army_knights . '<br>';
                echo '- Army Catapults: ' . $invadingRow->army_catapults . '<br>';

                if ($catsPresent == 0 AND $castlesPresent >= 1) {
                    echo '<br>- Fight: <font color=Red> No - No Catapult Present. </font><br><br>';
                    echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $this->game_id . '">';
                } else {
                    if ($armyNumbers == 0 AND $castlesPresent == 0) {
                        echo '<br>- No Defending Troops No Fight: <br><br>';
                        $AdjustQry1 = "UPDATE tbl_location SET location_army='No'  WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_coords='$oldLocation' ";
                        $AdjustQry2 = "UPDATE tbl_location SET location_army='Yes', user_id='$this->user_id'  WHERE game_id='$this->game_id' AND user_id='$oldOwner' AND location_coords='$newLocation' ";

                        mysqli_query($this->m_objConn, $AdjustQry1) or die(mysqli_error());
                        mysqli_query($this->m_objConn, $AdjustQry2) or die(mysqli_error());

                        if ($nextTurn != $newRow->user_id)
                            echo $this->nextTurn($this->game_id);
                        echo '<meta http-equiv="refresh" content="2;url=' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $this->game_id . '">';
                    } else {
                        echo '<br>- Fight: <font color=Red> Yes </font><br><br>';
                        #      echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?displayCode=FightNight&&GameID=' . $this->game_id . '&&ID1=' . $this->user_id . '&&ID2=' . $oldOwner . '&&locationID=' . $newLocation . '">';
                    }
                    $changeTurn = 3;
                }
            } else {
                echo '##############################<br>';
                echo 'If Not Occupied<br>';
                echo '##############################<br>';


                #  Check for Vassals to Add to Army  #################################################################
                #  Access Database to check if location is already taken

                $chanQry = "SELECT * FROM tbl_mapdata WHERE map_number='$mapID' AND map_coords='$newLocation' ";
                $chanResult = $this->m_objConn->query($chanQry);

                $chanRow = $chanResult->fetch_object();

                echo ' - Check Vassel add to Army: <font color=blue>' . $chanRow->map_vassals . '</font><br>';
                echo ' - Add to Income List: <font color=blue>' . $chanRow->map_income . '</font><br><br><br>';

                $changeTurn = 2;

                $armQry = "SELECT * FROM tbl_army_data WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";
                $armResult = $this->m_objConn->query($armQry);
                $armRow = $armResult->fetch_object();
                $newSoliders = $armRow->army_soldiers + $chanRow->map_vassals;

                $qry = "UPDATE tbl_location SET location_army='No'  WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_coords='$oldLocation' ";
                $qry1 = "UPDATE tbl_army_data SET army_soldiers='$newSoliders'  WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";

                $qry2 = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '$this->game_id', '$this->user_id', '$newLocation', 'Yes', 'Yes')";
                mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
                mysqli_query($this->m_objConn, $qry1) or die(mysqli_error());
                mysqli_query($this->m_objConn, $qry2) or die(mysqli_error());


                #   echo $this->nextTurn($this->game_id);
                #   echo '<meta http-equiv="refresh" content="2;url=' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $this->game_id . '">';
            }
        }
    }

    public function nextTurn($gameID) {
        $newQry = "SELECT * FROM tbl_game WHERE game_id='$gameID' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRow = $newResult->fetch_object();

        if ($newRow->user_id1 == $newRow->user_turn)
            $nextTurn = $newRow->user_id2;

        if ($newRow->user_id2 == $newRow->user_turn)
            $nextTurn = $newRow->user_id3;

        if ($newRow->user_id3 == $newRow->user_turn)
            $nextTurn = $newRow->user_id4;

        if ($newRow->user_id4 == $newRow->user_turn) {
            $nextTurn = $newRow->user_id1;
            echo $this->incomeTime($gameID);
        }

        $qry3 = "UPDATE tbl_game SET user_turn='$nextTurn '  WHERE game_id='$gameID' ";

        mysqli_query($this->m_objConn, $qry3) or die(mysqli_error());
    }

    public function incomeTime($gameID) {

        $gameIncome = 0;

        $qry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        $setArray = array($checkRow->user_id1, $checkRow->user_id2, $checkRow->user_id3, $checkRow->user_id4);

        foreach ($setArray as &$value) {
            $getQry = "SELECT * FROM tbl_location, tbl_mapdata WHERE tbl_location.location_coords=tbl_mapdata.map_coords AND user_id='$value' AND tbl_location.game_id='$this->game_id' ";

            $getResult = $this->m_objConn->query($getQry);
            $getRow = $getResult->fetch_object(); {
                $gameIncome += $getRow->map_income;
            }

            $getGoldAmount = "SELECT * FROM tbl_game_data WHERE game_id='$this->game_id' AND user_id='$value' ";
            $getGold = $this->m_objConn->query($getGoldAmount);
            $getGoldRow = $getGold->fetch_object();

            $currentGoldAmount = ($getGoldRow->game_gold + $gameIncome);

            $qry = "UPDATE tbl_game_data SET game_gold='$currentGoldAmount' WHERE game_id='$this->game_id' AND user_id='$value' ";
            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        }
        # $gameIncome += $getRow->map_income;
        #            $qry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";
        #    $checkResult = $this->m_objConn->query($qry);
        #   while ($checkRow = $checkResult->fetch_object()) {
        #       $getQry = "SELECT * FROM tbl_mapdata WHERE map_coords='$checkRow->location_coords' AND game_id='$this->game_id' ";
        #        $getResult = $this->m_objConn->query($getQry);
        #        $getRow = $getResult->fetch_object();
        #        $gameIncome += $getRow->map_income;
        #    }
    }

    public function updatePlayerMovement1() {
        $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$this->user_id' AND location_army='Yes' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = $newResult->num_rows;
        $newRow = $newResult->fetch_object();

        echo 'Get old Location: ' . $newRow->location_coords . '<br>';

        $locationID = isset($_GET['location']) ? $_GET['location'] : NULL;

        echo 'Get New Location: ' . $locationID . '<br><br>';

#check for conditions
        echo 'Conditions:<br>';

        $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND location_coords='$locationID' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = $newResult->num_rows;
        $newRow = $newResult->fetch_object();

        echo 'Check if its occupied.<br>';
        if ($newRows == 0) {
            $armyUpdateQry = "SELECT * FROM tbl_army_data WHERE game_id='$this->game_id' AND user_id='$newRow->user_id' ";
            $armyResult = $this->m_objConn->query($armyUpdateQry);

            $armyRow = $armyResult->fetch_object();

            $mapQry = "SELECT * FROM tbl_map_data WHERE map_id='$this->map_id' ";
            $mapResult = $this->m_objConn->query($mapQry);

            $mapRow = $mapResult->fetch_object();

            $datSoliders = $armyRow->army_soldiers;
            $datSoliders += $mapRow->$locationID;

            $qry = "UPDATE tbl_army_data SET army_soldiers='$datSoliders '  WHERE game_id='$this->game_id' AND user_id='$newRow->user_id' ";
            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

            echo 'Vassals Collected: No<br><br>';
        } else
            echo 'Vassals Collected: Yes <br><br>';


        echo '<br>';

        $tempVar = explode('_', $locationID);

        $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND location_coords='$tempVar[1]' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = $newResult->num_rows;
        $newRow = $newResult->fetch_object();

        echo 'Query: ' . $newQry . '<br>';
        echo 'NewRows: ' . $newRows . '<br>';

        if ($newRows >= 1)
            echo 'Enemy Owns it Attack.<br>';


        echo 'If you own it move again.<br>';

        $locationID = isset($_GET['location']) ? $_GET['location'] : NULL;

        echo 'User: ' . $this->user_id . '<br>';
        echo 'Game: ' . $this->game_id . '<br>';

        echo 'newLocation: ' . $locationID . '<br>';

        $tempValue = explode('_', $locationID);
        echo 'Set Old Location No Army<br><br>';


        echo 'Get Army that was at old Location<br><br>';

        $qry = "UPDATE tbl_location SET location_army='No' WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";
        echo '<br><br>Qry: ' . $qry . '<br>';
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());



        $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '$this->game_id', '$this->user_id', '$tempValue[1]', 'Yes', 'Yes')";

        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

        $newQry = "SELECT * FROM tbl_game_data WHERE game_id='$this->game_id' AND user_id='$this->user_id'  ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = $newResult->num_rows;
        $newRow = $newResult->fetch_object();


#  $qry = "INSERT INTO tbl_game_data (user_id, game_id, location_coords, data_soldiers, data_knights, data_catapults) VALUES ( '$this->user_id', '$this->game_id', '$tempValue[1]', 'Yes', 'Yes')";
#  mysqli_query($this->m_objConn, $qry) or die(mysqli_error()); 
        $newQry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = $newResult->num_rows;
        $newRow = $newResult->fetch_object();

        if ($newRow->user_id1 == $newRow->user_turn)
            $nextTurn = $newRow->user_id2;

        if ($newRow->user_id2 == $newRow->user_turn)
            $nextTurn = $newRow->user_id3;

        if ($newRow->user_id3 == $newRow->user_turn)
            $nextTurn = $newRow->user_id4;

        if ($newRow->user_id4 == $newRow->user_turn)
            $nextTurn = $newRow->user_id1;


        $qry = "UPDATE tbl_game SET user_turn='$nextTurn '  WHERE game_id='$this->game_id' ";

        echo 'Qry: ' . $qry . '<br>';

        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

#     if ($nextTurn=='2' OR $nextTurn=='3' OR $nextTurn=='4')
#         echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'?sct=Pass&&GameID='.$this->game_id.'">';
#    else    
#        echo '<meta http-equiv="refresh" content="0;url='.$_SERVER['PHP_SELF'].'?sct=GameScreen&&GameID='.$this->game_id.'">';
    }

    public function playerPass() {
        $qry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
        $checkResult = $this->m_objConn->query($qry);

        $newRow = $checkResult->fetch_object();

        if ($newRow->user_id1 == $newRow->user_turn)
            $nextTurn = $newRow->user_id2;

        if ($newRow->user_id2 == $newRow->user_turn)
            $nextTurn = $newRow->user_id3;

        if ($newRow->user_id3 == $newRow->user_turn)
            $nextTurn = $newRow->user_id4;

        if ($newRow->user_id4 == $newRow->user_turn)
            $nextTurn = $newRow->user_id1;

        $qry = "UPDATE tbl_game SET user_turn='$nextTurn '  WHERE game_id='$this->game_id' ";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

        echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?sct=Pass&&GameID=' . $this->game_id . '">';
    }

    public function passTurn() {
        $qry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id'";
        $checkResult = $this->m_objConn->query($qry);

        $checkRow = $checkResult->fetch_object();

        $currentUser = $checkRow->user_turn;
        $mapID = $checkRow->map_id;

        echo 'User: ' . $currentUser . ' On Map ' . $mapID . '<br>';


#get all locations of player
        $qry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$currentUser' ";
        $checkResult = $this->m_objConn->query($qry);
        $checkRows = $checkResult->num_rows;

        $locationArray = array();
        $incomeArray = array();
        while ($checkRow = $checkResult->fetch_object()) {
            $locationArray[] = $checkRow->location_coords;
        }
        $newArray1 = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');

        foreach ($locationArray as &$value) {
            $tempVar1 = substr($value, 0, 1);
            $tempVar2 = substr($value, 1, 1);
            $lowValue = $tempVar1 . ($tempVar2 - 1);
            $highValue = $tempVar1 . ($tempVar2 + 1);

            $getUp = array_search($tempVar1, $newArray1);
            $getDown = array_search($tempVar1, $newArray1);

            $upValue = $newArray1[$getUp - 1] . ($tempVar2);
            $downValue = $newArray1[$getDown + 1] . ($tempVar2);

            $qry = "SELECT * FROM tbl_mapdata,tbl_game WHERE tbl_game.game_id=tbl_mapdata.map_identifer AND game_id='$this->game_id' AND map_coords='$upValue' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1) {
                $qry = "SELECT * FROM tbl_map_static WHERE static_coords='$upValue' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1) {
                    $qry = "SELECT * FROM tbl_location WHERE location_coords='$upValue' ";

                    $checkResult = $this->m_objConn->query($qry);
                    $checkRows = $checkResult->num_rows;

                    if ($checkRows == 0) {
                        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$upValue' AND map_number='$mapID' ";

                        $checkResult = $this->m_objConn->query($qry);
                        $checkRow = $checkResult->fetch_object();

                        $incomeArray[] = $checkRow->map_income . ':' . $upValue;
                    }
                }


                $qry = "SELECT * FROM tbl_location WHERE location_coords='$upValue' AND user_id!='$currentUser' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRow = $checkResult->fetch_object();
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1) {
                    $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$upValue' AND map_number='$mapID' ";

                    $checkResult = $this->m_objConn->query($qry);
                    $checkRow = $checkResult->fetch_object();
                    $incomeArray[] = $checkRow->map_income . ':' . $upValue;
                }

                #    $incomeArray[] = $checkRow->map_income.':'.$upValue;
            }


            $qry = "SELECT * FROM tbl_mapdata,tbl_game WHERE tbl_game.game_id=tbl_mapdata.map_identifer AND game_id='$this->game_id' AND map_coords='$downValue' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1) {
                $qry = "SELECT * FROM tbl_map_static WHERE static_coords='$downValue' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1) {
                    $qry = "SELECT * FROM tbl_location WHERE location_coords='$downValue' ";

                    $checkResult = $this->m_objConn->query($qry);
                    $checkRows = $checkResult->num_rows;

                    if ($checkRows == 0) {
                        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$downValue' AND map_number='$mapID' ";

                        $checkResult = $this->m_objConn->query($qry);
                        $checkRow = $checkResult->fetch_object();

                        $incomeArray[] = $checkRow->map_income . ':' . $downValue;
                    }
                }


                $qry = "SELECT * FROM tbl_location WHERE location_coords='$downValue' AND user_id!='$currentUser' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRow = $checkResult->fetch_object();
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1)
                    $incomeArray[] = $checkRow->map_income . ':' . $downValue;
            }

            $qry = "SELECT * FROM tbl_mapdata,tbl_game WHERE tbl_game.game_id=tbl_mapdata.map_identifer AND game_id='$this->game_id' AND map_coords='$lowValue' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1) {
                $qry = "SELECT * FROM tbl_map_static WHERE static_coords='$lowValue' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1) {
                    $qry = "SELECT * FROM tbl_location WHERE location_coords='$lowValue' ";

                    $checkResult = $this->m_objConn->query($qry);
                    $checkRows = $checkResult->num_rows;

                    if ($checkRows == 0) {
                        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$lowValue' AND map_number='$mapID' ";

                        $checkResult = $this->m_objConn->query($qry);
                        $checkRow = $checkResult->fetch_object();

                        $incomeArray[] = $checkRow->map_income . ':' . $lowValue;
                    }
                }


                $qry = "SELECT * FROM tbl_location WHERE location_coords='$lowValue' AND user_id!='$currentUser' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRow = $checkResult->fetch_object();
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1)
                    $incomeArray[] = $checkRow->map_income . ':' . $lowValue;
            }

            $qry = "SELECT * FROM tbl_mapdata,tbl_game WHERE tbl_game.game_id=tbl_mapdata.map_identifer AND game_id='$this->game_id' AND map_coords='$highValue' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1) {
                $qry = "SELECT * FROM tbl_map_static WHERE static_coords='$highValue' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1) {
                    $qry = "SELECT * FROM tbl_location WHERE location_coords='$highValue' ";

                    $checkResult = $this->m_objConn->query($qry);
                    $checkRows = $checkResult->num_rows;

                    if ($checkRows == 0) {
                        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$highValue' AND map_number='$mapID' ";

                        $checkResult = $this->m_objConn->query($qry);
                        $checkRow = $checkResult->fetch_object();

                        $incomeArray[] = $checkRow->map_income . ':' . $highValue;
                    }
                }


                $qry = "SELECT * FROM tbl_location WHERE location_coords='$highValue' AND user_id!='$currentUser' ";

                $checkResult = $this->m_objConn->query($qry);
                $checkRow = $checkResult->fetch_object();
                $checkRows = $checkResult->num_rows;

                if ($checkRows == 1)
                    $incomeArray[] = $checkRow->map_income . ':' . $highValue;
            }
        }

#these are the options that are available from the locations that we own.



        sort($incomeArray);

        foreach ($incomeArray as &$value) {
            $tempValue = explode(':', $value);

            $bestOption = $tempValue[1];
        }
        echo 'Best Income Value: ' . $bestOption . '<br>';

        $qry = "SELECT * FROM tbl_location WHERE location_coords='$bestOption' AND user_id!='$currentUser' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();
        $checkRows1 = $checkResult->num_rows;

        echo 'Yes: ' . $checkRows1 . '<br>';


        $tempVar1 = substr($bestOption, 0, 1);
        $tempVar2 = substr($bestOption, 1, 1);

        $lowValue = $tempVar1 . ($tempVar2 - 1);
        $highValue = $tempVar1 . ($tempVar2 + 1);

        $getUp = array_search($tempVar1, $newArray1);
        $getDown = array_search($tempVar1, $newArray1);

        $upValue = $newArray1[$getUp - 1] . ($tempVar2);
        $downValue = $newArray1[$getDown + 1] . ($tempVar2);

        if ($checkRows1 == 1) {
            $qry = "SELECT * FROM tbl_location WHERE location_coords='$lowValue' AND user_id='$currentUser' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1)
                $this->goFightNow($bestOption, $lowValue);

            $qry = "SELECT * FROM tbl_location WHERE location_coords='$highValue' AND user_id='$currentUser' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1)
                $this->goFightNow($bestOption, $highValue);


            $qry = "SELECT * FROM tbl_location WHERE location_coords='$upValue' AND user_id='$currentUser' ";

            echo $qry . '<br>';
            $checkResult = $this->m_objConn->query($qry);
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1)
                $this->goFightNow($bestOption, $upValue);

            $qry = "SELECT * FROM tbl_location WHERE location_coords='$downValue' AND user_id='$currentUser' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRows = $checkResult->num_rows;

            if ($checkRows == 1)
                $this->goFightNow($bestOption, $downValue);
        }
        else {
#check to see if soldiers take already.
            $rumQry = "SELECT * FROM tbl_army_data WHERE game_id='$this->game_id' AND user_id='$currentUser' ";
            $rumResult = $this->m_objConn->query($rumQry);
            $rumRow = $rumResult->fetch_object();


            $qry = "SELECT * FROM tbl_mapdata WHERE map_number='$mapID' AND map_identifer='$this->game_id' AND map_coords='$bestOption' ";
            echo $qry . '<br>';

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();

            if ($checkRows >= 1)
                $newSoliders = $rumRow->army_soldiers;
            else
                $newSoliders = $rumRow->army_soldiers + $checkRow->map_soldiers;

            echo '<table>';
            echo '<tr><td>Current Soliders: </td><td>' . $rumRow->army_soldiers . '</td></tr>';
            echo '<tr><td>New Soliders: </td><td>' . $newSoliders . '</td></tr>';

            echo '</table>';


            $qry = "UPDATE tbl_location SET location_army='No'  WHERE game_id='$this->game_id' AND user_id='$currentUser' AND location_army='Yes' ";
            $qry1 = "UPDATE tbl_army_data SET army_soldiers='$newSoliders'  WHERE game_id='$this->game_id' AND user_id='$currentUser' ";



#check if location is already in database. if not insert if it is Update.
# $polishQry = "SELECT * FROM tbl_location WHERE location_coords='$tempVar[1]' ";
# $polishResult = $this->m_objConn->query($polishQry);
# $polishRows = $polishResult->num_rows;
#  if ($polishRows>=1)
#       $qry2 = "UPDATE tbl_location SET location_army='Yes'  WHERE game_id='$this->game_id' AND location_coords='$tempVar[1]' ";
# else
            $qry2 = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '$this->game_id', '$currentUser', '$bestOption', 'Yes', 'Yes')";

            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
            mysqli_query($this->m_objConn, $qry1) or die(mysqli_error());
            mysqli_query($this->m_objConn, $qry2) or die(mysqli_error());

            $qry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
            $checkResult = $this->m_objConn->query($qry);

            $newRow = $checkResult->fetch_object();

            if ($newRow->user_id1 == $newRow->user_turn)
                $nextTurn = $newRow->user_id2;

            if ($newRow->user_id2 == $newRow->user_turn)
                $nextTurn = $newRow->user_id3;

            if ($newRow->user_id3 == $newRow->user_turn)
                $nextTurn = $newRow->user_id4;

            if ($newRow->user_id4 == $newRow->user_turn)
                $nextTurn = $newRow->user_id1;

            $qry = "UPDATE tbl_game SET user_turn='$nextTurn '  WHERE game_id='$this->game_id' ";
            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
            echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '">';
        }
    }

    public function goFightNow($locationID1, $locationID2) {
        echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&Fight=1&&GameID=' . $this->game_id . '&&ID1=' . $locationID1 . '&&ID2=' . $locationID2 . '">';
#  echo 'Location1 : '.$locationID1.'<br>';
#  echo 'Location2 : '.$locationID2.'<br>'; 
    }

    public function checkMovement($locationID) {
        
    }

    public function defenderAI($ID) {
#needs to determine who has what. 
#needs to determine when to buy army.

        echo 'Control A1 for: ' . $ID . '<br>';

        $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$ID' AND location_army='Yes' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRow = $newResult->fetch_object();
        echo 'Current Location: ' . $newRow->location_coords . '<br>';


        echo 'Possible Movement: <br><br>';
        $testLocation = $newRow->location_coords;

#Move one Space.
#Need to make Array for movement.
        $moveArray = array();

        $tempVar1 = substr($testLocation, 0, 1);
        $tempVar2 = substr($testLocation, 1, 1);


        $lowValue = ($tempVar2 - 1);

        if (($lowValue) >= 1) {
            $firstLocation = $this->getLocationAmount($tempVar1 . $lowValue);
            $moveArray[] = $firstLocation;
#  echo 'Left Side: '.$tempVar1.$lowValue.'<br>';
        }

        $highValue = ($tempVar2 + 1);

        if (($highValue) <= 8) {
            $secondLocation = $this->getLocationAmount($tempVar1 . $highValue);
#     echo 'Right Side: '.$tempVar1.$highValue.'<br>'; 
            $moveArray[] = $secondLocation;
        }


        echo '<br>';
        $newArray1 = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $x = 0;
        $y = 1;
        foreach ($newArray1 as &$value) {
            if ($x != 0) {
                # echo  $value.' :: '.$x.'<br>';

                if ($tempVar1 == $value AND $x >= 2) {
                    $thirdLocation = $this->getLocationAmount($newArray1[$x - 1] . $tempVar2);
                    $moveArray[] = $thirdLocation;
                    $getValue3 = 'map_' . $newArray1[$x - 1] . $tempVar2;
                    #   echo 'Move Up: '.$newArray1[$x-1].$tempVar2.'<br>';
                }
                if ($tempVar1 == $value AND $x != 8) {
                    $fourthLocation = $this->getLocationAmount($newArray1[$x + 1] . $tempVar2);
                    $moveArray[] = $fourthLocation;
                    $getValue4 = 'map_' . $newArray1[$x + 1] . $tempVar2;
                    #   echo 'Move Down: '.$newArray1[$x+1].$tempVar2.'<br>';
                }
            }
            $x++;
        }

        sort($moveArray);

        foreach ($moveArray as &$value) {
            $bestOption = $value;
        }

        $getValue1 = 'map_' . $tempVar1 . $lowValue;
#  echo 'L1: '.$tempVar1.$lowValue.'<br>';
        $getValue2 = 'map_' . $tempVar1 . $highValue;

        echo 'L1: ' . $getValue1 . '<br>';
        echo 'L2: ' . $getValue2 . '<br>';
        echo 'L3: ' . $getValue3 . '<br>';
        echo 'L4: ' . $getValue4 . '<br>';

        $qry = "SELECT * FROM tbl_map_data WHERE map_id='$this->map_id' ";
        $checkResult = $this->m_objConn->query($qry);

        $checkRow = $checkResult->fetch_object();

        if ($checkRow->$getValue1 == $bestOption)
            $bestLocation = $getValue1;

        if ($checkRow->$getValue2 == $bestOption)
            $bestLocation = $getValue2;
        if ($checkRow->$getValue3 == $bestOption)
            $bestLocation = $getValue3;
        if ($checkRow->$getValue4 == $bestOption)
            $bestLocation = $getValue4;


        echo 'Best: ' . $bestOption . '<br>';


        $takQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' ";
        $takeResult = $this->m_objConn->query($takQry);
        $takeRows = $takeResult->num_rows;
        $takeRow = $takeResult->fetch_object();


        $qry = "UPDATE tbl_location SET location_army='No'  WHERE game_id='$this->game_id' AND user_id='$ID' ";

        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());


        $tempValue = explode('_', $bestLocation);


        $multipleQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND location_coords='$tempValue[1]' ";
        $multiResult = $this->m_objConn->query($multipleQry);
        $multiRows = $multiResult->num_rows;
        $multiRow = $multiResult->fetch_object();

        if ($multiRows == 0) {
            $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '$this->game_id', '$ID', '$tempValue[1]', 'Yes', 'Yes') ";

            echo $qry . '<br>';

            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        } else {
            echo 'Multiple Entries for ' . $tempValue[1] . '<br>';
            exit;
        }

        $Qry = "INSERT INTO tbl_game_data (user_id, game_id, location_coords, data_soldiers) VALUES ( '$ID', '$this->game_id',  '$tempValue[1]', '$bestOption') ";

        echo $Qry . '<br>';

        mysqli_query($this->m_objConn, $Qry) or die(mysqli_error());
    }

    public function getNextEmpty($ID) {
        $qry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$ID' ORDER BY location_coords ";
        $checkResult = $this->m_objConn->query($qry);
        echo '<table>';
        echo '<tr><td>Location </td><td> User </td></tr>';

        while ($checkRow = $checkResult->fetch_object()) {
            echo '<tr><td>' . $checkRow->location_coords . '</td><td>' . $checkRow->user_id . '</td></tr>';
            $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND location_coords='$checkRow->location_coords' AND user_id!='$checkRow->user_id' ";
            $newResult = $this->m_objConn->query($newQry);
            while ($newRow = $newResult->fetch_object()) {
                echo '<tr><td><font color=red>' . $newRow->location_coords . '</td><td><font color=red>' . $newRow->user_id . '</td></tr>';
            }
        }
        echo '</table>';
    }

    private function getLocationAmount($locationToFind) {
        $mapLocation = 'map_' . $locationToFind;

        $newQry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND location_coords='$locationToFind' AND location_vassal_collect!='' ";
        $newResult = $this->m_objConn->query($newQry);

        $newRows = isset($newResult->num_rows) ? $newResult->num_rows : NULL;

        $qry = "SELECT * FROM tbl_map_data WHERE map_id='$this->map_id' ";

#   echo 'Q: '.$qry.'<br>';

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        echo 'Location: ' . $locationToFind . ' Vassels: ' . $checkRow->$mapLocation . '<br>';

        return $checkRow->$mapLocation;

        if (!$newRows)
            echo 'Location Not Taken. <br>';
    }

    public function gameCreation() {
        $newDate = date('Y-m-d');

#Game ID Number
        $newQry = "SELECT * FROM tbl_map_information";
        $newResult = $this->m_objConn->query($newQry);
        $newRows = $newResult->num_rows;

        $map_id = 2;
# $map_id = rand(1, $newRows);

        $gameQry = "SELECT * FROM tbl_mapdata ORDER BY game_id DESC LIMIT 1";
        $gameResult = $this->m_objConn->query($gameQry);
        $gameRow = $gameResult->fetch_object();

        $mapIdent = isset($gameRow->game_id) ? ($gameRow->game_id + 1) : 1;

        $checkQry = "SELECT * FROM tbl_map_static WHERE static_map='$map_id' ";
        $checkResult = $this->m_objConn->query($checkQry);
        while ($checkRow = $checkResult->fetch_object()) {


            $mapMen = rand(1, 9);
            $mapMoney = rand(1, 9);

            $qry = "INSERT INTO tbl_mapdata (game_id, map_number, map_coords, map_income, map_vassals) VALUES ( '" . $mapIdent . "', '" . $map_id . "', '" . $checkRow->static_coords . "', '" . $mapMoney . "', '" . $mapMen . "')";

# echo 'q:'.$qry.'<br>';

            mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        }


        $qry = "INSERT INTO tbl_game (game_date, map_id, user_id1, user_id2, user_id3, user_id4, user_turn, game_status  ) VALUES ( '" . $newDate . "', '" . $map_id . "', '" . $this->user_id . "', '2', '3', '4', '" . $this->user_id . "', 'Active')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

        $gameQry = "SELECT * FROM tbl_game ORDER BY game_id DESC LIMIT 1";
        $gameResult = $this->m_objConn->query($gameQry);
        $gameRow = $gameResult->fetch_object();

        $mapQry = "SELECT * FROM tbl_map_information WHERE map_id='$map_id' ";
        $mapResult = $this->m_objConn->query($mapQry);
        $mapRow = $mapResult->fetch_object();

        $checkQry = "SELECT * FROM tbl_mapdata WHERE map_number='$map_id' ";
        # echo $checkQry . '<br>';

        $checkResult = $this->m_objConn->query($checkQry);
        while ($checkRow = $checkResult->fetch_object()) {
            if ($checkRow->map_coords == $mapRow->map_start_location1) {
                $datSoldiers1 = $checkRow->map_vassals;
                $datIncome1 = $checkRow->map_income;
            }

            if ($checkRow->map_coords == $mapRow->map_start_location2) {
                $datSoldiers2 = $checkRow->map_vassals;
                $datIncome2 = $checkRow->map_income;
            }
            if ($checkRow->map_coords == $mapRow->map_start_location3) {
                $datSoldiers3 = $checkRow->map_vassals;
                $datIncome3 = $checkRow->map_income;
            }
            if ($checkRow->map_coords == $mapRow->map_start_location4) {
                $datSoldiers4 = $checkRow->map_vassals;
                $datIncome4 = $checkRow->map_income;
            }
        }

#Player One Data
        $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '" . $gameRow->game_id . "', '" . $this->user_id . "', '$mapRow->map_start_location1', 'Yes', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_game_data (game_id, user_id, location_coords, game_income, data_soldiers, data_knights, data_catapults, data_start) VALUES ( '" . $gameRow->game_id . "', '" . $this->user_id . "', '$mapRow->map_start_location1', '$datIncome1', '$datSoldiers1', '0', '0', 'Yes')";
        # echo 'Q: ' . $qry . '<br>';
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_army_data (game_id, user_id, army_soldiers) VALUES ( '" . $gameRow->game_id . "', '" . $this->user_id . "', '$datSoldiers1')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

#Player Two Data
        $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '" . $gameRow->game_id . "', '2', '$mapRow->map_start_location2', 'Yes', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_game_data (game_id, user_id, location_coords, game_income, data_soldiers, data_knights, data_catapults, data_start) VALUES ( '" . $gameRow->game_id . "', '2', '$mapRow->map_start_location2', '$datIncome2', '$datSoldiers2', '0', '0', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_army_data (game_id, user_id, army_soldiers) VALUES ( '" . $gameRow->game_id . "', '2', '$datSoldiers2')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

#Player Three Data
        $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '" . $gameRow->game_id . "', '3', '$mapRow->map_start_location3', 'Yes', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_game_data (game_id, user_id, location_coords, game_income, data_soldiers, data_knights, data_catapults, data_start) VALUES ( '" . $gameRow->game_id . "', '3', '$mapRow->map_start_location3', '$datIncome3', '$datSoldiers3', '0', '0', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_army_data (game_id, user_id, army_soldiers) VALUES ( '" . $gameRow->game_id . "', '3', '$datSoldiers3')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());

#Player Four Data
        $qry = "INSERT INTO tbl_location (game_id, user_id, location_coords, location_army, location_vassal_collected) VALUES ( '" . $gameRow->game_id . "', '4', '$mapRow->map_start_location4', 'Yes', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_game_data (game_id, user_id, location_coords, game_income, data_soldiers, data_knights, data_catapults, data_start) VALUES ( '" . $gameRow->game_id . "', '4', '$mapRow->map_start_location4', '$datIncome4', '$datSoldiers4', '0', '0', 'Yes')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());
        $qry = "INSERT INTO tbl_army_data (game_id, user_id, army_soldiers) VALUES ( '" . $gameRow->game_id . "', '4', '$datSoldiers4')";
        mysqli_query($this->m_objConn, $qry) or die(mysqli_error());


        echo 'Please Wait.....<br>';
        echo '<meta http-equiv="refresh" content="4;url=' . $_SERVER['PHP_SELF'] . '?sct=PlayScreen">';
    }

    public function getPlayerStatus() {

        $gameIncome = 0;
        $newQry = "SELECT * FROM tbl_users WHERE user_id='$this->user_id' ";

        $newResult = $this->m_objConn->query($newQry);
        $newRow = $newResult->fetch_object();

        $arrQry = "SELECT * FROM tbl_army_data WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";

        $arrResult = $this->m_objConn->query($arrQry);
        $arrRow = $arrResult->fetch_object();

        $Qry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$this->user_id' ORDER BY location_coords ";
        # echo $Qry . '<br>';

        $checkResult = $this->m_objConn->query($Qry);
        while ($checkRow = $checkResult->fetch_object()) {

            $getQry = "SELECT * FROM tbl_mapdata WHERE map_coords='$checkRow->location_coords' AND game_id='$this->game_id' ";

            $getResult = $this->m_objConn->query($getQry);
            $getRow = $getResult->fetch_object();

            $gameIncome+= $getRow->map_income;
            echo 'Q: ' . $checkRow->location_coords . ' :: ' . $getRow->map_income . ' :: ' . $gameIncome . '<br>';
        }

        echo '<table>';
        echo '<tr><td>' . $newRow->user_fullname . '</td></tr>';
        echo '<tr><td>Soliders: ' . $arrRow->army_soldiers . ' </td></tr>';
        echo '<tr><td>Knights: ' . $arrRow->army_knights . '  </td></tr>';
        echo '<tr><td>Catapults: ' . $arrRow->army_catapults . '  </td></tr>';
        echo '<tr><td><br>Turn Income: ' . $gameIncome . '  </td></tr>';
        echo '<tr><td> </td></tr>';
        echo '</table>';
    }

    public function windowOptions($option) {
        $option = (!$option OR $option == 0) ? 1 : $option;

        if ($option == 1) {
            echo '<table border=0>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=2">Conquest</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=3">Buy Army</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=4">Read Map</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=PassPlayer&&GameID=' . $this->game_id . '">Pass</td></tr>';
            echo '</table>';
        }

        if ($option == 2) {
            echo '<table border=0>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=5">Move Army</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=2"> </td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=3">Back</td></tr>';
            echo '</table>';
        }

        if ($option == 3) {
            echo '<table border=0>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=2">Conquest</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=3">Buy Army</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=GameScreen&&GameID=' . $this->game_id . '&&Select=4">Read Map</td></tr>';
            echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?sct=Pass&&GameID=' . $this->game_id . '">Pass</td></tr>';
            echo '</table>';
        }
    }

    public function gameWindow() {

        echo '<table width=1000 bordercolor=blue border=1>';
        echo '<tr><td>';
        $readMapData = isset($_GET['ReadMap']) ? $_GET['ReadMap'] : NULL;

        if ($readMapData == 'Yes') {
            $qry = "SELECT * FROM tbl_mapdata WHERE game_id='$this->game_id' AND map_number='$this->map_id' AND map_coords='$this->mapCoords' ";

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();

            echo '<div id="dialog1" title="Twilight Event">';
            echo '<p> <b>Title:</b> <i>' . $checkRow->map_coords . '</i></p>';
            echo '<p> <b>Income:</b> <i> ' . $checkRow->map_income . '</i></p>';
            echo '<p> <b>Vassals:</b> <i> ' . $checkRow->map_vassals . '</i></p>';
            echo '</div>';
        }
        $fightTime = (isset($_GET['Fight'])) ? TRUE : NULL;
        $readMap = (isset($_GET['menuOption'])) ? $_GET['menuOption'] : NULL;

        $newArray1 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I');


        $mapQry = "SELECT * FROM tbl_game WHERE game_id='$this->game_id' ";
        $mapResult = $this->m_objConn->query($mapQry);

        $mapRow = $mapResult->fetch_object();

        $nextTurn = $mapRow->user_turn;
        if (!$fightTime) {
            if ($nextTurn == '2' OR $nextTurn == '3' OR $nextTurn == '4')
                echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '?sct=Pass&&GameID=' . $this->game_id . '&&ID=' . $nextTurn . '">';
        }
        $newQry = "SELECT * FROM tbl_game WHERE map_id='$this->map_id' ";
        $newResult = $this->m_objConn->query($newQry);
        $newRow = $newResult->fetch_object();

        $forgeQry = "SELECT * FROM tbl_map_information WHERE map_id='$this->map_id' ";
        $forgeResult = $this->m_objConn->query($forgeQry);
        $forgeRow = $forgeResult->fetch_object();

        echo '<table  border=1 bordercolor=red height=549 width=800 background="Images/' . $forgeRow->map_image . '">';

        for ($y = 0; $y <= 7; $y++) {
            echo '<tr>';
            for ($x = 1; $x <= 8; $x++) {
                $checkLink = $this->checkLocationLink($this->map_id, $newArray1[$y] . $x);
                $checkMovement = $this->checkAvailablity($this->map_id, $this->game_id, $newArray1[$y] . $x);



                if ($checkMovement == TRUE AND $this->sessionID == $nextTurn)
                    echo '<td width=100 height=65 style="cursor:hand" onclick="window.location.href = \'' . $_SERVER['PHP_SELF'] . '?displayCode=UpdatePlayer&&playerID=' . $this->user_id . '&&GameID=' . $this->game_id . '&&location=' . $newArray1[$y] . $x . '\'"><center>' . $checkLink . ' ' . $newArray1[$y] . $x;
                elseif ($readMap == 'ReadMap' AND $checkLink >= 1) {

                    $staticQry = "SELECT * FROM tbl_map_static WHERE static_map='$this->map_id' AND static_coords='$newArray1[$y]$x'";

                    $staticResult = $this->m_objConn->query($staticQry);
                    $staticRow = $staticResult->fetch_object();

                    echo '<td width=100 height=65 ><a class="alertWindow" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&ReadMap=Yes&&mapID=' . $this->map_id . '&&playerID=' . $this->user_id . '&&GameID=' . $this->game_id . '&&location=' . $newArray1[$y] . $x . '" title="' . $staticRow->static_name . '"><span title=" "><center>' . $checkLink . ' ' . $newArray1[$y] . $x . '</span>';
                } else
                    echo '<td width=100 height=65><center> ' . $newArray1[$y] . $x;

                # $this->checkAvailablity($this->map_id, $newArray1[$y] . $x, $this->game_id);
                if ($checkLink >= 1)
                    $this->displayArmys($this->game_id, $newArray1[$y] . $x);
                echo '</td>';
            }
            echo '</tr>';
        }
        echo '<tr><td>';




        echo '</td></tr>';
        echo '</table>';
        
        echo '</td><td width=200></td></tr>';
        echo '</table>';
    }

    public function displayArmys($gameID, $locationID) {

        $playerIcon = NULL;
        $qry = "SELECT * FROM tbl_location WHERE game_id='$gameID' AND location_coords='$locationID' ";
        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();
        $checkRows = $checkResult->num_rows;

        if ($checkRows) {
            $userID = (isset($checkRow->user_id)) ? $checkRow->user_id : NULL;


            $qry = "SELECT * FROM tbl_game WHERE game_id='$gameID' ";
            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();



            $playerIcon = ($checkRow->user_id1 == $userID) ? 'user_id1' : NULL;
            $playerIcon = ($checkRow->user_id2 == $userID) ? 'user_id2' : $playerIcon;
            $playerIcon = ($checkRow->user_id3 == $userID) ? 'user_id3' : $playerIcon;
            $playerIcon = ($checkRow->user_id4 == $userID) ? 'user_id4' : $playerIcon;

            $iconID = substr($playerIcon, 7, 1);

            $IconQry = "SELECT * FROM tbl_player_icons WHERE icon_user='$iconID' ";

            $iconResult = $this->m_objConn->query($IconQry);
            $iconRow = $iconResult->fetch_object();
            $iconRows = $iconResult->num_rows;


            $qry = "SELECT * FROM tbl_location WHERE location_coords='$locationID' AND location_army='Yes' AND game_id='$gameID' ";

            #    echo 'CheckQry: '.$qry.'<br>';

            $checkResult = $this->m_objConn->query($qry);
            $checkRow = $checkResult->fetch_object();
            $checkRows = $checkResult->num_rows;

            if ($checkRows) {

                # echo 'Session: '.$this->sessionID.' :: UserID: '.$userID.'<br>';

                if ($this->sessionID == $userID)
                    echo '<img src="Images/' . $iconRow->icon_horse . '" height=14>';
                else
                    echo '<img src="Images/' . $iconRow->icon_shield . '" height=14>';
            } else {
                echo '<img src="Images/' . $iconRow->icon_shield . '" height=14>';
            }
        }
    }

    public function checkAvailablity($mapID, $gameID, $testLocation) {

        $qry = "SELECT * FROM tbl_game WHERE game_id='$gameID' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        $whosTurn = $checkRow->user_turn;

        $qry = "SELECT * FROM tbl_location WHERE game_id='$gameID' AND user_id='$whosTurn' AND location_army='Yes' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRows = $checkResult->num_rows;

        $checkRow = $checkResult->fetch_object();

        $verticalArray = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        $tempVar1 = substr($checkRow->location_coords, 0, 1);
        $tempVar2 = substr($checkRow->location_coords, 1, 1);


        $leftValue = $tempVar1 . ($tempVar2 - 1);
        $rightValue = $tempVar1 . ($tempVar2 + 1);

        $tempValue = array_search($tempVar1, $verticalArray);
        $topValue = $verticalArray[$tempValue - 1] . $tempVar2;
        $bottomValue = $verticalArray[$tempValue + 1] . $tempVar2;


        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$rightValue' AND game_id='$gameID' ";

        $checkResult = $this->m_objConn->query($qry);
        $rightRows = $checkResult->num_rows;

        if ($rightValue == $testLocation) {
            if ($rightRows == 1)
                return TRUE;
        }
        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$topValue' AND game_id='$gameID' ";

        $checkResult = $this->m_objConn->query($qry);
        $topRows = $checkResult->num_rows;

        if ($topValue == $testLocation) {
            if ($topRows == 1)
                return TRUE;
        }

        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$bottomValue' AND game_id='$gameID' ";

        $checkResult = $this->m_objConn->query($qry);
        $bottomRows = $checkResult->num_rows;

        if ($bottomValue == $testLocation) {
            if ($bottomRows == 1)
                return TRUE;
        }

        $qry = "SELECT * FROM tbl_mapdata WHERE map_coords='$leftValue' AND game_id='$gameID' ";

        $checkResult = $this->m_objConn->query($qry);
        $leftRows = $checkResult->num_rows;

        if ($leftValue == $testLocation) {
            if ($leftRows == 1)
                return TRUE;
        }
    }

    public function checkLocationLink($mapID, $testLocation) {
#echo 'MapID: '.$mapID.' :: '.$testLocation.'<br>';

        $qry = "SELECT * FROM tbl_mapdata WHERE map_number='$mapID' AND map_coords='$testLocation' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        if (isset($checkRow->map_income))
            return $checkRow->map_income;



        #    $verticalArray = array('', 'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
        #    $tempVar1 = substr($testLocation, 0, 1);
        #    $tempVar2 = substr($testLocation, 1, 1);
        #    echo $testLocation.'::'.$tempVar1.$tempVar2.'<br>';
#   $locationID = 'map_'.$testLocation;
#    $newQry = "SELECT * FROM tbl_location WHERE user_id='$this->user_id' AND game_id='$this->game_id' AND location_army='Yes' ";
#    $newResult = $this->m_objConn->query($newQry);
#   $newRow = $newResult->fetch_object();
#   $armyLocation = $newRow->location_coords;
#    $needVar1 = substr($newRow->location_coords, 0, 1);
#    $needVar2 = substr($newRow->location_coords, 1, 1);
#    $leftValue =  ($needVar2-1);
#    $rightValue = ($needVar2+1);
#    $yesLeft = $needVar1.$leftValue;
#    $yesRight = $needVar1.$rightValue; 
#   $getLeft = array_search($needVar1, $verticalArray);
#   $yesUp = $verticalArray[$getLeft-1].$needVar2;
#    $yesDown = $verticalArray[$getLeft+1].$needVar2;
#      echo '<br>Army: '.$armyLocation.' :: '.$verticalArray[$getLeft-1].$needVar2.'<br>';
#      echo 'Location: '.$testLocation.' Link Needs to be '.$yesLeft.'<br>';
#    $mapQry = "SELECT * FROM tbl_mapdata WHERE map_id='$mapID' ";
#    $mapResult = $this->m_objConn->query($mapQry);
#    $mapRow = $mapResult->fetch_object();
#
#Left and Right.
#   echo 'Map Ref: '.$testLocation.' LV: '.$yesLeft.' LLV: '.$yesRight.'<br>';
#    if ($yesLeft==$testLocation AND $mapRow->$locationID!=0)
#        return TRUE;
#   elseif ($yesRight==$testLocation AND $mapRow->$locationID!=0)
#       return TRUE;
#    elseif ($yesUp==$testLocation AND $mapRow->$locationID!=0)
#        return TRUE;
#    elseif ($yesDown==$testLocation AND $mapRow->$locationID!=0)
#        return TRUE;
    }

    public function checkLocationLink1($mapID, $testLocation) {
        echo 'User: ' . $this->user_id . '<br>';

        $locationID = 'map_' . $testLocation;

        $newQry = "SELECT * FROM tbl_map_data WHERE map_id='$mapID' ";


        $newResult = $this->m_objConn->query($newQry);
        $newRow = $newResult->fetch_object();


#Move one Space.

        $tempVar1 = substr($testLocation, 0, 1);
        $tempVar2 = substr($testLocation, 1, 1);
# echo '1: '.$tempVar1.'<br>';
# echo '2: '.$tempVar2.'<br>';            


        if ($tempVar1 != 'A' AND $tempVar1 != 'H' AND $tempVar2 != '8' AND $tempVar2 != '1') {

            $lowValue = ($tempVar2 - 1);

            if (($lowValue) >= 2 AND ! isset($firstLocation))
                $firstLocation = $tempVar1 . $lowValue;

            $highValue = ($tempVar2 + 1);

            if (($highValue) <= 7)
                echo 'Right Side';

            echo '<br><br>';
            $newArray1 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
            $x = 1;

            foreach ($newArray1 as &$value) {

                if ($tempVar1 == $value AND $x >= 3)
                    echo 'Move Up!';

                if ($tempVar1 == $value AND $x != 7)
                    echo 'Move Down!';
                $x++;
            }
            echo 'LowValue: ' . $firstLocation . '<br>';
        }
    }

    public function displayLocation($locationID, $gameNumber) {
        $qry = "SELECT * FROM tbl_location, tbl_game WHERE tbl_game.game_id=tbl_location.game_id && tbl_game.game_id='$gameNumber' AND location_coords='$locationID' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRows = isset($checkResult->num_rows) ? $checkResult->num_rows : 0;

        if ($checkRows) {
            $checkRow = $checkResult->fetch_object();

            if (($checkRow->user_id1 == $checkRow->user_id) AND ( $checkRow->location_coords == $locationID)) {
                $newQry = "SELECT * FROM tbl_player_icons WHERE icon_user='1' ";
                $newResult = $this->m_objConn->query($newQry);
                $newRow = $newResult->fetch_object();

                $qry = "SELECT * FROM tbl_location WHERE location_coords='$locationID' AND game_id='$this->game_id' ";
                $arrResult = $this->m_objConn->query($qry);
                $arrRow = $arrResult->fetch_object();

                if ($arrRow->location_army == 'Yes')
                    echo '<img src="Images/' . $newRow->icon_horse . '" width=32>';
                else
                    echo '<img src="Images/' . $newRow->icon_shield . '" width=32>';
            } else
                echo '&nbsp;';

            if (($checkRow->user_id2 == $checkRow->user_id) AND ( $checkRow->location_coords == $locationID)) {
                $newQry = "SELECT * FROM tbl_player_icons WHERE icon_user='$checkRow->user_id2' ";
                $newResult = $this->m_objConn->query($newQry);
                $newRow = $newResult->fetch_object();

                echo '<img src="Images/' . $newRow->icon_shield . '" width=32>';
            }

            if (($checkRow->user_id3 == $checkRow->user_id) AND ( $checkRow->location_coords == $locationID)) {
                $newQry = "SELECT * FROM tbl_player_icons WHERE icon_user='$checkRow->user_id3' ";
                $newResult = $this->m_objConn->query($newQry);
                $newRow = $newResult->fetch_object();

                echo '<img src="Images/' . $newRow->icon_shield . '" width=32>';
            }

            if (($checkRow->user_id4 == $checkRow->user_id) AND ( $checkRow->location_coords == $locationID)) {
                $newQry = "SELECT * FROM tbl_player_icons WHERE icon_user='$checkRow->user_id4' ";
                $newResult = $this->m_objConn->query($newQry);
                $newRow = $newResult->fetch_object();

                echo '<img src="Images/' . $newRow->icon_shield . '" width=32>';
            }
        }
    }

    public function gameList() {
        $players = 1;
        $bgcolor = '000000';

        echo '<table width=100% cellpadding=5 border=0 cellspacing=1>';
        echo '<tr bgcolor=2f2f2f><td width=15></td>'
        . '<td width=200 class="smallFont"><b>Game Name</td>'
        . '<td class="smallFont"><b>Map</td>'
        . '<td width=100 class="smallFont"><b>Players</td>'
        . '<td class="smallFont"><b>Status</td></tr>';

        $qry = "SELECT * FROM tbl_game WHERE game_status!='Completed' ";
        $checkResult = $this->m_objConn->query($qry);
        while ($checkRow = $checkResult->fetch_object()) {
            $checkQry = "SELECT * FROM tbl_game WHERE game_id='$checkRow->game_id' ";
            $getResult = $this->m_objConn->query($checkQry);
            $getRow = $getResult->fetch_object();

            $players += ($getRow->user_id2 >= 1) ? 1 : 0;
            $players += ($getRow->user_id3 >= 1) ? 1 : 0;
            $players += ($getRow->user_id4 >= 1) ? 1 : 0;

            if ($checkRow->game_status == 'In Game')
                $bgcolor = '404040';
            else
                $bgcolor = '000000';
            if ($checkRow->game_password)
                echo '<tr bgcolor=' . $bgcolor . '><td class="smallFont"><img src="Images/lock.png" width=14></td>'
                . '<td class="smallFont"><a href=' . $_SERVER['PHP_SELF'] . '?displayCode=GAMESCREEN&&GameID=' . $checkRow->game_id . '">' . $checkRow->game_name . '</td>'
                . '<td class="smallFont">' . $checkRow->map_id . '</td>'
                . '<td class="smallFont">' . $players . '/4</td>'
                . '<td class="smallFont">' . $checkRow->game_status . '</td></tr>';
            else
                echo '<tr bgcolor=' . $bgcolor . '><td class="smallFont"> </td>'
                . '<td class="smallFont">' . $checkRow->game_name . '</td>'
                . '<td class="smallFont">' . $checkRow->map_id . '</td>'
                . '<td class="smallFont">' . $players . '/4</td>'
                . '<td class="smallFont">' . $checkRow->game_status . '</td></tr>';
            $players = 1;
        }
        echo '</table>';

        echo '<table width=500 cellspacing=10 cellpadding=10>';
        echo '<tr><td><center><a href="' . $_SERVER['PHP_SELF'] . '?displayCode=CreateMGame">Create Game</a> </td><td><center> Filter </td><td><center> Refresh </td><td><center> Join Random </td></tr>';
        echo '</table>';



        echo '</table>';
    }

    public function createMultiplayerGame() {
        #options:
        #password Protect a game
        #should be first
        #how many human players
        #what map
        #difficult of AI
        #invitePlayer
        #4 Options:  easy, Medium, Hard, Warlord, Human.
        echo '<form name="Select" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?displayCode=SubmitMGame" method="post">';
        echo '<table border=1 width=900 height=500 cellspacing=20>';
        echo '<tr><td>Map Gen</td><td rowspan=2>Map And Settings</td></tr>';
        echo '<tr><td rowspan=2>';

        echo '<table width=100% height=100% border=1>';
        echo '<tr><td></td></tr>';
        echo '<tr><td></td></tr>';
        echo '<tr><td></td></tr>';
        echo '<tr><td></td></tr>';
        echo '</table>';

        echo '</td></tr>';
        echo '<tr><td>Start/ Cancel</td></tr>';
        echo '</table>';

        #  echo '<tr><td>MultiplayerScreen</td></tr>';
        #   echo '<tr><td>Game Name </td><td>Password </td><td>Invite </td></tr>';
        #   echo '<tr><td><input type=text name=txtGameName> </td><td><input type=text name=txtGameName> </td><td><input type=text name=txtInvite1> </td></tr>';
        #   echo '<tr><td> </td><td> </td><td><input type=text name=txtInvite2> </td></tr>';
        #   echo '<tr><td> </td><td> </td><td><input type=text name=txtInvite3> </td></tr>';
        #   echo '<tr><td>Playername </td></tr>';
        #   echo '<tr><td>PlayerName </td></tr>';
        #   echo '<tr><td>PlayerName </td></tr>';
        #   echo '<tr><td>PlayerName </td></tr>';
        #   echo '<tr><td><input type=submit name=submit value=Create></td></tr>';
#
        #  echo '</table>';
    }

    public function gameHeader() {

        $gamesQry = "SELECT * FROM tbl_game WHERE user_turn='$this->user_id' ";
        $gamesResult = $this->m_objConn->query($gamesQry);

        $gameRows = $gamesResult->num_rows;

        $messQry = "SELECT * FROM tbl_messages WHERE message_to='$this->user_id' AND message_status='UnRead' ";
        $messResult = $this->m_objConn->query($messQry);

        $messRows = $messResult->num_rows;

        $checkQry = "SELECT * FROM tbl_users WHERE user_username='$this->sessionUsername' ";
        $checkResult = $this->m_objConn->query($checkQry);
        $checkRow = $checkResult->fetch_object();

        echo '<table width=100% border=0 cellpadding=0 cellspacing=0>';
        echo '<tr><td bgcolor=f1f0f0><a href="http://www.sunsetcoders.com/TheCrown/"><img src="Images/header.jpg"></td><td bgcolor=f1f0f0></td><td width=20% bgcolor=f1f0f0>';

        if ($this->sessionUsername) {
            echo '<table>';
            echo '<tr><td>' . $checkRow->user_fullname . ' <a href="' . $_SERVER['PHP_SELF'] . '?displayCode=Logout"><img src="Images/logout.png"></a></td></tr>';
            echo '<tr><td>' . $gameRows . ' games waiting </td></tr>';
            echo '<tr><td>' . $messRows . ' messages waiting</td></tr>';
            echo '</table>';
        }
        echo ' </td></tr>';
        echo '<tr><td colspan=3 bgcolor=darkblue height=20>&nbsp; </td></tr>';
        echo '</table>';
    }

    public function logout() {
        session_destroy();

        echo '<meta http-equiv="refresh" content="0;url=' . $_SERVER['PHP_SELF'] . '">';
    }

    public function playScreen() {

        echo '<table border=1 bordercolor=red width=100% height=100%>';
        echo '<tr><td width=1010 background="Images/flags.jpg" height=186>&nbsp;</td></tr>';
        echo '<tr><td valign=top>';
        echo '<table border=1 bordercolor="#acacac" width=100%>';
        echo '<tr bgcolor="black"><td><font color="White">Game ID</td><td><font color="White">Date</td><td><font color="White">Map</td><td><font color="White">Turn</td></tr>';
        $newQry = "SELECT * FROM tbl_game WHERE game_status!='Completed' AND (user_id1='$this->user_id' OR user_id2='$this->user_id' OR user_id3='$this->user_id' OR user_id4='$this->user_id') ";
        $newResult = $this->m_objConn->query($newQry);
        while ($newRow = $newResult->fetch_object()) {

            $userQry = "SELECT * FROM tbl_users WHERE user_id='$newRow->user_turn' ";

            $userResult = $this->m_objConn->query($userQry);
            $userRow = $userResult->fetch_object();

            $displayStatus = (isset($userRow->user_fullname)) ? $userRow->user_fullname : "Waiting on Players...";


            echo '<tr bgcolor="black"><td><a class="noChange" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $newRow->game_id . '">' . $newRow->game_id . '</td>
                <td><a class="noChange" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $newRow->game_id . '">' . datChange($newRow->game_date) . '</td>
                <td><a class="noChange" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $newRow->game_id . '">Map: ' . $newRow->map_id . ' </td>
                <td><a class="noChange" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&GameID=' . $newRow->game_id . '">' . $displayStatus . ' </td></tr>';
        }
        echo '</table>';


        echo '</td></tr>';
        echo '</table>';
    }

    public function leftMenu() {
        echo '<table>';
        echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?displayCode=NewGame&&ID=' . $this->user_id . '">Skirmish</a></td></tr>';
        echo '<tr><td><a href="' . $_SERVER['PHP_SELF'] . '?displayCode=NewMGame&&ID=' . $this->user_id . '">Multiplayer</a></td></tr>';
        echo '<tr><td>Ranking (coming soon)</td></tr>';
        echo '<tr><td>Help (coming soon)</td></tr>';

        echo '</table>';
    }

    public function loginScreen() {

        echo '<form name="Select" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?displayCode=processLogin" method="post">';
        echo '<table height=100% border=1 width=100%>';
        echo '<tr><td></td></tr>';
        echo '<tr><td height=225><center><img src="Images/header.png"></td></tr>';
        echo '<tr><td height=30><center><font color=white><b>Username</td></tr>';
        echo '<tr><td height=50><center><input type=text name=userName></td></tr>';
        echo '<tr><td height=30><center><font color=white><b>Password</td></tr>';
        echo '<tr><td height=50><center><input type=password name=userPass></td></tr>';
        echo '<tr><td height=30><center><font color=white><b>Email</td></tr>';
        echo '<tr><td height=50><center><input type=text name=txtEmail></td></tr>';
        echo '<tr><td><center><input type=submit name=submit value=Login></td></tr>';
        echo '<tr><td><center><a href="' . $_SERVER['PHP_SELF'] . '?displayCode=Register"><img src="Images/newAccount.png"></td></tr>';
        echo '<tr><td height=30><center><font color=white><b>SatedanSystems<br>Productions</td></tr>';
        echo '<tr><td height=20><center><font color=white><b>TheCrown (c) 2014 SatedanSystem Productions. All Rights Reserved.</td></tr>';
        echo '</table>';
    }

    public function serverStatus() {
        echo '<table>';
        echo '<tr><td><font color=white><b>Beta Server is Running.</td></tr>';
        echo '</table>';
    }

    public function leftGameScreen() {

        $userQry = "SELECT * FROM tbl_users WHERE user_id='$this->user_id' ";

        $checkResult = $this->m_objConn->query($userQry);
        $checkRow = $checkResult->fetch_object();

        $gameIncome = $Gold = 0;
        echo '<table width=100% border=1 cellpadding=5 cellspacing=0>';
        echo '<tr><td><center><img src="Images/' . $checkRow->user_avatar . '"></td></tr>';

        $qry = "SELECT * FROM tbl_location WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";

        $checkResult = $this->m_objConn->query($qry);
        while ($checkRow = $checkResult->fetch_object()) {
            $getQry = "SELECT * FROM tbl_mapdata WHERE map_coords='$checkRow->location_coords' AND game_id='$this->game_id' ";

            $getResult = $this->m_objConn->query($getQry);
            $getRow = $getResult->fetch_object();

            $gameIncome += $getRow->map_income;
        }

        $qry = "SELECT * FROM tbl_army_data WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        $setLeadership = 'Good';
        $setSwordPlay = 'Good';


        echo '<tr><td><font color="Orange"><b>Army:</td></tr>';
        echo '<tr><td class="skyBlue"> :: ' . $checkRow->army_soldiers . ' Soldiers</td></tr>';
        echo '<tr><td class="skyBlue"> :: ' . $checkRow->army_knights . ' Knights</td></tr>';
        echo '<tr><td class="skyBlue"> :: ' . $checkRow->army_catapults . ' Catapults</td></tr>';
        echo '<tr><td><font color=Orange><b>Options</td></tr>';


        if ($this->menuOption == 'Conquest')
            echo '<tr><td class="skyBlue">Move Army </td></tr>';
        elseif ($this->menuOption == 'BuyArmy')
            echo '<tr><td class="skyBlue">Buy Army </td></tr>';
        elseif ($this->menuOption == 'Pass')
            $this->passTurn();
        else {
            echo '<tr><td> :: <a class="skyBlue" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&menuOption=Conquest&&GameID=' . $this->game_id . '">Conquest</td></tr>';
            echo '<tr><td> :: <a class="skyBlue" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&menuOption=BuyArmy&&GameID=' . $this->game_id . '">Buy Army</td></tr>';
            echo '<tr><td> :: <a class="skyBlue" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&menuOption=ReadMap&&GameID=' . $this->game_id . '">Read Map</td></tr>';
            echo '<tr><td> :: <a class="skyBlue" href="' . $_SERVER['PHP_SELF'] . '?displayCode=GameScreen&&menuOption=Pass&&GameID=' . $this->game_id . '">Pass</td></tr>';
        }
        echo '<tr><td><font color="Orange"><b>Attributes:</td></tr>';
        echo '<tr><td class="skyBlue"> :: Leadership ' . $setLeadership . '</td></tr>';
        echo '<tr><td class="skyBlue"> :: SwordPlay ' . $setSwordPlay . '</td></tr>';
        echo '<tr><td></td></tr>';

        $qry = "SELECT * FROM tbl_game_data WHERE game_id='$this->game_id' AND user_id='$this->user_id' ";

        $checkResult = $this->m_objConn->query($qry);
        $checkRow = $checkResult->fetch_object();

        echo '<tr><td><font color="Orange"><b>Current Gold: <font color=green>' . $checkRow->game_gold . '</td></tr>';
        echo '<tr><td><font color="Orange"><b>Income: <font color=green>' . $gameIncome . '</td></tr>';
        echo '</table>';
    }

    public function leftWindow() {

        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $localAction = NULL;
        if (isset($_POST['displayCode']))
            $localAction = $_POST['displayCode'];
        elseif (isset($_GET['displayCode']))
            $localAction = urldecode($_GET['displayCode']);

        Switch (strtoupper($localAction)) {
            case "CHECKFORLINK":
                $this->turnTheLink();
                break;
            case "GAMESCREEN":
                $this->leftGameScreen();
                break;
            default:
                $this->leftMenu();
        }
    }

    public function rightWindow() {

        error_reporting(E_ALL);
        ini_set('display_errors', '1');
        $localAction = NULL;
        if (isset($_POST['displayCode']))
            $localAction = $_POST['displayCode'];
        elseif (isset($_GET['displayCode']))
            $localAction = urldecode($_GET['displayCode']);

        Switch (strtoupper($localAction)) {
            case "UPDATEPLAYER":
                $this->updatePlayerMovement();
                break;
            case "GAMESCREEN":

                $this->gameWindow();
                break;
            case "READMAPWINDOW":
                $this->readMapWindow();
                break;
            case "PROCESSLOGIN":
                $this->processLogin();
                break;
            case "NEWGAME":
                $this->gameCreation();
                break;
            case "LOGOUT":
                $this->logout();
                break;
            case "NEWMGAME":
                $this->gameList();
                break;
            case "CREATEMGAME":
                $this->createMultiplayerGame();
                break;
            default:
                $this->playScreen();
        }
    }

    #login Functions.

    public function processLogin() {
        $userUsername = $_POST['userName'];
        $userPassword = $_POST['userPass'];

        $newQry = "SELECT * FROM tbl_users WHERE user_username='$userUsername' AND user_password='$userPassword' ";

        $newResult = $this->m_objConn->query($newQry);
        $newRow = $newResult->fetch_object();
        $newRows = $newResult->num_rows;

        if ($newRows) {
            $_SESSION['userName'] = $userUsername;
            $_SESSION['userPass'] = $userPassword;

            $_SESSION['userID'] = $newRow->user_id;

            echo '<br><b><center> Login Successful.<br>';
            echo '<meta http-equiv="refresh" content="3;url=' . $_SERVER['PHP_SELF'] . '?displayCode=PlayScreen">';
        }
    }

    public function gameScreen() {
        
    }

    /*
      echo '<table width=100% height=100% border=1>';
      echo '<tr><td bgcolor=black height=200><center> <img src="Images/defenderHeader.jpg"> </td></tr>';
      echo '<tr><td>';

      echo '<form name="Select" enctype="multipart/form-data" action="' . $_SERVER['PHP_SELF'] . '?sct=processLogin" method="post">';
      echo '<table>';
      echo '<tr><td> Username: <input type=text name=userName></td></tr>';
      echo '<tr><td> Password: <input type=password name=userPass></td></tr>';
      echo '<tr><td> <input type=Submit name=Submit></td></tr>';
      echo '</table>';

      echo '</td></tr>';
      echo '<tr><td>';

      #Bottom Section

      echo '<table width=100% height=100% border=1>';
      echo '<tr><td width=50%></td><td width=40%></td><td width=10%>';

      #buttons

      echo '<table width=100% border=1 height=100%>';
      echo '<tr><td>Create Account</td></tr>';
      echo '<tr><td>Forums</td></tr>';
      echo '<tr><td>Support</td></tr>';
      echo '<tr><td></td></tr>';
      echo '</td></tr>';
      echo '</table>';


      echo ' </td></tr>';
      echo '</table>';

      echo '<tr><td height=12><center><font color=white>@ copyright blah blah Game Version 1.02a Terms and Conditions </td></tr>';
      echo '</table>';
      }
     */

    public function splashScreen() {

        if ($this->sessionUsername == NULL AND $_POST['userName'] == NULL) {


            echo '<table width=100% height=100% border=1 >';
            echo '<tr><td colspan=3 height=15>';

            $this->serverStatus();

            echo '</td></tr>';
            echo '<tr><td width=20%></td><td>';

            $this->loginScreen();

            echo '</td><td width=20%></td></tr>';
            echo '</table>';
        } else {
            echo '<table width=100% cellspacing=0 cellpadding=0 border=1 bordercolor=green bgcolor=white>';
            echo '<tr><td colspan=4>';

            echo $this->gameHeader();

            echo '</td></tr>';
            echo '<tr><td></td><td width=200 valign=top>';

            $this->leftWindow();

            echo '</td><td width=1000><center>';

            $this->rightWindow();

            echo '</td><td></td></tr>';
            echo '</table>';
        }
    }

}

?>
