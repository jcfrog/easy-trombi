<?php
/* Database */
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

if (!defined('DB_PATH')) {define('DB_PATH', './database/contacts.db');} // SQLite data base file path
if (!defined('TABLE_NAME')) {define('TABLE_NAME',"contacts");} // name of the table in the data base

$bDispLoginLink = true ; // shall we display a link to login page at the bottom of the page?


/* Site identity */
$siteTitle = "Titre trombinoscope";
$siteSubtitle = "Sous-titre pour mon trombinoscope";

/* according to your place */
setlocale(LC_ALL, 'fr_FR.UTF-8');

/* Pagination : number of displayed contacts per page */
if (!defined('NB_PER_PAGE')) {define("NB_PER_PAGE", 8);}

/* User default anonymous avatar */
if (!defined('DEFAULT_PIC')) {define('DEFAULT_PIC','avatars/user.png');}
