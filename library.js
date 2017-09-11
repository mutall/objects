
//The Label layout of data supports interaction with data that is laid out in
//the labl format.
function Label(record)
{
    //
    this.record = record;
    //
    //Onloading this label layout, record set the focus to the selected
    //field name. 
    this.load = function()
    {
        //Get the field name to focus on.
        var fname = this.record.focus_name;
        //
        //Focus only if this is a valid name
        //The fname is valid only for new payments and for cases where the 
        //field clicked on is still focussable in the this page
        if (fname!==null)
        {
            //
            //Get the input identified by the field name
            var input = document.getElementById(fname);
            //
            //If this input is null, then the requested field is not found.
            //bort he field focus
            if (input!==null)
            {
                //
                //Transfer the focus to the fnamed element
                input.focus();
            }
        }

    };
    
    //Compile the updated values, save them to the database and close this window with 
    //an ok message.
    this.save = function ()
    {
        //If any of the identfication fields is invalid, do not continue with
        //the save
        var id = this.get_unique_id();
        if (!id) {return false;}
        //
        //Set the fields of this label's record to get ready for expprt
        this.record.fields = this.get_input_fields();
        //
        //Compile the unique id from this label from the inputs. Note the likely 
        //confusion between 'this' with reference to this.get_unique_id() 
        //and xhttp. That is why this function is defned here
        this.record.id = {tname:this.record.tname, id:id};
        //
        //Convert the new record to a json string. Encoding is not 
        //really needed as the json will not need to be moved around
        var json_record = JSON.stringify(this.record);
        //
        //Create a new xml http request object to allow communication with 
        //the server
        var xhttp = new XMLHttpRequest();
        //
        //When ok return the unique id and close the edit window; otherwise
        //an error must have occured. Show the details in the error tag
        xhttp.onreadystatechange = function () 
        {
            if (this.readyState === 4 && this.status === 200) 
            {
                //
                //If saving was sucessful, save the unique id in the browser
                //window for subsequent anchoring purposes and close it
                if (this.responseText === "ok")
                {
                    //
                    //Return to sender
                    window.close();
                }
                //
                //Otherwise report any error messsages
                else
                {
                   //Get the header element for saving the error message
                   var error = document.getElementById("error");
                   //
                   //Set message's inner html
                   error.innerHTML=this.responseText;
                }
            }
        };
        //
        //Use the post method save the record. Save means adding new or updating 
        //an existing record
        xhttp.open("post", "sql_save.php");
        //
        //Send a request header that tells the post method that we are sending it
        //content of the json string type (not just any string)
        xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        //
        xhttp.send("record=" + json_record);
    };
    
    //Define the unique id of a record as a concatenation of all field values 
    //used for the best identification index
    this.get_unique_id = function()
    {
        //Start with the id as sn empty string
        var id="";
        //
        //Get the identification index field names
        var xnames = this.record.idfnames;
        //
        //Loop through all the identification field names
        for(var i=0; i<xnames.length; i++)
        {
            //Get the field id value
            var value = this.get_field_id(xnames[i]);
            console.log(value);
            //
            //verify that this value is not empry; otherwise give an alert and
            //retun a false
            if (value === '') 
            {
                //Alert te user
                alert("Identification field " + xnames[i] + " should not be empty");
                //
                //return a false
                return false;
            }
            //
            //Define a conditional separator
            var sep = id==="" ? "" : "/";
            //
            //Build the id
            id = id + sep + value;
        }
        //
        return id;
    };
    
    //Returns the id of the named field. If the field is foreign, return the
    //the id attribute; otherwise return its value
    this.get_field_id = function(fname)
    {
        //Get the field, i.e., div tag, with the given name
        //
        //This div must be a chiled of the record tag
        var record = window.document.querySelector("record");
        //
        //Define the required selector -- a div with the given name attribute
        var selector = "div[name=" + fname + "]";
        //
        var field  = record.querySelector(selector);
        //
        //Get the input element
        var input = field.querySelector("input");
        //
        //The id of a foreign key field is retrieved from the id attribute
        if (field.getAttribute("is_foreign") ==="true")
        {
            //Return the id attribute
            return input.getAttribute("id");

        }   
        else
        {
            //Return the actual value input
            return input.value;
        }
        
    };
    
    //Change a foreign key input using a record selector page
    this.change_fk =function(fkinput)
    {
        //We are about to list of all records from the foreign key table 
        //associated with this field.
        //Compile the data needed for interating with this list -- startings
        //from from a selection that corresponds to this field
        var fkfield = new FkField();
        //
        //Set the foreign key table name -- the source of records to be 
        //listed
        fkfield.fk_table_name = fkinput.getAttribute("fk_table_name");
        //
        //Set the id needed to be used in "goto" the list record corresponding
        //t the value of thie foreign key field
        fkfield.id = fkinput.id;
        //
        //Define the dimension specs of the select window in pixels
        var specs = "top=100, left=1100, height=400, width=400";
        //
        //Open the selection window with the current hint query string
        var selection = window.open("view_selection.php?fkfield="+JSON.stringify(fkfield), "Select " + this.fk_table_name, specs);
        //
        //Wait for the window to be closed, checking after 100 milliseconds
        var timer = setInterval(function() {OnSelectingRecord();}, 100);
        
        //Complete the record selection when the selection list window is closed
        //by assigning the result (carried by the closed window) to the 
        //fields of this page
        function OnSelectingRecord()
        {
            //If the selection window is closed, clear the timer first, then 
            //retrieve the returned record 
            if (selection.closed)
            {
                //
                //Clear the timer
                clearInterval(timer);
                //
                //Get the selected record (recpresented by an Fkfield sructure)
                var fkfield2 = selection.fkfield;
                //   
                //See if the record is valid or not. If it is then update this 
                //edit window
                if (typeof fkfield2!=="undefined")
                {
                    //
                    //Set the foreign key input value to the friendly record name
                    fkinput.value = fkfield2.hint;
                    //
                    //Update the forein key original value to the record primar key
                    fkinput.setAttribute("primarykey", fkfield2.primarykey);
                    fkinput.setAttribute("id", fkfield2.id);
                }
            }
        }

    };
    
    //Retrieve the input fields for inserting to or updating a database.
    this.get_input_fields = function()
    {
        //
        //Collect all the input fields that are in a record. (How do you formulate
        //a single selector record/div, so that only div's within a record are 
        //considereds
        var recordtag = window.document.querySelector("record");
        var divs = recordtag.querySelectorAll("div");
        //
        //Initialize the record's field collection
        var fields =[];
            //
        //Push each input value to the values list
        for(var i=0; i<divs.length; i++)
        {
            //Get the i'th input field
            var div = divs[i];
            //
            //Get the input element of this field
            var input = div.querySelector("input");
            //
            //Compile a field/value pair object
            var field = 
            {
                //Collect the field name
                name: div.getAttribute('name'),
                //
                //Retrieve the value of the named field (depending on the whther
                //this field is foreign or not)
                value:  (div.getAttribute("is_foreign")==="true") ? input.getAttribute("primarykey") :input.value
            };    
            //
            //Push the field
            fields.push(field);
        }
        //
        //Return the collected fields
        return fields;
    };
    
}

