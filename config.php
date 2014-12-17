<?php
  session_start();
  

error_reporting(E_ALL);
ini_set('display_errors', '1');
echo '<title>Satedan Systems. Computer and Electrical Systems.</title>';
echo '<meta name="description" content="Computer Systems, Computer Repair, Electrical Systems.">';
echo '<meta name="author" content="Andrew Jeffries">';
echo '<script src="js.js"></script>';
echo '  <link rel="stylesheet" href="jquery-ui.css">';
echo ' <script src="jquery-1.10.2.js"></script>';
echo ' <script src="jquery-ui.js"></script>';
?>  <script>
  $(function() {
    $( "#dialog1" ).dialog();
  });
  </script>
 <?php
echo '<body leftmargin=0 rightmargin=0 topmargin=0 bottommargin=0 bgcolor=black>';
echo '<link rel="stylesheet" type="text/css" href="sateda.css">';



function getMsqliConnection()
{
    $authConfig = Array("host" => "localhost", "user" => "root", "password" => "Aort101ms!", "catalogue" => "TheCrown", "adminemail" => "andrew.jeffries@satedansystems.com");

    $mysqli = mysqli_connect($authConfig["host"],$authConfig["user"],$authConfig["password"], $authConfig["catalogue"]);
    return $mysqli;
}

function datChange($datToChange)
{
    $tempVar = explode('-', $datToChange);
    
    return $tempVar[2].'/'.$tempVar[1].'/'.$tempVar[0];
}
?>
