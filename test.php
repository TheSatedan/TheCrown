<?php


    $testLocation = 'A8';

    #Move one Space.

    $tempVar1 = substr($testLocation, 0, 1);
    $tempVar2 = substr($testLocation, 1, 1);            
    echo '1: '.$tempVar1.'<br>';
    echo '2: '.$tempVar2.'<br>';            

    $lowValue =  ($tempVar2-1);

    if (($lowValue)>=1)
        echo 'Left Side';

    $highValue = ($tempVar2+1);

    if (($highValue)<=8)
        echo 'Right Side';

    echo '<br><br>';
    $newArray1 = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H');
    $x=1;

    foreach ($newArray1 as &$value) 
    {


        #    echo  $value.' :: '.$x.'<br>';

        if ($tempVar1==$value AND $x>=2)
            echo 'Move Up!';

        if ($tempVar1==$value AND $x!=8)
            echo 'Move Down!';
        $x++;


    }




?>