//Modelling the foreign key field
function FkField()
{
    //The primary key is the defacto (databse) value of a foreign key field
    this.primarykey;
    //
    //The visible value of a field is derived from the hint field of some quey
    this.hint;
    //
    //The id of a foreign key field is primarity used for supporting "go-to", 
    //i.e., the hreferencing operation
    this.id;
    //
    //The foreign key table name is the vbases for the list rom which new 
    //values of a forign key feield may be selected
    this.fk_table_name;
}

function Schema()
{
    //
    this.dbname;
    
    //
    this.select = function(tr)
    {
        
        new Row(tr).select();
        //
        //Save the current row for future reference. Note that local storage does
        //not store objectts; rather it stores serializable data structures
        this.dbname = window.last_tr.getAttribute("dbname");

    };
    
    //Assuming that the user has logged into a database list all the 
    //mutall tables of the database.
    this.view_database=function()
    {
        //Open the dabase window with the current database. Pass the schema structure
        //-- just incase it has data that is important
        var dbwin = window.open("view_database.php?schema="+JSON.stringify(this), this.dbname);
        
    };
    
    //Supply the login credentials, i.e., username, password and database
    //They will be saved in a session
    this.login = function()
    {
        //To proceed the database name parameter must be set
        if (typeof this.dbname==="undefined")
        {
            alert("Please select a database");
            return;
        };
        //
        //Get teh database name
        var dbname = this.dbname;
        //
        //Call the login menu and on login, save the credentials in session 
        //variables
        var loginwin = window.open("login.php?dbname=" + dbname, "Login");
        //
        //Once logged in, unhide the log out menu option.
        //
        //Wait for the login window to be closed, checking after 100 milliseconds
        var timer = setInterval(function() {OnLogIn();}, 100);
        //
        //Complete the login procedure by showing the logged in dbname
        function OnLogIn()
        {
            //If the selection window is closed, clear the timer first, then 
            //retrieve the returned record 
            if (loginwin.closed)
            {
                //Clear the timer
                clearInterval(timer);
                
                //Unhide the logout menu button
                var logout = document.getElementById("logout");
                //
                //Update the value of the Logout menu to show the username
                logout.value = "Logout " + dbname;
                //
                //Remove the hidden attribute
                logout.removeAttribute("hidden");
            }
        }
    };


    //Save the credentials in a session and close this window
    this.login_save = function (dbname)
    {
        //Extract the login credentials
        var dbase = 
        {
            username: document.getElementById("username").value,
            password: document.getElementById("password").value,
            dbname: dbname
        };
        //
        //Save the credentials in a session
        var win = window.open("login_save.php?dbase="+JSON.stringify(dbase));
        //
        //Close this window, thus signalling to the caller that we are done with
        //the login
        window.close();
        
    };
    
    //Loginout desctroys the session variables and hides the logout menu button
    this.logout = function()
    {
        //Destropy the session varaibales
        var win = window.open("logout.php");
        //
        //Hide the logout button
        document.getElementById("logout").setAttribute("hidden", true);
    };
    
}


