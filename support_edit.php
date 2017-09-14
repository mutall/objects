<?php
//This is a standard page. i.e., with header a, articles and footer sections, for
//editing any record in a label layout. It is called with a record where 
//the primary key of the record to be edited and the field to focus on are
//provided.
//
//Access the session variales
session_start();
//
//You require the library of class definitions
require_once 'library.php';
//
//Retrieve the json record that was sent to this page from the
//the client
$json_record = $_GET['record'];

//Convert the json string to a record of the stdClass type, hence the 
//underbar after the name
$record_ = json_decode($json_record);
//
//Get the primary key
$primarykey = $record_->primarykey;
//
//Retrieve teh table name
$tname = $record_->tname;
//
//Open the database
$dbase = Dbase::open();
////
//Create a conditionless sql select statement
$sqlEdit = new SqlEdit($tname);
//
//Get the primary key field from teh basic ones
$pfield = $sqlEdit->basicfields()[Field::primary];
//
//Compile the primary key value expression
$x2 = new ExpressionNumeric($record_->primarykey);
//
//Compile the where clause using primary key condition
$where= new ExpressionBinary($pfield->fvalue(), "=", $x2);
//
//Update where clause of sqlEdit
$sqlEdit->where = $where;
//
//Compile a presentation for the label layout
$label = new Label($sqlEdit, $primarykey, $record_->focus_name);
?>

<html>
    <head>
        <title><?php echo $tname; ?> Edit</title>
        
        <!-- style for controlling record visibility-->
        <link rel="stylesheet" type="text/css" href="record.css">
      
        <!-- Script for linking to the library of Javascript prototypes-->
        <script src="library.js"></script>
        
        <!--Script for initialising the label object to support user interactions-->
        <script>
            <?php
            //Add the index field names to the incoming record
            //
            //Get the stabdard refererence table of sqlEdit
            $reftable = $sqlEdit->reftable();
            //
            //Get the column names and convert
            $cols = $reftable->default_index_cols();
            //
            //Return the index names of teh columns
            $colnames = array_keys($cols);
            //
            //Set the stdClass record sstructure
            $record_->idfnames = $colnames;
            //
            //Convert the complete record to a json string
            $json_record2 = json_encode($record_);
            ?>
            //
            //Get the record from the server directly
            var record_ = <?php echo $json_record2; ?>;
            //
            //Create the Label object uing the data passed via the record 
            //structure
            label  = new Label(record_);
            
        </script>
        
    </head>
    
    <!-- After loading the body, transfer focus to the field name clicked 
    on-->
    <body onload="label.load()">
        
        <!-- Header section-->
        <header>
        </header>
        
        <!-- Article section -->
        <article>
        <?php    
            //Display all the fields of the global record in a label format
            $label->display();
        ?>
        </article>
       
        <!-- footer section -->
        <footer>
        <div>
            <!-- Save the current record based on the old record -->
            <input type='button' value ='Save' onclick='return label.save()' />
            <input type=button value="Cancel" onclick="window.close()"/>
        </div>
            
        <!-- If you click on the error message, you clear it-->    
        <div id="error" onclick="this.innerHTML=''"></div>    
       </footer>
        
    </body>
    
</html>
