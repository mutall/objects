<?php
//The PHP library supports the creation of Mutall objects that the server uses
//for constructing pages

//Concat is an extension of an exression. It's string value is MySql's concat
//function expression. This expression was introduced to support construction of
//identification sql where id columns needed to be concatenated. Concat accepts
//only basic field values (not compund ones)
class ExpressionConcat extends Expression
{
    //
    function __construct($basicfields)
    {
        $this->basicfields = $basicfields;
    }
    
    //The sql string representation of a concat expression, e.g., concat(x1, x2, ...,xi)
    //xi is the i'th expression to be concatenated
    function __toString()
    {
        //Extract the expression values from the basic fields 
        $values = array_map(function($field){return $field->fvalue();}, $this->basicfields);
        //
        //The value of concat is the function concat(c1,'/', c2, '/', ...) where ci are
        //columns,
        $value = implode(",'/',", $values);
        //
        return "concat($value)";
    }
}

//A inary expression is characterised by an operator. This spports arithmetic,
//boolean and comparsion expressions
class ExpressionBinary extends Expression
{
    function __construct($xp1, $operator, $xp2)
    {
        $this->xp1 = $xp1;
        $this->operator= $operator;
        $this->xp2 = $xp2;
        
    }
    
    //Convert a binary expression to a string
    function __toString() 
    {
        return $this->xp1." ".$this->operator." ".$this->xp2;
    }
}

//Numeric expressions. In contrast to text, numbers are not uotd
class ExpressionNumeric extends Expression
{
    function __construct($number)
    {
        $this->number=$number;
    }
    function __toString() 
    {
        //If a number is set, return it; otherwise retur a null
        if (isset($this->number))
        {
            return $this->number;
        }
        else 
        {
            return "NULL";
        }
            
    }
}

//Text expressions. The key characteristics is the opening/closinng quotes
class ExpressionText extends Expression
{
    function __construct($text)
    {
        $this->text=$text;
    }
    function __toString() 
    {
        //By default texts are singl quote delineated; otherwise with are
        //double quoted.If text as both single and double quies within then we
        //ay that is its malformed 
        return "'". $this->text."'";
    }
}

//This is an expression derived from the table and field names some sql.
class ExpressionColumn extends Expression
{
    //
    public $tname;
    public $fname;
    
    //
    function __construct($tname, $fname)
    {
        $this->tname= $tname;
        $this->fname = $fname;
    } 
    
    function __toString()
    {
        //
        //Compile the string value of a column expression
        return  "`$this->tname`.`$this->fname`";
        
    }
}

//Condition is a named equality expression that is used for expressing join 
//conditions. Equality is a binary expression using the equals operator
class Condition extends expression
{
    public $id;
    //
    //The expressions for which equality is sought
    private $x1;
    private $x2;
    
    //A condition has 3 basic components: a id for indexing the condition in a 
    //multi-condition join; 2 expressions for which equality is sought
    function __construct($id, $x1, $x2)
    {
        $this->id = $id;
        $this->x1 = $x1;
        $this->x2 = $x2;
    }
    
    //The sql string version of a (equality) condition expression required for
    //implementing a join
    function __toString()
    {
        return $this->x1. "=". $this->x2;
    }
}


//A table's column can beused as a basic field of an sql (sql)field and uses 
//the Normal trait methods 
class Column extends FieldBasic
{
    //
    //Table that is  the home of thos column
    private $table;
    //
    //Initialize a column using the given description in a result type
    function __construct($resulttype, $table)
    {
        //Set teh table of this column
        $this->table = $table;
        //
        //Transfer matching fields from $result type to this column
        foreach($resulttype as $key=>$value)
        {
            //
            //Transfer the properteies from resulttype to this field
            $this->$key=$value;
        }
        //
        //The value of a column field is a column expresson of the table 
        //and column name
        $value = new ExpressionColumn($table->name(), $this->column_name);
        //
        //Initialize the basic field. The local name of the field is the 
        //same as theh column name. Its value is the same as column name and
        //the base sql is that of the column
        parent::__construct($value, $this->column_name);
    }
    
    //Initialization of sqlEdit using an ordinary column simply adds a new basic 
    //field to those of edit sql using the name index. The joins are not affected
    function initialize_edit_sql($sqlEdit)
    {
       $sqlEdit->fields[$this->name()] = new FieldBasic($this->fvalue(), $this->name());
    }
    
    //
    //An ordinary column initilaizes the hint sql by adding itsel to this 
    //sqlHitnt's fielda indexed by their names. The joins are not affected
    function initialize_hint_sql($sqlHint)
    {
        //It does not matter if the column is pointing to this sql. The most critical
        //element is that the reference table is the correct one.
        $name = $this->table->name()."_".$this->name();
        //
        //
        $sqlHint->fields[$name]=$this;
    }

    //To convert columns into objects of type string for use in uniquefying 
    //columns use the string of the form table_name.column_name -- i.e., the 
    //value of the column expression
    function __toString() 
    {
        return $this->name()."_".$this->column_name;
    }
    
    //By default any column is nonforeign
    function is_foreign()
    {
        return false;
    }
    
    //        
    //Define these public fields constructted from a stdClass
    public $column_name;
    public $data_type;
    public $character_maximum_length;
    public $column_type;
    public $column_key;
    public $column_comment;
    
    //
    //By default the input type of a normal field or column is text
    function type() {return "text";}
    //
    //By default, a normal field or column is anabled
    function disabled(){return "";}
    //
    //By default the onclick event is igored
    function onclick(){return "return false";}
    //
    //The original value of an input. This is particularly important for hiding 
    //original values of foreign in fields or columns
    public $orgvalue = "";
    
    //By default, a normal field or column is visible, i.e., the hidden attribute is missing
    function hide() {return "";}
    //
    //Returns the table of a column
    public function table()
    {
        return Dbase::tables[$this->name()];
    }
     
    //The name of a column
    function name()
    {
        return $this->column_name;
    }
}

//A primary key column inherits from a normal column and uses the Primary key trait 
class ColumnPrimary extends Column
{
    //
    //Extend the normal column
    function __construct($resulttype, $table)
    {
        parent::__construct($resulttype, $table);
    }
    
    //A field is primary if its source column is also primary
    function is_primary(){return true; }
    
    //
    //Initialization of sqlEdit using this primary key columnn produces a 
    //composite field derived from sqlExt query. The field has 3 subfields: 
    //primary, hint and id.
    // 
    //The joins are simply merged with those of sqlExt
    function initialize_edit_sql($sqlEdit)
    {
        //
        //Formulate table extension sql based on the reference table of sqlEdit
        $sqlExt = new SqlExt($sqlEdit->reftname);
        //
        //Compile the fields of sql ext into a composite field. The data name
        //corresponding to the primary key field is simply hint. 
        $field = new FieldCompound($this, $this->name(), $sqlExt->fields, Field::hint);
        //
        //Add the field to those of the edit sql using the name index
        $sqlEdit->fields[$this->name()]=$field;
        //
        //Transfer the joins of sqlExt to those of sql edit. Its a simple merge.
        //Will the merge respect the array indexing? Check.
        $sqlEdit->joins = array_merge($sqlEdit->joins, $sqlExt->joins);
    }
    
    //
    //It is illeagal to use the primary key field for identication 
    //or decroptition purposes
    function initialize_hint_sql($sqlHint)
    {
        die("Primary key ". $this->name()." in table ". $sqlHint->name(). " should not be used for ientifivarion");
    }

    //
    //It is not useful to show primary keys, so, they are hidden
    function hide() {return "hidden='true'";}
    
}

