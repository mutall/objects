
<?php
//Returns a page of hinted members of any table in the mutall data model
//This page is not meant for user interaction -- rather it is used for populating
//the articles section of a page via the Ajax mechanism
//
//This page is special in that it does not have the usual sectioning:
//header, articles and footer. It is typically inserted in the articles section 
//of another page
//
//Avail the session variables
session_start();
//
//To list records, a tablular layout must have been provided by the client
if (isset($_GET['tabular']))
{
    $json_tabular= $_GET['tabular'];
}
//
//Otherwise we cannot continue
else
{
    die("The Tabular structure not set");
}
//
//Retrieve the tabular layout
$tabular_ = json_decode($json_tabular);
//
//The library of php classes is required
require_once 'library.php';
//
//Set the database connection
$dbase = Dbase::open();
//
$sqlEdit = new SqlEdit($tabular_->tname);
//
//Compile the hint condition
//
//Retrieve the hint field
$hintfield = $sqlEdit->basicfields()[Field::hint];
//
//Compile the hint field expression
$x1 = $hintfield->fvalue();
//
//Compile the hint text expression;
$x2 = new ExpressionText("%$tabular_->hint%");
//
//Compile the like binary expression
$hint = new ExpressionBinary($x1, "like", $x2);
//
//Update the where clause of sqlEdit
 $sqlEdit->where = $hint;
//
//Create a new tabular layout to support (a) construction of this page and (b)
//interation with it
$tabular = new Tabular($sqlEdit);

?>

<html>
    <head>
        <title><?php echo $tabular_->tname; ?> list</title>
    </head>
    <body>
        <!-- 
        There is no direct interaction with this page, so this scriptng section 
        is blank and the scripting library (js) is not needed-->
        <script>
        </script>
        
        <!-- Main Table -->
        <table>
            <?php
            //Display the tabular layout
            $tabular->display();
            ?>
        </table>
    </body>
        
</html>
