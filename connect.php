<?php
if (!file_exists('config.php')){
    exit ('Erreur : il manque le fichier config.php, vous pouvez le créer depuis le fichier exemple config-default.php');
}
include_once 'config.php';
if(!is_writable(dirname(DB_PATH))){
    exit ('Erreur : le répertoire '.dirname(DB_PATH).' n\'est pas accessible en écriture...');
}
if(!is_writable('avatars')){
    exit ('Erreur : le répertoire ./avatars n\'est pas accessible en écriture...');
}



class MyDB extends SQLite3
{
    function __construct()
    {
        $this->open(DB_PATH);
    }
}

$db = new MyDB();


// create table if not exists
$tableCheck =$db->query("SELECT name FROM sqlite_master WHERE name='".TABLE_NAME."'");
if ($tableCheck->fetchArray() === false){
    //echo "Table does not exist";
    $sql = 'CREATE TABLE IF NOT EXISTS "'.TABLE_NAME.'" (
        "id"	INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE';
        foreach($fields as $f => $k){
            $sql .= ',
            "'.$f.'" TEXT';
        }
    $sql .=');';
    echo("La table a été créée avec la requête suivante : <br>");
    echo($sql);
    $db->query($sql);
}else{
    //echo "Table exists";
}

/* edit mode logout handling */
session_start();
if (isset($_GET["editpw"])&& !empty($_GET["editpw"])){
    if ($_GET["editpw"] == "stop"){
        unset($_SESSION["admin"]) ;
    }
}
$editmode = isset($_SESSION["admin"]);


function avatarPath($id){
    return sprintf('avatars/%08d.jpg', $id);;
}

