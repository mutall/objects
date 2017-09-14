<?php

//Sumbit the data in the posted record in a 2 stage process:
//saving the data to a database and uploading
//the files referenced by the data
//
//Aval the session variables
session_start();
//
//Retrieve the incoming json erecord that has the data to save
$json_record= $_POST['record'];
//
//Decode the json record string to a php structure of the type stdClass
//Hence, note the underbar
$record_= json_decode($json_record);
//
require_once "library.php";
//
//Create a php structure of the record class
$record= new Record($record_);
//
//Ensure that the session variable id is updated
$_SESSION['id'] = $record_->id;
//
//Save the given record's data
$record->save();
//
//Upload files -- if any -- from client to server
$record->upload_files();
//
//Successful finish
echo "ok";
