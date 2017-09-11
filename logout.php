<?php

    //Logout this session
    session_start();

    //Free all session variables
    session_unset();

    //Destroy all data related to this session
    session_destroy();

?>
<!-- Close this window -->
<html>
    <head>
    <script>
        window.close();
    </script>
    </head>
</html>