//Mutall Tabular object represents the table (rather than the label) layout style
//of a page
function Tabular(tabular_)
{
    //Retrieve the following variables from the in coming tabular layout
    this.tname = tabular_.tname;
    this.id = tabular_.id;
    //
    //The following tabular layout properties are set during user interaction
    //
    //Current hint is used for populating the articles section
    this.hint; 
    //
    //Primary key and focus field name of the current selected row
    this.primarykey;
    this.focus_name;
    //
    //Add a new record to this tabular layout the layout's view fields
    this.add_record=function ()
    {
        //
        //A new record has no primary key nor field to focus on
        var primarykey=null; var focus_name=null;
        //
        //Create a label view
        this.edit(primarykey, focus_name);
    };
    
    //Modify the selected record from this tabular layout
    this.modify_record = function ()
    {
       //
       //Get the current row. A row is a tr with mutall methods
       var row = get_current_row();
       //
       //Abort if the row is not valid
       if (!row) {return false;}
       //
       //Get the current selected td. There must be one, if row is valid
       var td = window.last_td;
       //
       //Set the name of the field to focus on initially, given that
       //the td has a name attribute that matches a field name
       var focus_name=td.getAttribute("name");
       //
       //Get the primary key for this row
       var primarykey=row.primarykey;
       //
       //Edit this primary key record and initially focus on the named field
       this.edit(primarykey, focus_name);
    };
    
    //Edit the redord with the given primary key value and focus initially on 
    //the named field
    this.edit = function(primarykey, focus_name)
    {
        //Compile a record for exporting data for editing
        var record = 
        {
            tname: this.tname,
            primarykey:primarykey,
            focus_name: focus_name
        };
        //
        //Convert the this tabular structire into a json string
        var json = JSON.stringify(record);
        //
        //Define the dimensions of the edit sub-window in (absolute) pixels
        var specs = "top=200, left=600, height=400, width=400";
        //
        //Open the edit support page usig the json record and the window specs
        var edit_window = window.open("support_edit.php?"+"record="+json, "MyWindow", specs);
        //
        //Wait until the edit support window is closed
        var timer = setInterval(function (){on_finish_edit();}, 100);
        
        //Test if the edit window is closed. If it is, then refresh
        //the current window and release the timer
        function on_finish_edit()
        {
            if (edit_window.closed)
            {
               //
               //Stop polling by the timer
               clearInterval(timer);
               //
               //Reset the last tr visited using the unique id set in the editor 
               //window
               window.localStorage.last_tr_id = edit_window.unique_id;
               //
               //Do a refresh of parent sqlEdit edit window
               window.location.reload(true);
            }
        }

    };


    //gravitate moves the row that contains the given checkbox input to the
    //top of ths list just after the header
    this.gravitate = function(input)
    {
        //If we have just unchecked an row, return immediately
        if (input.checked===false) {return;}
        //
        //Retrieve the tr in which the input is found. Its 2 parents up 
        //because of the interevening td
        var tr = input.parentNode.parentNode;
        //
        //Get the parent of tr; its the tbody I suppose
        var tbody = tr.parentElement;
        //
        //Remove this row's tr from tbody
        tbody.removeChild(tr);
        //
        //Add this rows tr to the top of tbody just after the heading
        tbody.insertBefore(tr, tbody.childNodes[1]);
    };
    
    //Delete the selected row
    this.delete=function()
    {
        //
        //Confirm the delete and continue if necessary.
        var yes = window.confirm("Do you really want to delete this row?");
        if (!yes) return;
        //
        //Get get the current row of this tabular layout
        var row = get_current_row();
        //
        //Skip this process if the row is not valid
        if (!row) {return;}
        //
        //Compile a record for exporting data for editing
        var record = 
        {
            tname: this.tname,
            primarykey:row.primarykey
        };
        //
        //create a new XMLHttpRequest object to allow communication with 
        //the server
        var xhttp = new XMLHttpRequest();
        //
        //When ready innsert the response text to the div tag named clients
        xhttp.onreadystatechange = function () 
        {
            if (this.readyState === 4 && this.status === 200) 
            {
                //If ok refresh the display
                if (this.responseText === "ok")
                {
                    //Reload the page for the changes to take effect
                    window.location.reload(true);
                }
                //
                //Otherwise show the error messages in a new window
                else
                {
                    //Open a new window
                    var win = window.open("", "Delete Error", "");
                    //
                    //output teh text
                    win.document.write(this.responseText);
                }
            }    
        };
        //
        xhttp.open("GET", "sql_delete.php?record="+JSON.stringify(record));
        //
        //Send it now
        xhttp.send();
    };
    
    
    //Load this tabular page, using the given hint 
    this.load = function (hint)
    {
        //
        //Populate the articles section with the given hint
        this.populate_article(hint, "list_table.php");
        //
        //Transfer focus to the hint and select the initial text
        //
        //Get the client hint field of this page
        var input = document.getElementById("hint");
        //
        //Set it to the hint value brought into this page
        input.value = hint;

        //Select all the text in the client hint field
        input.select();
        //
        //Transfer focus to the client hint field
        input.focus();
    };

    //Mark the given td for future reference
    this.mark_td = function(td)
    {
        window.last_td=td;
    };
    
    //Populate the articles section with records of the present table given 
    //the hint
    this.populate_article = function (hint, page)
    {
        //Set the hint property of the tabular layout object, as we need to
        //transfer it to the server (when we json this tabular layout)
        this.hint = hint;
        //
        //Pass the concat expression
        //create a new XmlHttpRequest object to allow communication 
        //with the server
        var xhttp = new XMLHttpRequest();
        //
        //When ready insert the response html to the article element
        //and complete the loading
        xhttp.onreadystatechange = function () 
        {
            if (this.readyState === 4 && this.status === 200) 
            {
                //Get the article element in this document
                var article = document.querySelector("article");
                //
                //Proceed only if the article is valid. (When would it not be 
                //valid? I guesss when loading of the page fas failed)
                if (article!==null)
                {    
                    //
                    //Set its html to text to the server response
                    article.innerHTML = this.responseText;
                    //
                    //Show the current selection and transfer focus to
                    //the hint field
                    OnFinishLoading();
                }
                //
                //Report response text in an alert box; otherwise we miss 
                //whatever error got us here
                else
                {    
                    alert(this.responseText);
                }
            }
        };
        //
        //Prepare a url for sending the list support page with current tabular 
        //settings -- especially the hint
        var url = page + "?tabular=" + JSON.stringify(this);
        //
        //Use the get method to send the url to the server
        xhttp.open("GET", url);
        xhttp.send();

        //
        //On finishing the loading of the records:-
        //- use the last tr id to select a tr
        //- set the focus to the client hint
        function OnFinishLoading()
        {
           //
           //Get the last tr id that was edited or selected
           var last_tr_id = window.localStorage.last_tr_id;
           //
            //Retrieve the last identified tr
            var tr = document.getElementById(last_tr_id);
            //
            //Check if the tr is valid
            if (tr!==null)
            {
                //
                //Select the tr by marking it as the only row with a current
                //attribute
                new Row(tr).select();
                //
                //Href, i.e., move to the identfied tr
                window.location.href="#"+last_tr_id;
            }
            //
            //Set focus to the hint field
            var hint = document.getElementById("hint");
            hint.focus();
        }
        
    };
    
    
    //Return the selected record to the caller
    this.return_selection = function ()
    {
        //
        //Get the selected tr
        var tr=document.querySelector("tr[current]");
        //
        //Alert the user if there is no current selection
        if (typeof tr==="undefined")
        {
            alert ("No selection is found");
            //
            //Do not continue
            return;
        }
        //
        //Create a foreign key field
        var fkfield = new FkField();
        //
        //Initilialize the foreign key field
        fkfield.primarykey = tr.getAttribute("primarykey");
        fkfield.hint = tr.getAttribute("hint");
        fkfield.id = tr.getAttribute("id");
        //
        //Add the foreign key field to this window so that the caller can access the 
        //selection when the window is closed
        window.fkfield=fkfield;
        //
        //console.log(record);
        //alert("Testing");
        //
        //Close the window regardless
        window.close();
        
    };

    
    
    //On loading the data show the current selection. This implies 2 things:
    //1) Mark the requested row;
    //2) Go to it, thus makimg it visible, i.e., hreference it
    this.show_selection=function()
    {
        //Get the id of the row to show; return if its not valid
        if (!this.id) {return;}
        //
        //Mark the requested row
        //
        //Get the tr with the given id
        var tr  = document.getElementById(this.id);
        console.log(this.id);
        console.log(tr);
        //
        //If there is no row that mtaches the given id, then probably it does 
        //not exist. Perhaps it was deleted. do not continue
        if (tr===null) {return;}
        //
        //Formulate a new row and select it
        new Row(tr).select();
        //
        //Go to the requested row, i.e, hreference to it
        window.location.href="#" + this.id;
    };

}

