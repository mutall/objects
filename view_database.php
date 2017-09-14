<?php
// This assumes that the user has already logged in to a database. It opens
// that database and lists all the mutall tables that are in it. The user uses
// these tables to navigate around the database
//
//Make the session variables accessible, so that log in can work
session_start(); 
//
//Include the libray of Mutall classes to access the database open static method
require_once "library.php";
//
//Open the database, assuimng that the user jhas already logged in
$dbase = Dbase::open();
?>
<html>
    <head>
        <title>Database Tables</title>
        
        <link rel="stylesheet" type="text/css" href="list.css">
        
        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js"></script>
        
        <!--Script for defining the objects needed for interacting with this page-->
        <script>
            <?php
            //
            //Encode the database for exporting to javascript
            $json_dbase= json_encode($dbase, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            //
            //Test whether the encoding was sucessful or not; dying if not
            if (!$json_dbase)
            {
               die(json_last_error_msg());
            }?>
            //
            //Create a database (of prototype object -- hence the suffixed
            //underbar) from the json string
            var dbase_ = <?php echo $json_dbase; ?>;
            //
            //Create a dbase prototype and add use dbase_ update its properties
            var dbase = new Dbase(dbase_);
            
        </script>
        
         
    </head>
    <body onload="dbase.on_select()">
        
        <!-- The header section -->
        <header>
        </header>
        
        <!-- The articles section. -->
        <article>
            <?php
            //List all the tables of this database
            $dbase->display();
            ?>
        </article>
        
        <!-- The footer section -->
        <footer>
            <input type="button" value="View Table" onclick='dbase.view_table()'>
        </footer>
    </body>
        
</html>
        