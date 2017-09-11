<?php
//Save the login credetials held in a schema to a session

//Login was sucessful: store login data on the server for further sessions
session_start();

//The schema data
$dbase = json_decode($_GET['dbase']);

$_SESSION['dbase']=$dbase;

?>

<html>
    <head>
        <script>
            //
            //Close this window
            window.close();
        </script>
    </head>
</html>