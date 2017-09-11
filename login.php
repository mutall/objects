<?php
//Get the database name
$dbname = $_GET['dbname'];
?>
<html>
    <head>
        <title>Login</title>
        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js"></script>
        
        <script>
            var schema = new Schema();
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
        <input type="button" value="Login" onclick="schema.login_save('<?php echo $dbname;?>')"/>

    </body>
    
</html>
