<?php
include 'connect.php';

if (isset($_POST['action']) && $editmode){

    // load user data from id
    if ( ($_POST['action'] == 'loadbyid') && isset($_POST['id']) ){
        $sql = 'SELECT * FROM ' . TABLE_NAME . ' WHERE "id" = :id';

        $query = $db->prepare($sql);

        $query->bindValue(':id', intval($_POST['id']), SQLITE3_INTEGER);

        $result = $query->execute();

        if ($row = $result->fetchArray(SQLITE3_ASSOC)){
            $path = avatarPath($_POST['id']) ;
            if (file_exists($path)){
                $row['avatarpath'] = $path ;
            }
            echo '{"errMsg":"", "data" : '.json_encode($row).'}';
        }else{
            echo '{"errMsg":"Record "'.$_POST['id'].'" not found"}';
        }
    }

    // make a search

    if ( ($_POST['action'] == 'search') && isset($_POST['searched-str']) ){

        $nbmax = 40 ;

        $string = htmlentities($_POST['searched-str']);

        $condition = '';

        foreach($mandatories as $k => $label) {
            $condition .= $label .' like "%"||:str||"%" OR ';
        }
        $condition = substr($condition,0,-4);

        $sql = 'SELECT COUNT(*) as count FROM ' . TABLE_NAME . ' WHERE ' . $condition;

        $query = $db->prepare($sql);

        $query->bindValue(':str', $string, SQLITE3_TEXT);

        $result = $query->execute();

        //echo($sql);
        $count = $result->fetchArray(SQLITE3_ASSOC)['count'];
        if ($count == 0){
            echo '{"errMsg":"Désolé, aucun résultat pour '. $string.'..."}';
        }else if ($count > $nbmax){
            echo '{"errMsg":"Désolé, trop de résultats pour '. $string.'... ('.$count.')"}';
        }else{
            $sql = 'SELECT id, '. implode(', ',$mandatories).' FROM ' . TABLE_NAME . ' WHERE ' . $condition . 'ORDER BY '.implode(', ',$mandatories).' ASC LIMIT ' . $nbmax ;
            //var_dump($sql);

            $query = $db->prepare($sql);

            $query->bindValue(':str', $string, SQLITE3_TEXT);

            $result = $query->execute();

            $rows = [];
            while($row = $result->fetchArray(SQLITE3_ASSOC)){
                $rows[] = $row;
            }
            echo '{"errMsg":"","data": '.json_encode($rows).'}';
        }
    }


    // update user data
    if ($_POST['action'] == "update"){
        if ($data = json_decode($_POST['updates'])) {
            if ($data->id >= 0){ // update this user data
                $sql = 'UPDATE ' . TABLE_NAME . ' SET ';
                $nbData = 0 ;
                $val = [];
                foreach ($data as $key => $value){
                    if ($key != "id"){
                        if ($nbData > 0) {$sql .= " , ";}
                        $sql .= '"'.$key.'" = :'.$key;
                        $nbData++;
                        if (is_integer($value)) {
                            $val[$key] = SQLITE3_INTEGER;
                        } elseif (is_float($value)) {
                            $val[$key] = SQLITE3_FLOAT;
                        } elseif (is_string($value)) {
                            $val[$key] = SQLITE3_TEXT;
                        } elseif (is_null($value)) {
                            $val[$key] = SQLITE3_NULL;
                        } else {
                            $val[$key] = SQLITE3_BLOB;
                        }
                    } else {
                        $val['id'] = SQLITE3_INTEGER;
                    }
                }
                $sql .= ' WHERE id = :id';

                $query = $db->prepare($sql);

                foreach ($data as $key => $value) {
                    $query->bindValue(':'.$key, $value, $val[$key]);
                }

                $result = $query->execute();

                echo '{"errMsg":"Modifications enregistrées.","id": "'.$data->id.'"}';

            } else { // create a new user
                $keys = [];
                $values = [];
                $val = [];
                foreach ($data as $key => $value){
                    if ($key != 'id'){
                        $keys[] = '"'.$key.'"';
                        $values[]= ':'.$key;
                        if (is_integer($value)) {
                            $val[$key] = SQLITE3_INTEGER;
                        } elseif (is_float($value)) {
                            $val[$key] = SQLITE3_FLOAT;
                        } elseif (is_string($value)) {
                            $val[$key] = SQLITE3_TEXT;
                        } elseif (is_null($value)) {
                            $val[$key] = SQLITE3_NULL;
                        } else {
                            $val[$key] = SQLITE3_BLOB;
                        }
                    } else {
                        $val['id'] = SQLITE3_INTEGER;
                    }
                }
                if (sizeof($keys)>0){
                    $sql = 'INSERT INTO ' . TABLE_NAME . ' ('.implode(', ',$keys).') VALUES ('.implode(', ',$values).')';

                    $query = $db->prepare($sql);

                    foreach ($data as $key => $value) {
                        $query->bindValue(':'.$key, $value, $val[$key]);
                    }

                    $result = $query->execute();

                    echo '{"errMsg":"Modifications enregistrées.","id":"'. $db->lastInsertRowID() . '"}';
                }
            }


        } else {
            echo '{"errMsg":"Data base update failed..."}';
        }
    }

    // delete user
    if ($_POST['action'] == 'delete' && isset($_POST['id'])){
        // remove from base
        $sql = 'DELETE FROM ' . TABLE_NAME . ' WHERE id = :id';

        $query = $db->prepare($sql);

        $query->bindValue(':id', intval($_POST['id']), SQLITE3_INTEGER);

        $result = $query->execute();
        // delete avatar
        if (is_file(avatarPath($_POST['id']))) {
            unlink(avatarPath($_POST['id']));
        }
        echo '{"errMsg":"Fiche effacée.","id":"' . $_POST['id'] . '"}';
    }

    // get info
    if ($_POST['action'] == "get-nbusers"){
        $count = $db->querySingle('SELECT COUNT(*) as count FROM ' . TABLE_NAME );
        echo '{"errMsg":"","nbusers" : "' . $count . '"}';
    }
}