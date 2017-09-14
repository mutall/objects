
<?php
//Returns a page of hinted members of any table in the mutall data model
//This page is not meant for user interaction -- rather it is used for populating
//the articles section of a page via the Ajax mechanism
//
//This page is special in that it does not have the usual sectioning:
//header, articles and footer. It is typically inserted in the articles section 
//of another page
//
//Avail teh session variales
session_start();
//
//To list records, a tablular layout must have been provided by the client
if (isset($_GET['records']))
{
    $json_records= $_GET['records'];
}
//
//Otherwise we cannot continue
else
{
    die("The Records structure not set");
}
//
//Retrieve the records layout
$records_ = json_decode($json_records);
//
//The library of php classes is required
require_once 'library.php';
//
//Set the database connection
$dbase = Dbase::open();
//
$sqlExt = new SqlExt($records_->tname);
//
//Compile the hint condition
//
//Retrieve the hint field
$hintfield = $sqlExt->fields[Field::hint];
//
//Compile the hint field expression
$x1 = $hintfield->fvalue();
//
//Compile the hint text expression;
$x2 = new ExpressionText("%$records_->hint%");
//
//Compile the like binary expression
$hint = new ExpressionBinary($x1, "like", $x2);
//
//Update the where clause of sqlEdit
 $sqlExt->where = $hint;
//
//Debug
//echo print_r((string)$edit, true); die(";");
//
//Create a new records layout to support (a) construction of this page and (b)
//interation with it
$records = new Records($sqlExt);

?>

<html>
    <head>
        <title><?php echo $records_->tname; ?> list</title>
    </head>
    <body>
        <!-- 
        There is no direct interaction with this page, so this scripting section 
        is blank and the scripting library (js) is not needed-->
        <script>
        </script>
        
        <!-- Main Table -->
        <table>
            <?php
            //Display the records layout
            $records->selection_display();
            ?>
        </table>
    </body>
        
</html>
