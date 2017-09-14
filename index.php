<?php
//Use the schema object to show all the mutall prefixed databases on the 
//local server.
//Availa teh sesssion variavles
session_start();

//Include the library
require_once 'library.php';

//Create a schema object that allows us to access all the database through
//the root username and no password
$schema = new Schema("root", "");
//
//Further work thought is needed on this
$schema->id=$schema->get_last_dbase();
//
//Prepare to pass the schema to javascript
$json_schema = json_encode($schema);

//
?>

<html>
    <head>
        <title>Schema</title>

        <link rel="stylesheet" type="text/css" href="list.css">

        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js">
        </script>
        


        <!--Script for defining the objects needed for interacting with this 
        page-->
        <script>
            //
            //Use the php schema details to produce the js version
            var schema = new Schema(<?php echo $json_schema; ?>);
            
        </script>

    </head>
    <body onload="schema.show_selection()">

        <!-- The header section -->
        <header>
        </header>

        <!-- Display the databases
        -->
        <article>

            <table>
                <tr><th>List of Available Databases</th></tr>
                <?php
                $schema->show();
                ?>            
            </table>

        </article>

        <!-- The footer section -->
        <footer>
            <!--
            Log into the selected database -->
            <input type="button" value="Login" onclick='schema.login()'>
            <!--
            View the tables of the selected database-->
            <input type="button" value="View Logged in Dbase" onclick='schema.view_database()'>

            <!-- 
            The logout button will be shown only if there is indeed a logged
            in database from previous session -->
            <input type="button" <?php echo $schema->show_login_value(); ?> onclick='schema.logout()' <?php echo $schema->hide_login(); ?> id="logout">
        </footer>
    </body>

</html>
