<?php
include("connect.php");

if (isset($_POST['action']) && $editmode){

    // load user data from id
    if ( ($_POST['action'] == "loadbyid") && isset($_POST["id"]) ){
        $result = $db->query('SELECT * FROM ' . TABLE_NAME . ' WHERE "id" = ' . $_POST["id"]);
        if ($row = $result->fetchArray(SQLITE3_ASSOC)){
            $path = avatarPath($_POST["id"]) ;
            if (file_exists($path)){
                $row["avatarpath"] = $path ;
            }
            echo("{\"errMsg\":\"\", \"data\" : ".json_encode($row)."}");
        }else{
            echo("{\"errMsg\":\"Record ".$_POST["id"]." not found\"}");
        }
    }

    // make a search

    if ( ($_POST['action'] == "search") && isset($_POST["searched-str"]) ){
    
        $nbmax = 40 ;

        $condition = 'name like "%' . $_POST["searched-str"].'%" OR firstname like "%' . $_POST["searched-str"].'%"';
        
        $sql = 'SELECT COUNT(*) as count FROM ' . TABLE_NAME . ' WHERE ' . $condition;
        //echo($sql);
        $count = $db->querySingle($sql);
        if ($count == 0){
            echo("{\"errMsg\":\"Désolé, aucun résultat pour '". $_POST["searched-str"]."'...\"}");
        }else if ($count > $nbmax){
            echo("{\"errMsg\":\"Désolé, trop de résultats pour '". $_POST["searched-str"]."'... ($count)\"}");
        }else{
            $sql = 'SELECT id, name , firstname , address FROM ' . TABLE_NAME . ' WHERE ' . $condition . 'ORDER BY name , firstname ASC LIMIT ' . $nbmax ;
            //var_dump($sql);
            $result = $db->query($sql);
            $rows = [];
            while($row = $result->fetchArray(SQLITE3_ASSOC)){
                $rows[] = $row;
            }
            echo("{\"errMsg\":\"\",\"data\":".json_encode($rows)."}");
        }
    }


    // update user data
    if ($_POST['action'] == "update"){
        if ($data = json_decode($_POST["updates"])) {
            if ($data->id >= 0){ // update this user data
                $sql = "UPDATE " . TABLE_NAME . " SET " ;
                $nbData = 0 ;
                foreach ($data as $key => $value){
                    if ($key != "id"){
                        if ($nbData > 0) $sql .= " , ";
                        $sql .= " \"$key\" = \"".$data->$key."\"";
                        $nbData++;
                    }
                }
                $sql .= " WHERE id = ".$data->id;
                $result = $db->query($sql);
                echo("{\"errMsg\":\"Modifications enregistrées.\",\"id\":\"$data->id\"}");
            }else{ // create a new user
                $keys = [];
                $values = [];
                foreach ($data as $key => $value){
                    if ($key != "id"){
                        $keys[] = "\"$key\"";
                        $values[]= "\"$value\"";
                    }
                }
                if (sizeof($keys)>0){
                    $sql = "INSERT INTO " . TABLE_NAME . " (".implode(", ",$keys).") VALUES (".implode(", ",$values).")";                    
                    $result = $db->query($sql);

                    echo("{\"errMsg\":\"Modifications enregistrées.\",\"id\":\"" . $db->lastInsertRowID() . "\"}");
                }
            }


        }else{
            echo("{\"errMsg\":\"Data base update failed...\"}");
        }
    }

    // delete user 
    if ($_POST['action'] == "delete" && isset($_POST["id"])){
        // remove from base
        $sql = "DELETE FROM " . TABLE_NAME . " WHERE id = " . $_POST["id"];        
        $result = $db->query($sql);
        // delete avatar
        unlink(avatarPath($_POST["id"]));
        echo("{\"errMsg\":\"Fiche effacée.\",\"id\":\"" . $_POST["id"] . "\"}");
    }

    // get info
    if ($_POST['action'] == "get-nbusers"){
        $count = $db->querySingle("SELECT COUNT(*) as count FROM " . TABLE_NAME . "");
        echo("{\"errMsg\":\"\",\"nbusers\" : \"$count\"}");
    }
}


?>