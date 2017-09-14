<?php
//Show records derived from the Hint field of the table provided by the posted
//foreign key field. The Hint field of a table is derived from all the 
//identification and friendly fields of the and table. The records are shown in
// tabuar fashion
// 
// Avail the sesion data
session_start();
//
//The libray contains class definitions needed for creating the objects that
//are used for (a) building this page this page and (b) interacting with it
require_once 'library.php';
//
//Get the foreign key field was passed as a json string
$fkfield = json_decode($_GET["fkfield"]);
//
//Retreve the foreign key field name
$tname = $fkfield->fk_table_name;
//
//Create a database from the login credentials
$dbase=Dbase::open();
//
//Construct the sql exension based on teh foreign key table
$sqlExt = new SqlExt($tname);
//
//Create a new records layout to support (a) construction of this page and (b)
//interation with it. The record id comes from the foreign key field
$records = new Records($sqlExt, $fkfield->id);

?>

<html>
    <head>
        <title>Select <?php $tname ?></title>
        
        <link rel="stylesheet" type="text/css" href="list.css">
        
        <!-- Script for referencing the prototypes for objects needed for 
        interacting with this page -->
        <script src="library.js"></script>
        
        <!--Script for defining the objects needed for interacting with this page-->
        <script>
            //Crete a records object around which the methods of this page 
            //will be organized
            //Compile the php records layout to a json string
            <?php
            $json_records = json_encode($records);
            ?>
            //
            //Now create the records structure
            var records = new Records(<?php echo $json_records; ?>);
            
        </script>
        
         
    </head>
    <!-- Once the body is loaded, mark the current record and hreference it -->
    <body onload="records.show_selection()">
        
        <!-- The header section -->
        <header>
            <!-- Button for client hint -->
            <div>
                <label for ="hint">Filter Client</label>
                <input type ="text" id="hint" onkeyup="records.populate_article(this.value, 'list_selection.php')"/>
            </div>
            
        </header>
        
        <article>
           
            <?php
            //Display the view in a records version
            $records->selection_display();
            ?>
        </article>
        
        <!-- The footer section -->
        <footer>
            <?php echo $tname; ?>: 
            <input type="button" value="Return Selection" onclick='records.return_selection()'>
            <input type="button" value="Edit Selection" onclick='records.modify_record()'>
            <input type="button" value="Add New" onclick='records.add_record()'>
            <input type="button" value="Delete Current" onclick="records.delete()">
            <input type=button value="Cancel" onclick="window.close()"/>
        </footer>
    </body>
        
</html>