//A foreign key column inherits from a normal column and uses the Foreing trait
class ColumnForeign extends Column
{
    //
    //Extend the normal column
    function __construct($resulttype, $table, $foreign)
    {
        //Initialize paant with 
        parent::__construct($resulttype, $table);
        //
        $this->foreign = $foreign;
    }
    
    //
    //Initialization of sqlEdit using this foreign key columnn produces a composite
    //field derived from sqlExt query and the foreign key table. The join is a simple
    //left join added to sql Edit
    function initialize_edit_sql($sqlEdit)
    {
        //
        //Formulate sql ext based on the foreign key table name
        $sqlExt = new SqlExt($this->foreign->table_name);
        //
        //Customize the fields of sqlExt so that they are correctly referenced
        //in sqlEdit
        $subfields = array_map(function ($cfield) use($sqlExt)
        {
            //
            //The field value is a column expression whose table is sqlExt and 
            //field name is that of the customized field
            $fvalue = new ExpressionColumn($sqlExt->name(), $cfield->name());
            //
            //The customized name is that of the field name prefixed by the 
            //name of the sqlExt
            $name = $sqlExt->name().$cfield->name();
            //
            //Return a new basic (sub)field
            return new FieldBasic($fvalue, $name);
        }, $sqlExt->fields);
        //
        //The data field name of a coreign key field is hin prefixed with the
        //reference table name
        $data_fname = $sqlExt->name().Field::hint;
        //
        //Compile the fields of sql ext into a composite case
        $field = new FieldCompound($this, $this->name(), $subfields, $data_fname);
        //
        //Add the field to those of the edit sql using the name index
        $sqlEdit->fields[$this->name()]=$field;
        //
        //Add the left join
        //
        //The primary expression is derived from the primary field value of sqlExt
        //and teh primary key field name
        $primaryxp = new ExpressionColumn($sqlExt->name(), Field::primary);
        //
        //The foreign key field is the value of the column expression
        $foreignxp = $this->fvalue();
        //
        //The join condition id is formulated from the foreign key field name
        $cid = $this->foreign->column_name; 
        //
        //Formulate the join condition expression
        $conditionxp = new Condition($cid, $primaryxp, $foreignxp);
        //
        //Formulate the left join
        $join = new Join("LEFT", $sqlExt, $conditionxp);
        //
        //Add it to the edit sql's joins indexed by the name of the sqlExt table
        $sqlEdit->joins[$sqlExt->name()] =  $join;
    }
    
    //
    //This foreign key column initializes the (primary) hint sql by expanding 
    //both the joins and the fields using a secondary hint sql
    function initialize_hint_sql($sqlHintPrimary)
    {
        //
        //Get the foreign key table name
        $fktname = $this->foreign->table_name;
        //
        //Formulate the secondary hint sql using the foreign key table name
        $sqlHintSecondary = new sqlHint($fktname);
        //
        //Update primary fields using the secondary ones
        foreach($sqlHintSecondary->fields as $index=>$field)
        {
            //
            $fvalue = $field->fvalue();
            //
            $name = $field->name();
            //        
            $sqlHintPrimary->fields[$index]=new FieldBasic($fvalue, $name);
        }
        //
        //FRMULATE THE INNER JOIN CORRESPONDING TO THE GIVEN COLUMN
        //
        //Get tHe foreign key table expression
        global $dbase; $fktablexp =$dbase->get_table($fktname); 
        //
        //The primary expression is derived from the primary field value of sqlExt
        //and the primary key field name
        $primaryxp = new ExpressionColumn($fktname, $this->foreign->column_name);
        //
        //The foreign key field is the value of the column expression
        $foreignxp = $this->fvalue();
        //
        //Formulate the indexing name of the condition. This should be the
        //same as the name of this foreign key field
        $cname = $this->name();
        //
        //Formulate the join condition expression
        $conditionxp = new Condition($cname, $primaryxp, $foreignxp);
        //        
        //Use this foreign key column to formulate an inner join expression for 
        //the hint Sql
        $myjoin = new Join("INNER", $fktablexp, $conditionxp);
        //
        //Use the join to update those of the secondary hint sql
        $myjoin->update_sql($sqlHintSecondary);
        //
        //Merge the primary and secondary joins to give the primary ones
        array_walk($sqlHintSecondary->joins, function($primaryjoin) use($sqlHintPrimary)
        {
            $primaryjoin->update_sql($sqlHintPrimary);
        });
    }
    
    //Foreign key details: a stdClass comprising of table_name and column_name
    public $foreign;
    //
    //By default, the foreign key input is of type button
    function type() {return "button";}
    
    //By default a foreign folumn is foreign
    function is_foreign()
    {
        return true;
    }
    //
    //Returns the foreign key table of this field. This is a method -- rather
    //than property, to avoid havig to sort th tables by order of dependency
    function get_foreign_table()
    {
        //Use the global database
        global $dbase;
        //
        //Return the table indexed by the foreign key table bame
        return $dbase->get_table($this->foreign->table_name);
    }
    
    //A foreign key field is displayed as a concatenation of identification
    //fields built from the best index
    function display($layout, $value)
    {
        //Save the original value of the input before it is made user friendly
        $this->orgvalue = $value;
  
        //Foren key fields are not displayed, unless we are in record layout mode
        if ($layout!=View::record_layout) {return; }
        //
        //Get te foreign table name
        $ftable = $this->foreign->table_name;
        //
        //Get teh foreign column name (i.e., the primary key)
        $fcolname = $this->foreign->column_name;
        //
        //Compile the sql statement for selecting the foreign key record
        $sql = "select * from $ftable where $fcolname=$value";
        //
        //Compile the foreign key view
        $fkview = new View($ftable, $sql);
        //
        //Debug
        //echo "<pre>".print_r($fkview, true)."</pre>";
        //
        //Fetch the only resulttype in a view's result
        $resulttype = $fkview->result->fetch_assoc();
        //
        //Check if it is valid
        if (!$resulttype)
        {
            die ("No record found for sql ".$sql);
        } 
        //
        //Create a new record
        $record = new Record($resulttype, $fkview);
        //
        //Retrieve the record's friendly name omponent and treat it as the 
        //modifed bvalue 
        $value2 = $record->name;
        
        //Call the parent display
        parent::display($layout, $value2);
    }
   
    //Display the given value for purpose of editing. It comes from the given
    //compound field
    function display_value($value, $field, $resulttype)
    {        
        echo "<input";
            //
            //The default input type is a buttont
            echo " type='button'";
            //
            //Get the primary key field name of thhis foreign field
            $primary = $field->subfields[Field::primary]->name;
            //
            //The primay key field value
            echo "primarykey='{$resulttype[$primary]}'";
            //
            //The id of this foreign key field -- retrieve it from the result type
            //Firtst get the acutal name of te field
            $id = $field->subfields[Field::id]->name;
            //
            //Retrieve/set the indexed id. Useful for formulating the id component
            echo "id='{$resulttype[$id]}'";
            //
            //Display the input value
            echo " value='$value'";
            //
            //Set the foreign key table for as it is needed for editing
            //foreign keys
            echo " fk_table_name='{$this->foreign->table_name}'";  
            //
            //The onclick event of a foreign key activates record.change_fk()
            echo " onclick='return label.change_fk(this)'";
            //
        //Close the input
        echo " />";
    }
    //
    //Returns an sql expresssion that is a more friendly representation of a
    //foreign key column. The general shape of the expression is 
    //concat(`id1`,'/', `id2`...) 
    //where idi is the i'th identification field of the foreign key table of this
    //field.
    function get_fk_exp()
        {
            //Get the foreign key table of this column
            $fktable = $this->get_foreign_table();
            //
            //Debug
            //echo "<pre>".print_r($this, true)."</pre>"; die("");
            //
            //Get the identification fields of the first index of the foreign 
            //table
            //
            //Get the columns of the default index of the foreign table
            $cols = $fktable->default_index_cols();
            //
            //Map them to their string expressions
            $exps = array_map(function($col){return (string)$col; }, $cols);
            //
            //Concatenate the expressions with a ,'/', separator so that the 
            //values come out slash separated
            $str= implode(", '/',", $exps);
            //
            return "concat($str) AS ". $this->name(). "_". $this->column_name."_ext";
        }    
  }

