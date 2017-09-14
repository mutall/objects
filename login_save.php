<?php
//Save the login credetials held in a schema to a session

//Login was sucessful: store login data on the server for further sessions
session_start();

//The schema data
$schema = json_decode($_GET['schema']);

$_SESSION['schema']=$schema;

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