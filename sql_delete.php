<?php
//
//Delete a specified record from a specified table
//
//Access teh session variables
session_start();
//
//
//Retrieve the incoming json record that has the data to save
$json_record= $_GET['record'];
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
$record->delete();
//
//Done
echo "ok";