//The database
class Dbase
{
    public $username;
    public $password;
    
    //The name of the database is used for isolating the table schema
    public $dbname;
    //
    public $conn;
    //
    //The base tables of this database; what about the views?
    private $tables=[];
    // 
    //Use the given credentials to open a new database
    function __construct($username, $password, $dbname)
    {
        $this->username = $username;
        $this->password=$password;
        $this->dbname = $dbname;
        //
        //Establish the connection that sets the static connection property
        $this->connect();
    }
    
    
    //Establich the connection
    function connect()
    {
        //Open the database using the given credentials
        $this->conn=new mysqli("localhost", $this->username, $this->password, $this->dbname);
    }

    //Returns the requested table, first by looking up from protected tables, 
    //then from first principles
    //table 
    function get_table($tname)
    {
        //Return the table if it is set
        if (isset($this->tables[$tname]))
        {
           return $this->tables[$tname];
        }
        //
        //Otherwise create a new standard table from first principles
        else 
        {
            $table = new TableStd($tname, $this);
            //
            //Update this database's table list
            $this->tables[$tname]= $table;
            //
            return $table;
        }

    }
    
    //
    //Get the current period from the database
    function get_current_period()
    {
        //Get the opened connection
        $conn = $this->conn;
        //
        //Formulate teh sql
        $sql = "select year, month from period where is_current";
        //
        //Execute the sql
        if (!$result = $conn->query($sql))
        {
           die ($sql."<br/>".$conn->error);    
        }
        //
        //Fetch the only oe value
        $resulttype = $result->fetch_assoc();
        //
        //There must be at least one current period, otherwise its an error
        if (!$resulttype)
        {
           die ("There must be at least 1 curent period");
        }
        //
        //Return teh year and month
       return $resulttype['month']."/".$resulttype['year'];
    }
    
    //Display the tables of this database
    function display()
    {
        //
        //Selecet all system tables of the current database (ignorig views)
        $sql ="select" 
            ." table_name"
            ." from information_schema.tables"
            ." where table_schema='".$this->dbname."' and table_type='base table'";
        //
        //Now use the sql to query the database (connection). Abort the process in case 
        //of error -- echoing the error message. We assume that teh sql was set by the 
        //caller
        if (!$result = $this->conn->query($sql))
        {
           die ($sql."<br/>".$this->conn->error);    
        }        
        //
        //Define a table
        echo "<table>";
        echo "<tr><th>Database Tables</th></tr>";
        //
        //Visit all the listed records and create a table for each record
        while ($resulttype = $result->fetch_assoc())    
        {
            //Retrieve the table name
            $tname = $resulttype['table_name'];
            //
            echo "<tr tname='$tname' onclick='new Row(this).select();'>";
            echo "<td>$tname</td>";
            echo "</tr>";
        }
        echo "</table>";
        
    }
    
    //Opening a database assumes that the user has already logged in and 
    //that the database parameters are available from session variables
    static public function open()
    {  
        //See if the dabase session variable has been set or not
        if (!isset($_SESSION['dbase']))
        {
            die("Please login to accesss the desired database");
        }
        //
        //Get the database schema
        $dbase= $_SESSION['dbase'];
        //
        //Open and return the requested database
        return new Dbase($dbase->username, $dbase->password, $dbase->dbname);
    }
    
}

//Expression is the root of all sql expresions; it is an abstract class because
//the its string -- the only member of Expression -- must be implemented by 
//classes that extend it
abstract class Expression
{
    //This function is important so that the expression can take part in a
    //concat(.) expression. The string version must be consistent with the 
    //expressions they would appear in an sql statement
    abstract function __toString();
}

//The Field of an sql statement extenda an expression. Every field must be 
//named and must be part of an sql table.
abstract class Field
{
    //Contantas that define special field names in an sql statement. The leading
    //_ is added to prevent possibilities of mixing up these special names with
    //user defined fields.
    const id = "_id";
    const hint="_hint";
    const primary="_primary";
    
    protected $name;
    //
    //A field is characterised by a field name. It would seem like the fvalue
    //of a field is an inportant constructor item, but it is only defined for
    //basic fields. It is not relevant for compound fields.
    function __construct($name)
    {
        //$this->sql = $sql;
        $this->name = $name;
    }
    
    //Tests if this is a primary key field or not. By default every field is 
    //non primary except for compound fields (which may or may not b primary -- 
    //depending on its source (column)
    function is_primary()
    {
        return false;
    }
    
    //By default any field is non-foreign (exept for compound fields whose
    //source is foreign
    function is_foreign(){return false;}
    
    
    //Tests if this field is part of the default id index of the reference table 
    //in which it is taking part
    function field_is_id()
    {
        //Get this field's value
        $fvalue = $this->fvalue();
        //
        //Identification values are always column exressions
        if (get_class($fvalue)==="ExpressionColumn")
        {
            //Get the expression;s table name
            $tname = $fvalue->tname;
            //
            //Get the expression's field name
            $fname = $fvalue->fname;
            //
            //Get teh named table from the global database
            global $dbase;
            $table = $dbase->get_table($tname);
            //
            //Get the default index of this columns source table
            $cols = $table->default_index_cols();
            //
            //See if this column exists
            return array_key_exists($fname, $cols);    
        }    
        //Otherwise this cannot be an indetification field
        else 
        {
            return false;
        }    
        
    }
    
    //Display the field's value in edit mode -- the default mode
    function display_value($value)
    {
        echo "<input";
            //
            //The default input type is text
            echo " type='text'";
            //
            //For ordinary fields, the id component is the same as he field's
            //value. In contrast, the value of a foreign key field is diferent
            //from its id component.
            echo "id='$value''";
            //
            //Display the input value
            echo " value='$value'";
            //
        //Close the input
        echo " />";
    }
    
    //Returns a field name
    function name()
    {
        return $this->name;
    }
    
    //By default the data file name is the same as the name of the field.
    //For a compound field, the data name is supplied during cration of the field
    //as it depends on the context
    function data_fname()
    {
        return $this->name();
    }
    
    //A field can be split into subfields. A basic field splits into a self
    //and a compound feld splits into its children.
    abstract function split();
 
}
 
//A basic field has a only one value. A table column extends a basic field
class FieldBasic extends Field
{
    public $fvalue;
    
    //
    //A basic field is characterised by a value of type expression and an 
    //optional name
    function __construct($fvalue, $name=null)
    {
        $this->fvalue = $fvalue;
        parent::__construct($name);
    }
    
    //Returns the field value as an expression
    function fvalue() {return $this->fvalue;}
    
    //A basic field is split it yields itself
    function split()
    {
        yield $this;
    }
   
    //Returns the alias of a field depending on whether the name is given or not
    function alias() 
    {
       if($this->name)
       {   
           
            return " AS `".$this->name."`";
             
       }
       //Otheerwise the default is no alias
       return "";
    }
    