//Returns the current row of any page (based) on the last tr
function get_current_row()
{
    //Retrieve the last tr
    var tr = window.last_tr;
    //
    if (typeof tr==="undefined")
    {
        alert ("There is no current selection");
        return false;
    }
    //
    //Formulate a new row and return it
    return new Row(tr);
};

//Post the given structure to the given php file and execute 
//the opinish function when done
function post(record, php, on_finish)
{
    //
    //Convert the input record to a json string. Encoding is not 
    //really needed as the json will not need to be moved around
    var json_record = JSON.stringify(record);
    //
    //Create a new xml http request object to allow communication with 
    //the server
    var xhttp = new XMLHttpRequest();
    //
    //When ready innsert the response text to the div tag named clients
    xhttp.onreadystatechange = function () 
    {
        if (this.readyState === 4 && this.status === 200) 
        {
            //
            //If finishing was not sucessful report the error...
            if (!on_finish(this.responseText))
            {
               //Get the header element for saving the error messages. Please 
               //ensure that the header of an edit record can report errors
               var error = document.getElementById("error");
               //
               //Set message's inner html
               error.innerHTML=this.responseText;
            }
        }
    };
    //
    //Use the post method as we will be transferring json string data
    xhttp.open("post", php);
    //
    //Send a request header that tells the post method that we are sending it
    //content of the json string type (not just any string)
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    //
    //Send the data and dont retrn until it is done
    xhttp.send("record=" + json_record);
    
}

