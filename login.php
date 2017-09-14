<?php
//
//retrieve the schema
$json_schema= $_GET['schema'];
?>
<html>
    <head>
        <title>Login</title>
        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js"></script>
        
        <script>
           var schema = new Schema(<?php echo $json_schema; ?>);
        </script>
        
    </head>
    <body>
        User name: <input type="text" id="username"/>
        <br/>
        <br/>
        <br/>
        Password: <input type="password" id="password"/>
        <br/>
        <br/>
        <!-- Save the credentials in a session and close this window-->
        <!--the name of the db is part of the schema object -->
        <input type="button" value="Login" onclick="schema.login_save()"/>

    </body>
    
</html>