    //The expression value of a basic string...
    function __toString()
    {
        return (string)$this->fvalue;
    }
    
}


//A compound field is a list that is a a mixture of basic and compound fields
//derived from some column
class FieldCompound extends Field
{
    //The children of a compound field
    public $subfields;
    public $data_fname;
    //
    //The column from which this field is derived
    public $source;
    
   //In addition to the field name, we need to (a) track the original column from 
   //which this compund field is derived, (b) supply the name of the subfield 
   //to be used as the representative data fueld name.
    function __construct($source, $name, $subfields, $data_fname)
    {
        $this->subfields = $subfields;
        $this->data_fname = $data_fname;
        //
        //The original feld -- which may be an ordinary field a foreihn key one
        $this->source = $source;
        
        parent::__construct($name);
    }
    
    //A compound field is primary if its source column is primary
    function is_primary()
    {
        return $this->source->is_primary();
    }
    
    //A compind field is forein if its source column is also foreign
    function is_foreign()
    {
        return $this->source->is_foreign();
    }
    
    
    //The displayed value of a compound field in edit mode is a button;
    //otherwise it is a simple text. The result type is important for 
    //supportin displays of foreign keys
    function display_value($value, $resulttype)
    {
        //The value (to display) of a compound field depends on the
        //field's original source (column)
        $this->source->display_value($value, $this, $resulttype);
    }
    
    //The data field name of a compound field is formulated during creattion 
    //of the field
    function data_fname()
    {
        return$this->data_fname;
    }
    
    //When a compoud field is split, it yields one of its subfields
    function split()
    {
        foreach($this->subfields as $subfield)
        {
            //
            yield $subfield;
        }
    }
    
}

//
//Modelling a join. The general expression of a join is:-
//($hook $joinrtpe JOIN $a on $a->primary_fname()= $h1 and $a->primaryfname = $h2$...)
//The $hook may be a simple database table sql or a sub-query or another join
//expression. The hi are field values of the foreign key fields in the $hook
class Join
{
    //Type of the join -- left or inner
    public $join_type;
    //
    //The foreign key table of a join. The id if a join is derived from this 
    //table.
    public $fktable;
    //
    //The join conditions as a list of equality expressions
    public $conditions;
    
    //A join is basicaly a foreign key table expression followed by join 
    //conditions expressed as equality expressions. More expressions may be 
    //added to the join to take care of multiple conditions. The join type may 
    //inner or left. 
    function __construct($join_type, $fktablexp, $condition)
    {
        $this->join_type = $join_type;
        $this->fktable = $fktablexp;
        //
        //Start a join conditions list using the given expression
        $this->conditions[$condition->id] = $condition;
    }
    
    //Convert the join to a string. It has the form:-
    //$join_type JOIN $fktablexp on $c1 AND $c2 AND $c3...
    //where $ci is the i'th sql condition expression, e.g., client.zone=zone.zone
    function __toString()
    {
        //
        //Map the join condition expressions to thier sql string equivalents
        $condition_strs = array_map(function($condition)
           {
            return (string)$condition;
           }, $this->conditions);
        //
        //And-separate the string conditions
        $condition_str = implode(" AND ", $condition_strs);
        //
        return " $this->join_type JOIN ".$this->fktable->value(). " ON ".$condition_str;
    }
    
    //The id of a join s formulated frppm the foreign key table that is
    //participating in the join
    function id()
    {
        return $this->fktable->name();
    }
    
    //Update the joins of the given sql using this one. This proceeds by either 
    //expanding the condition of an existing join, or adding a complete 
    //new one, or reporting cyclic errors. If a new join is added, it will be
    //indexed by its foreign key table name.
    function update_sql($sql)
    {
        //
        //Test if this join's id is already participaing in the hint joins
        if (array_key_exists($this->id(), $sql->joins))
        {
            //It is; test for cyclic loop
            //
            //Get the identified hint join
            $hintjoin = $sql->joins[$this->id()];
            //
            //See if any of the conditions of this join exists in the hint join.
            foreach($this->conditions as $condition)
            {    
                //Check if this condition exists in the hint join
                if (array_key_exists($condition->id, $hintjoin->conditions))
                {
                    //It does (exist). This is an endless loop. Report it
                    die("Cyclic condition. This foreign key ".$condition->id." is aready in the join ". $hintjoin);
                }
                //
                //Otherwise, add the condition of this join to the hint join
                {
                    $hintjoin->conditions[$condition->id] = $condition;
                }
            }
        }
        //
        //Otherwise, i.e., thi join is not partcipating in the current hint joins
        //Add it, using its id as the index
        else
        {
            $sql->joins[$this->id()] = $this;
        }
    }
}

//Tabular is a data layout that comprises of a headed table
class Tabular extends Layout
{
    public $sqledit;
    
    //A layout is driven by the edit sql; record id to select initially may
    //be given explicity;y or it may be derived from current session variables
    function __construct($sqledit, $id=false)
    {
        //Initialize the parent
        parent::__construct($sqledit);
  
        //Set the table name from the sql reference table name
        $this->tname = $sqledit->reftname;
        //        
        //Set the record id
        //
        //Test if the given id is set or not
        if (!$id)
        {
            //It is not valid, derive it from the session variables
            $this->id = $this->get_session_id();
        }
        //The incoming id is valid. Use it to initialize this id
        else
        {
            $this->id = $id;
        }
    }
    
    //Retrieve the session if if valid; else return a false
    function get_session_id()
    {
        //
        //Confirm that there is indeed an id for this session
        if (isset($_SESSION['id']))
        {
            $id = $_SESSION['id'];
            //
            //See whether the id and reference table names do matche
            if ($id->tname === $this->tname)
            {
                return $id->id;
            }
        }
        //There is no valid id
        return false;
    }
    
    //Display the basic layout shape whose portions get overridden by the 
    //various layout extensions
    function display()
    {
        echo "<table>";
        //
        //Display a header
        $this->display_header();
        //
        //execute the sql to get a result
        $result = $this->sqledit->execute();
        //        
        //Display the rows
        while ($resulttype = $result->fetch_assoc())
        {
            //Use the resulttype to open a td tag as you need to access 
            //primary keys for updates and id for hrereberencing
            $this->open_tr($resulttype);
            //
            //Add the checkbox cell. its id is check and on click it should
            //gravitate current row to the top
            echo "<td><input type='checkbox' id='check' onclick='tabular.gravitate(this);'/></td>";
            //
            //Display the record values based on the sql fields
            foreach($this->sqledit->fields as $field)
            {
                //
                //Get field value, using the field's data field name
                $value = $resulttype[$field->data_fname()];
                //
                //Display a table cell, td. The resulttype is needed to access
                //further details if need be
                $this->display_td($field, $value, $resulttype);
            }
            //
            //Display the close tr tag
            $this->close_tr();
        }
        //
        //Close the table
        echo "</table>";
        
    }
    
    
    //Display the list of table recors for purpose of slecting one member
    function selection_display()
    {
        echo "<table>";
        //
        //Display a header named after table name in sql edit
        echo "<tr>";
        echo "<th>";
        echo "List of ".$this->sqledit->name."s";
        echo "</th>";
        echo "</tr>";
        //
        //Execute the sql to get a result
        $result = $this->sqledit->execute();
        //        
        //Display the rows
        while ($resulttype = $result->fetch_assoc())
        {
            //Use the resulttype to open a td tag as you need to access 
            //primary keys for updates and id for hrereberencing
            $this->open_tr($resulttype);
            //
            //The selection list has only one td cell that holds the hint
            echo "<td>";
            echo $resulttype[Field::hint];
            echo "</td>";
            //
            //Display the close tr tag
            $this->close_tr();
        }
        //
        //Close the table
        echo "</table>";
        
    }
    
    
    //Display the header of a table
    function display_header()
    {
        echo "<tr>";
        //
        //The first header cell is that of a checkbox
        echo "<th>Checked</th>";
        //
        //Visit all the fields of this layout's sql and output each one of them 
        //as a th using the local name. How do we output compound fields?
        array_walk($this->sqledit->fields, 
                function($field)
                {
                    //
                    //Output the header column
                    echo "<th>".$field->name()."</th>"; 
            
                }); 
        //
        echo"</tr>";
    }
    