//A mutall row is an extension of the html table tr
function Row(tr)
{
    this.tr=tr;
    //
    //Set the primary key of a row from the tr/primary key attribute
    this.primarykey = tr.getAttribute('primarykey');
    //
    //Set the id of a row
    this.id = tr.getAttribute('id');
            
    //Select this row by ensuring that it is the ony one with the current 
    //attribute
    this.select = function()
    {
        //  
        //Select all currentl markd trs
        var trs  = document.querySelectorAll("[current]");
        //
        //Remove the current attribute from them
        for(var i=0; i<trs.length; i++)
        {
            trs[i].removeAttribute("current");
        }
        //  
        //Make the given row as current
        this.tr.setAttribute("current", true);
        //
        //Save the current row for future reference. Note that local storage does
        //not store objectts; rather it stores serializable data structures
        window.last_tr=this.tr;
        window.localStorage.last_tr_id=this.tr.getAttribute("id");
    };
    
}

//The database representative
function Dbase(dbase_)
{
    //Initialize the mutall database object from the stanadard javascript object
    this.username = dbase_.username;
    this.password  = dbase_.password;
    this.dbname = dbase_.dbname;
    
    //Quitting the database re-calles view_database with a quire arguement; then
    //closes this window
    this.quit = function()
    {
        //Destroy the current session
        var win = window.open("view_database.php?quit='yes");
        //
        //Close the destroyer window
        win.close();
        //
        //Close this main window
        window.close();
    };
    
    //View the records of the currently selected table in its own window
    this.view_table = function()
    {
        //Set the selected table name
        row = get_current_row();
        //
        //Retrieve the tname attribute from the row's tr
        this.tname = row.tr.getAttribute("tname");
        //
        //View records of the selected table
        window.open("view_table.php?dbase="+JSON.stringify(this));
    };
    
   
}



