<?php
//
//View the records of a selected table in a records fasshion. The
//table is part of the wider database structure paased to this page
//
//Avail th session variables
session_start();
//
//Retrieve the table that was used to evoke this page from the posted
//database structure
$dbase_ = json_decode($_GET['dbase']);
//
//Get the table name
$tname = $dbase_->tname;
//
require_once "library.php";
//
//Set the database connection
$dbase = Dbase::open();
//
//Create an sql that can support editing?? of database records
$edit = new SqlEdit($tname);
//
//Create a new records layout to support (a) construction of this page and (b)
//interation with it
$records = new Records($edit);
?>
<html>
    <head>
        <title>Client Visits List</title>
        
        <link rel="stylesheet" type="text/css" href="list.css">
        
        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js"></script>
        
        <!--Script for defining the objects needed for interacting with this page-->
        <script>
            //Create a records layout object around which the methods of this page 
            //will be organized
            //
            //Compile the php records layout to a json string
            <?php
            $json_records = json_encode($records);
            ?>
            //
            //Now create the records structure
            var records = new Records(<?php echo $json_records; ?>);
        </script>
        
         
    </head>
    <body onload="records.show_selection()">
        
        <!-- The header section -->
        <header>
            <!-- Button for client hint -->
            <div>
                <label for ="hint">Filter Client</label>
                <input type ="text" id="hint" onkeyup="records.populate_article(this.value, 'list_table.php')"/>
            </div>
            
        </header>
        
        <article>
           
            <?php
            //Display the table's data in a records version
            $records->display();
            ?>
        </article>
        
        <!-- The footer section -->
        <footer>
            <?php echo $tname; ?>: 
            <input type="button" value="Edit Selection" onclick='records.modify_record()'>
            <input type="button" value="Add New" onclick='records.add_record()'>
            <input type="button" value="Delete Current" onclick="records.delete()">
        </footer>
    </body>
        
</html>