    //The default display of any value; the field will be useful for data 
    //formatting
    function display_value($value)
    {
        echo $value;
    }
    
    
    //Display the tabular td
    function display_td($field, $value)
    {
        //Open a td
        echo "<td"; 
        //
        //The name of a td is the same as that of the field name
        echo " name='{$field->name()}'";
        //
        //On click mark the current td for future focus
        echo " onclick = 'tabular.mark_td(this)'";
        //
        //Close the td attributes
        echo ">";
        //
        //Output the value
        echo $value;
        //
        //Close teh td tag
        echo "</td>";
    }
    
    //
    function close_tr(){ echo "</tr>"; }
    
    //Open tr tag for a tabular layout. There are 3 attributes for a tr: primary 
    //key to support updates, the id for hreferencing and the onclick event for
    //row selection. For label layouts, the  resulttype is not used: hence 
    //the default value of null
    function open_tr($resulttype=null)
    {
        //Open the tr attributes
        echo "<tr";
        //
        //The primary key attribute is needed to support record updates and inserts
        echo " primarykey='". $resulttype[Field::primary]. "'";
        //
        //The id attribute is needed for hreferencing the tr
        echo " id='". $resulttype[Field::id]."'";
        //
        //The hint attribute is used for effecting searches
        echo " hint='". $resulttype[Field::hint]."'";
        //
        //The onclick event should evoke the row select
        echo " onclick='row=new Row(this); row.select()'";
        //
        //Close the tr attributes       
        echo ">"; 
    }
    
}

class Layout
{
    //Every layout must associated with a query
    function __construct($sqledit)
    {
        $this->sqledit=$sqledit;
    }
}

//Label is a layout in which the named fields and thier associated data are
//laid out out side by side
class Label extends Layout
{
    //The primary key value for data updates
    public $primarykey;
    //
    //The field that receives focus on edit
    public $focus_name;
    //
    function __construct($sqledit, $primarykey, $focus_name)
    {
        parent::__construct($sqledit);
        $this->primarykey = $primarykey;
        $this->focus_name = $focus_name;
    }
       
    //The tr tag of a labeled out id a dive named field that allows the td label
    //and input to ma managed as a single unit. It also stores the primary key 
    //for database updates later. The $resulttype is not used
    function open_tr()
    {
        echo "<record";
            //
            //The class field allows us to css the whole td
            echo " class='field'";
            //
            //Save the primary key field
            echo " primarykey='".$this->primarykey."'";
        //
        echo ">";
    }
    
    //Display a labeled td. The key feature of a labeld td is the field name 
    //followed by the input value. For each td must be identified by its original
    //field anme and table to support data updates
    function display_td($field, $value, $resulttype)
    {
        //Do not output the primary key field
        if ($field->is_primary()) {return;}
        //
        //Get the foreihn field status
        $status = $field->is_foreign() ? "true": "false";
        //
        //Ensure that the label and its associated value are output as joint 
        //unit
        echo "<div name='{$field->name()}' is_foreign='$status'>";
            //
            //The label of the field 
            echo "<label class='normal'";
                //
                //Output the "for" clause as name of the field; this must match the 
                //input id
                echo " for='{$field->name()}'";
                //
            //Close the label attributes
            echo ">";
                //
                //Display the label text
                echo $field->name();
            //
            //Close the label
            echo "</label>";
            //
            //
            //Display the field value in edit mode
            $field->display_value($value, $resulttype);
        //
        echo "</div>";
        
    }
        
    //
    function close_tr(){ echo "</record>"; }
   
    //
    //Display only one record in the label layout format
    function display()
    {
        //
        //Execute the sql to get a result
        $result = $this->sqledit->execute();
        //
        //Retrieve the first record, or none (if resulttype is null
        $resulttype = $result->fetch_assoc();
        //
        //Use the resulttype to open a tr as you need to access 
        //primary keys for updates and the id for hrereferencing
        $this->open_tr($resulttype);
        //
        //Display the record values based on the sql fields
        foreach($this->sqledit->fields as $field)
        {
            //
            //Get field value, using the field's data field name
            $value = $resulttype==null ? null : $resulttype[$field->data_fname()];
            //
            //Display a table cell, td. The resulttype is needed to access
            //further details if need be
            $this->display_td($field, $value, $resulttype);
        }
        //
        //Display the close tr tag
        $this->close_tr();

    }
}

//A record is a collection of data waiting to be written to a database
class Record
{
    
    //The table name to be inserted or updated
    public $tname;
    //
    //Indicateor as to whethe update or insert is needed. If updated the value
    //shows which field to update
    public $primarykey;
    //
    //The indentification field names of this record's index
    public $idfnames;
    //
    //An indexed array of all the fields of this record
    public $fields;
    
    //Construct a record (of type Record) using a record_ of the stdClass 
    //structure. Note the underbar
    function __construct($record_)
    {
        //Pass fields directly from record_ to this recird
        foreach($record_ as $key=>$value)
        {
            $this->{$key} = $value;
        }
        //
        //The collection of fields (if any) are handled as an indexed array 
        //in php
        if (isset($record_->fields))
        {
            //reset the fields
            $this->fields = [];
            //
            //index them
            foreach($record_->fields as $field)
            {
                $this->fields[$field->name] = $field;
            }
        }
        
    }
   
    //Save this record to the database
    function save()
    {
        //
        //The required save method is either an insert, as in:-
        //insert into <table> (<fields>) values (<values>)
        //or an update, as in:-
        //update <table> set column1 = value1, column2 = value2, ...where <table>=<primarykey>
        //
        //Formulate the sql statement needed for either inserting new record
        //or updating the existing one if the primary key is set
        $sql = $this->get_sql();
        //
        $dbase = Dbase::Open();
        //
        //Execute the sql, reporting any error
        if (!$result = $dbase->conn->query($sql)) 
        {
            die ($sql."<br/>".$dbase->conn->error);
        }
        //
        //Success
        return true;
    }

    //Delete current record
    function delete()
    {
        //Formulate the delete sql
        $sql = "delete from `$this->tname` where `$this->tname`=$this->primarykey";
        //
        $dbase = Dbase::open();
        //
        //Execute the sql, reporting any error
        if (!$result = $dbase->conn->query($sql)) 
        {
            die ($sql."<br/>".$dbase->conn->error);
        }
    }

