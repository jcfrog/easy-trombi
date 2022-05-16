<?php

/* Site identity */
$siteTitle = "Titre trombinoscope";
$siteSubtitle = "Sous-titre pour mon trombinoscope";

/* according to your place */
setlocale(LC_ALL, 'fr_FR.UTF-8');


/* edit mode password : put the md5 of your password, you can create it here https://www.md5.fr/ */ 
$md5pw = "3bfdb73af5d1ab6f5d947439ca96352f";

/* Database */
define('DB_PATH', './database/contacts.db'); // SQLite data base file path
define('TABLE_NAME',"contacts"); // name of the table in the data base

// fields handled in the data base
$fields = array(
    // "name" and "firstname" are needed, for the rest it's up to you
    "name" => array ( "input" => "text", "label" => "Nom"),
    "firstname" => array( "input" => "text", "label" => "Prénom"),
    // your fields from here ----------------------------------------
    "phone" =>  array( "input" => "text", "label" => "Téléphone"),
    "email" => array( "input" => "email", "label" => "Adresse email"),
    "address" => array( "input" => "text", "label" => "Adresse postale"),
    "bday" => array("input" => "date", "label" => "Date de naissance"),
    "comments" => array("input" => "textarea", "label" => "Informations diverses")
);

/* Pagination : number of displayed contacts per page */ 
define("NB_PER_PAGE", 8);

/* User default anonymous avatar */
define('DEFAULT_PIC','avatars/user.png');

?>