    //Returns the insert or update sql statement depending on whether the
    //primary key is known or not
    function get_sql()
    {
        //The required save method is either an insert, e.g.:-
        //insert into <table> (<fields>) values (<values>)
        //or an update -- e.g.
        //update <table> set column1 = value1, column2 = value2, ...where <table>=<primarykey>
        //
        //Get the table to save to, complete with the backticks
        $table = "`".$this->tname."`";
        //
        //Compile the sql paramaters, i.e., fnames as field names, values as data
        //values and ofields as field/value pairs needed by update. All these
        //values are built in one loop that transverses the fild collection
        $fnames=""; $values=""; $ofields="";
        //
        //Loop through all the fields to be saved to compile the above (list) 
        //parameters. Emoty fields are ignored
        foreach($this->fields as $field)
        {
            //Ignote fields with no values
            if ($field->value==='') {continue; }
            //
            //Get the orignal field name from the saved field (sfield), complete with the backtics
            $fname = "`".$field->name."`";
            //
            //Get the value from the saved field, complete with the quotes
            $value = "'".$field->value."'";
            //
            //Compile the output field/value pair
            $ofield = "$fname=$value";
            //
            //Work out the seperator, based on the list of field names
            $separator = $fnames=="" ? "": ", ";
            //
            //Compile the field name list
            $fnames .= $separator.$fname;
            //
            //Compile the field values
            $values .= $separator.$value;
            //
            //Compile the output fields
            $ofields .= $separator.$ofield;
        }
        //        
        //This is an update sql if there is a primary key available
        if (isset($this->primarykey))
        {
            $sql = "update $table set $ofields where $table=".$this->primarykey;     
        }
        //
        //Otherwise it is an insert sql statement
        else
        {
            $sql = "insert $table ($fnames) values ($values)";
        }
        //
        //Return the sql statememt
        return $sql;
    }
    
    //Upload all the necessary (image) files; 
    function upload_files()
    {
        //This process is valid oly if there are files to upload
        if (count($_FILES)===0) {return;}
        //
        //Set the image directory as a subdirectory of current
        $images = "images";
        //
        //Exit if the image folder does not exist on teh server
        if (!file_exists($images))
        {
            die("Folder $images does not exist on the server");
        }
        //
        //Upload valid file brought to the server
        foreach ($_FILES as $file)
        {
            //Retrieve the basename of the file
            $basename = $file['name'];
            //
            //Uploading is not valid for empty file name
            if ($basename==""){continue;}
            //
            //Compile the absolute path on the server subfolder where the image will
            //be saved. We assume the same drive as this page. The relative one will 
            //not do for data movement on the server. Note the direction of the 
            //slashes (assuming a Windows server) to desigate an OS path
            $fullname ="$images/$basename";
            //
            //If the file exists do not overwite it
            if (file_exists($fullname)) {continue;}
            //
            //Transfer the temp filename to the correct server path -- using absolute
            //paths. If for any reason the move is not successful alert the user
            if (!move_uploaded_file($file["tmp_name"], $fullname))
            {
                //There was an issue: report it
                echo "Error in uploading to file '$fullname'";
            }
        }
        return true;
    }
}

//The root sql needs a reference table as the seed of a join
//It is abstract because 2 method, initialize_joins and columns, must be 
//implemented by classes that extend it.
abstract class Sql
{
    //The name of this sql
    public $name;
    //
    //The database from which this sql is derived is protected as we dont need a 
    //json version of it. But we need to access it from other sqls extending this
    //one.
    protected $dbase;
    
    //The fields of an sql as shown in an sql select statement
    public $fields= [];
    //
    //The joins that constitute the From clause of an sql statement
    public $joins=[];
    //
    //The where clause as a boolean expression
    public $where;
    //
    //The reference table name. The reference table may be private for jsoning 
    //reasons, but athe $tname must be public
    public $reftname;
    //
    //Returns the reference standard table derived from the reference tname
    function reftable()
    {
        return $this->dbase->get_table($this->reftname);
    }
    //
    //An sql must named so that it can be referenced elsewhere. I have discarded
    //the notion that the joins can be formulated from the sql's columns due
    //to the fact that for some derived cases it is not intuitive to do so. The
    //edit sql is one such case. The select cpndition of an sql is optional
    function __construct($name, $reftname, $where=null)
    {
        $this->name = $name;
       //
        $this->reftname = $reftname;
        //
        $this->where=$where;
        //
        //Set the sql database to be the global one
        global $dbase;
        $this->dbase = $dbase;
        //
        //Let the caller initialize the sql data, i.e., the columns and joins of
        //this sql
        $this->initialize_sql_data();
        
    }
    //
    //Initialise the sql data, i.e., the column and joins, that make up the sql.
    //This function must be implemented by the caller
    abstract function initialize_sql_data();
    
    //The name of an sql
    function name() {return $this->name;}
    
    //Convert any sql into a select string statement
    function __toString()
    {
        //We assume that the fields of this sql are all basic fields, so the 
        //fvalue() method is valid
        $exps = array_map(function($field)
            {
                //The is is no alias if a field does not have a name
                return $field->fvalue().$field->alias();
            }, $this->fields);    
        //
        //Convert the list of aliased field expressions into a comma separated 
        //list of strings
        $a= implode(", ", $exps);
        //
        //Let $b be the required join expression
        //
        //Startin with the regerence table anme, formulate a join expression 
        $b = $this->reftname;
        //
        //Walk through the joins to compile the desired From clause
        foreach($this->joins as $join)
        {
            $b = "(". $b . (string)$join . ")";
        }
        //Let $c be where clause
        $c = isset($this->where) ? " WHERE ".$this->where: ""; 
        //
        //Compile the full statemen, including the select condition
        return "SELECT $a FROM $b $c";
    }
    
    //Execute this sql to get a result
    function execute()
    {
        //Get the string version of this sql object
        $sql = (string)$this;
        //
        //Execute this sql to get a result
        if (!$result = $this->dbase->conn->query($sql))
        {
           die ($sql."<br/>".$this->dbase->conn->error);    
        }
        //
        //Return the result
        return $result;
    }
    
}


//The standard sql charaterised by a constructor of 4 key items. It is needed 
//for converting special sqls into their equivalent valid sql strings. For 
//instance, sqledit used the standard sql to convert it to an sql 
//string where we first slit it into basic fields
class SqlStd extends Sql
{
    //The critical bits of a standard sql are the fields and joins; however we
    //need a namd and some referemce table in order to initialize the paremt sql
    function __construct($name, $reftname, $basicfields, $joins, $where)
    {
        $this->name = $name;
        $this->fields = $basicfields;
        $this->joins = $joins;
        //
        parent::__construct($name, $reftname, $where);
    }
    
    //A standard sql does not need to initialize its fields and joins as the
    //constructor has already done that
    function initialize_sql_data()
    {
        //Do noting
    }
}

//Sql to support editing of table records. It is driven by all the columns 
//defined by the reference table. Foreign key columns are befriended.
class SqlEdit extends Sql
{
    //The sql edit has features of a table: primary key, hint and id fields
    use Table_;
    //
    //The basic fields of this sql is obtained by spliting (mixed) field list 
    //when needed. They are used for retrieving data for this
    private $basicfields;
    
   //The reference table is key to this process; its condition is optional
    function __construct($reftname, $where=null)
    {
        //Formulate the name of the sql edit based on the reference table name
        $name = $reftname."_edit";
        //
        //Initialize parent sql using the updated sql data
        parent::__construct($name, $reftname, $where);
    }
    
    //The primary field name of sql edit is the same as the reference table name
    function primary_fname(){return $this->reftname;}
    
    //Initialiize both fields and joins of this sql . The process is driven by
    //all the fields of the reference table plus the intermediate table sqlExt
    function initialize_sql_data()
    {
        //Use all the columns of the reference table to derive the fields of this Sql Edit
        array_walk($this->reftable()->fields, function($col)
        {
            $col->initialize_edit_sql($this);
        });
    }
    
    //Split this query's mixed fields into all basic fields for the purpose of 
    //querying the database
    function basicfields()
    {
        if (isset($this->basicfields)) {return $this->basicfields; }
        //
        //Collect all the basic fields of this sql by splitting its compound 
        //fields into subfields -- strating with the empty basic fields. 
        $this->basicfields = [];
        foreach($this->fields as $field)
        {
            //Split the field into subfields
            foreach($field->split() as $subfield)
            {
                //Add the subfield to the basic fields collection
                $this->basicfields[$subfield->name()] = $subfield;
            }
        }
        //
        return $this->basicfields;
    }
    
    //Convert this edit sql into a valid string
    function __toString()
    {
        //
        //Compile a standard sql using the edit sql's basic fields
        $sql = new SqlStd($this->name, $this->reftname, $this->basicfields(), $this->joins, $this->where);
        //
        //Return the string version of the sql
        return (string)$sql;
    }
    
}

//The SqlHint is an sql that comprises of only the identfication and hint 
//raw fields of some reference table. It is used to derive the id and hint fields
//of SqlExt (through concatenation) that is in turn used to extend the SqlEdit 
class SqlHint extends Sql
{
    //The reference table guides the construction of the sql columns and their
    //supporting joins
    function __construct($reftname)
    {
        //Formuate teh name of a hint sql
        $name = $reftname."__hint";
        //
        //Initialize parent sql; the driving columns are those of the default 
        //index.
        parent::__construct($name, $reftname);
    }
    
    //Initialialize both fields and joins of this extension sql. The process is 
    //driven by hint columns, i.e., the identification and friendly fields of 
    //the reference table.
    function initialize_sql_data()
    {
        //
        //Use the hint columns of the reference table to initialize the sql 
        //data for this extension sql
        array_walk($this->reftable()->hint_cols(), function($col)
        {
            $col->initialize_hint_sql($this);
        });
    }
    
}

//The Ext Sql is used to extend the Edit sql so that foreign keys can be 
//befriended. It has the following characateristics: 1) it can be (left) joined 
//to the reference table of the Edit Sql, 2) It has 3 columns: the primary,
//the hint and the id columns. To enable (1), therefore, the sql 
//must implement the Table interface
class SqlExt extends Sql implements Table 
{
    //Use the shared implementations of some of the functions defined in the 
    //table interface
    use Table_;
    //
    //
    function __construct($reftname)
    {
        //Compile name the of the sql that extends a reference table
        $name = $reftname."_ext";
        //
        parent::__construct($name, $reftname);
    }
    
    //Initialize the required 3 fields -- primary, id and hint -- of 
    //this sql as well as joins needed to support formulation of these fields
    //The expected string versio of this sql should be look like:-
    //
    //select $primaryfield as primary, concat($hints) as hint,  concat($ids) as id from
    //$joins
    //
    //The primary key column is used for supporting record updates, the id 
    //for hreferencing the records and the hint for driving record selection 
    function initialize_sql_data()
    {
        //SET THE PRIMARY FIELDS
        //
        //Get the reference table of this sql as it is the key ingredient for
        //deriving the desired data
        $reftable = $this->reftable();
        //
        //Formulate the desired primary key field 
        $primaryfield = $reftable->primary_field();
        //
        //Add the primary field to this sql's fields
        $this->fields[Field::primary] = new FieldBasic($primaryfield->fvalue(), Field::primary);
        //
        //Get a) raw id and hint fields and b) required joins from sql IdHint
        $sqlHint= new SqlHint($this->reftname);
        //
        //SET THE HINT FIELDS
        //
        //Concat all the fields of $sql to get a new expression
        $hintvalue = new ExpressionConcat($sqlHint->fields);
        //
        //Create a basic field hint named hint
        $hintfield = new FieldBasic($hintvalue, Field::hint);
        //
        //Add the hint to this sql's fields using the hint index
        $this->fields[Field::hint] = $hintfield;
        //
        //SET THE ID FIELDS
        //
        //Filter the id fields from the hint cases
        $idfields = array_filter($sqlHint->fields, function($idfield)
        {
            return $idfield->field_is_id();
        });
        //
        //The fvalue expression of the id field is the concatenation of the id 
        //fields of the hint sql
        $idvalue = new ExpressionConcat($idfields);
        //
        //Create the id field for this sql
        $idfield =  new FieldBasic($idvalue, Field::id);
        //
        //Add it to the sqlExt usng the id index
        $this->fields[Field::id] = $idfield;
        //
        //SET THE JOINS
        //
        //Set this sql's joins to those of $sqlHint
        $this->joins = $sqlHint->joins;
    }
}

//Table is an abstract that is extended by ordinary database tables and other
//derived versions. It is used to support the "From $table" clause of an sql 
//where $table is a standard database table or an sql that can participate
//in a From clause
interface Table
{
    
   //Examples of a table sql values are expressions used in the From clause.
   //SELECT ... FROM "client" WHERE ....
   //SELECT ... FROM "(SELECT .....) AS zone__id" WHERE ....
    //The quoted bits are table expressions
   function value();
   //
   //The name of an sql used for qualifying field names, e.g., 
   function name();
   //
   //To support foreign key joins, a table must have a primary key field name and
   //the actual primary field.
   function primary_fname();
   function id_fname();
   function hint_fname();
   
   function primary_field();
   
}

//The table trait contains implementations of functions that are shared by 
//claases the implement the table intterface
trait Table_
{
    //
    //The identification field of a table is teh same as the primary one with 
    //the __id suffix. Note the double under bar. The field is used for 
    //hreferencing records of this table
    function id_fname()
    {
        return $this->primary_fname().Field::id;
    }
    //
    //The hint field of a table is the same as the primary one with 
    //the __hint suffix. It is used for (a) searching recrod of this table by 
    //hints and (2) befriending foreign key columns
    function hint_fname()
    {
        return $this->primary_fname().Field::hint;
    }
    
    //The sql value of an sql that extends a table is used in the From clause
    //and has teh form:-
    //(select .....) as zone_fk
    function value()
    {
        return "(".(string)$this.") AS `".$this->name()."`";
    }
    
    //Returns the hint field of a table
    function hint_field()
    {
        return $this->fields[$this->hint_fname()];
    }
    
    //The primary key field name of a sql extension is the same as that of
    //the table
    function primary_fname()
    {
        return $this->name();
    }
    
     //Returns the primary field of this table
    function primary_field()
    {
        return $field = $this->fields[$this->primary_fname()];
    }
}

//A standard table correspond to database table and implements the table 
//interface so that it can take part in a From clause of the select 
//statement
class TableStd extends Sql implements Table 
{
    //Use the commonly immplemented functions of the Table interace
    use Table_;

    //Indices are needed by view to construct a row's unique id
    public $indices;
    
    //A table constructor
    function __construct($tname)
    {
        //In a standard table, the reference table name is the same as the table
        parent::__construct($tname, $tname);
        //
        //Set all the fields of this table
        $this->fields= $this->get_columns();
        //
        //Set the identification indices of this table
        $this->indices = $this->get_indices();
    }
    
    
    function initialize_sql_data()
    {
        //Do notiing for a standard table
    }
    
    //Returns the default index columns of this table as the list of columns 
    //that are derived from the first identification index
    function default_index_cols()
    {
        //Get the first identification index of this table
        //
        //Get the first key from this table's indices
        $key = array_keys($this->indices)[0];
        //
        //Now get the first index
        $index = $this->indices[$key];
        //
        //A index is an array of index colum names; map them to actual columns
        $cols = array_map(function ($colname){return $this->fields[$colname];}, $index);
        //
        //To ensure that the returned columns are column_name indexed
        return array_combine($index, $cols);
    }
    
    //Return the hint columns, i.e, unique combination of descriptive and
    //identification fieields
    function hint_cols()
    {
        //Filter from this table's columns those that are descriptive
        $descriptives= array_filter($this->fields, 
                function($col)
                {
                    //Get the column name
                    $name=$col->column_name; 
                    //
                    //Descriptive columns ar names descriptions or have a name
                    //suffix
                    $filter = ((substr($name,-4)=="name") || ($name=="description"))? true: false;
                    //
                    return  $filter;
                });
        //
        //Let $c be the combination of indexing and descriptive columns
        $c = array_merge($this->default_index_cols(), $descriptives);
        //
        //Remove duplicates. Get the __toString to work correctory
        $d = array_unique($c);
        //
        //return the combination
        return $d;
    }
    
    //The sql value of a standard table, as required in a From clause is simply
    //the table name
    function value()
    {
        return "`{$this->name()}`";
    }
    
    //Collect this table's identification indices
    private function get_indices()
    {
        //
        //Select all the identification indices of this table
        $sql =  
           "select 
                constraint_name 
            from information_schema.TABLE_CONSTRAINTS 
            where table_schema='".$this->dbase->dbname."'
                and constraint_type='unique'
                and table_name='{$this->name()}'";
            //
            //Now use the sql to query the database (connection). Abort the process in case 
            //of error -- echoing the error message.
            if (!$result = $this->dbase->conn->query($sql))
            {
               die ($sql."<br/>".$this->dbase->conn->error);    
            }
            //
            //Start with an empty list of indices
            $indices=[];
            while ($resulttype = $result->fetch_assoc())    
            {
                //
                //Get the name of the index
                $xname = $resulttype['constraint_name'];
                //
                //Set the named index to all her index column names
                $indices[$xname]=$this->get_index_fields($xname);
            }
            //
            //Return the indices
            return $indices;
    }
    
    //
    //Return all the index column names of the named index
    private function get_index_fields($xname)
    {
        //Select column names  of the named index
        $sql=
            "select  
                column_name 
            from information_schema.STATISTICS 
            where table_schema='".$this->dbase->dbname."'
            and index_name ='$xname'
            and table_name='{$this->name()}'";
            //
            //Now use the sql to query the database (connection). Abort the process in case 
            //of error -- echoing the error message.
            if (!$result = $this->dbase->conn->query($sql))
            {
               die ($sql."<br/>".$this->dbase->conn->error);    
            }
            //
            //Start with an empty list of index fields
            $xfnames=[];
            while ($resulttype = $result->fetch_assoc())    
            {
                //
                //Get the name of the column
                $colname = $resulttype['column_name'];
                //
                //Push the column name into the array
                array_push($xfnames, $colname);
            }
            //
            //Return the indexing column names
            return $xfnames;

    }
    
    //Returns this table's column collection
    private function get_columns()
    {
        //Select all tye fields of this table
        $sql=
        "select 
            column_name,
            is_nullable,
            data_type,
            character_maximum_length,
            column_type,
            column_key,
            extra,
            column_comment
        from information_schema.columns 
        where table_schema='".$this->dbase->dbname."' 
              and table_name='{$this->name()}'";
        //
        //Now use the sql to query the database (connection). Abort the process in case 
        //of error -- echoing the error message.
        if (!$result = $this->dbase->conn->query($sql))
        {
           die ($sql."<br/>".$this->dbase->conn->error);    
        }        
        //
        //Visit all the listed records and create a field for each record
        //
        //Start with an empty list of table columns
        $cols=[];
        while ($resulttype = $result->fetch_assoc())    
        {
            //Retrieve the field name
            $fname = $resulttype['column_name'];
            
            //
            //Create a new of column of this table guided by the given result type
            $col= $this->create_column($resulttype);
            //
            //Add it to the collection (associatively)
            $cols[$fname]=$col;
        }
        //
        //Return the table columns
        return $cols;
        
    }
    
    //Return the proper type of column of this table guided by the decription
    //of the resulttype.
    private function create_column($description)
    {
        
        //Recognize a primary key column
        if ($description['column_key']=='PRI')
        {
            //
            //Return a new primary key using this same table
            return new ColumnPrimary($description, $this);
        }
        //
        //Recognize foreign key columns
        elseif ($this->column_is_foreign($description['column_name'])) 
        {
            //Get the foreign key details
            $foreign=$this->column_is_foreign($description['column_name']);
            //
            //Rteurn a foreign key column
            return new ColumnForeign($description, $this, $foreign);
        }
        //
        //Return a new ordinary column
        else 
        {
            return new Column($description, $this);
        }    

    }
        
    //Tests if the named column is foreign or not; if foreign it returns the 
    //referenced table and column as a stdClass object
    private function column_is_foreign($colname)
    {
        //Formulate a sql for selecting key usage and table constraints for the
        //given column
        $sql = 
            "select 
                us.`table_name`, 
                us.`column_name`, 
                us.referenced_table_name, 
                us.referenced_column_name 
            from information_schema.`KEY_COLUMN_USAGE` as us
                 inner join information_schema.`TABLE_CONSTRAINTS` as const 
                 on us.`constraint_name`=const.`constraint_name`
                 and us.`table_name`=const.`table_name`
                 and us.table_schema=const.table_schema
            where us.table_schema='".$this->dbase->dbname."' and
                  const.constraint_type='foreign key' and
                  us.`table_name`='{$this->name()}' and
                  us.`column_name`='$colname'"; 
        //
        //Execute the sql, reporting any error
        if (!$result = $this->dbase->conn->query($sql)) 
        {
            die ($sql."<br/>".$this->dbase->conn->error);
        }
        //
        //Fetch the result
        $resulttype = $result->fetch_assoc();
        //
        //If not valid this is not a foreign key
        if (!$resulttype)
        {
            return false;
        }
        //
        //Otherwise return the referenced table and column
        else 
        {
            
            $foreign= new stdClass;
            $foreign->table_name = $resulttype['referenced_table_name'];
            $foreign->column_name = $resulttype['referenced_column_name'];
            return $foreign;
        }
                
    }
    
}

//Models the enture mysql database on the local server
class Schema
{
    //Login credentials
    public $username;
    public $password;
    //
    //We assume that the username and password are needed for pening other 
    //databases accessible using this schema
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }
    
    //List all the databases under this schema
    public function show()
    {
        //
        // Create database connection
        $conn = new mysqli("localhost", $this->username, $this->password, "information_schema");
        //
        // Check connection
        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        } 
        //
        //Select all the mutall databases
        $sql="select SCHEMA_NAME from SCHEMATA WHERE SCHEMA_NAME LIKE 'mutall_%'";
        //
        //Query the result
        $result = $conn->query($sql);
        //
        //
        //Loop through all selected databases
        while($row=$result->fetch_assoc())
        {
            //Get the current database name
            $dbname=$row['SCHEMA_NAME'];
            ?>
            <tr onclick='schema.select(this)' dbname='<?php echo $dbname; ?>'>
                <td>
                    <?php echo $dbname; ?>
                </td>
            </tr>
        <?php }
            
    }
    
    //Show the login value as either blank or the logged in database 
    //depending on ehter the dbase session variable is set or not
    public function show_login_value()
    {
        if (isset($_SESSION['dbase']))
        {
            //Get the database ame;
            $dbname = $_SESSION['dbase']->dbname;
            //
            //Return the login value
            return "value = 'Logout $dbname'";
        }
        else 
        {
            return "";
        }
    }
    
    //If there is no logged in database from last session hide the login button
    public function hide_login()
    {
        if (isset($_SESSION['dbase']))
        {
            return "";
        }
        else 
        {
            return "hidden='true'";
        }
    }

}