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

$mandatories = [];

foreach($fields as $label => $field) {
    if (isset($field['mandatory']) && $field['mandatory'] === true) {
        $mandatories[] = $label;
    }
}

if (empty($mandatories)) {
    exit('Erreur : au moins un champ doit être étiqueté comme ***mandatory***...');
}
reset($mandatories);


// create table if not exists
$tableCheck =$db->query('SELECT name FROM sqlite_master WHERE name="'.TABLE_NAME.'"');
if ($tableCheck->fetchArray() === false){
    //echo "Table does not exist";
    $sql = 'CREATE TABLE IF NOT EXISTS "'.TABLE_NAME.'" (
        "id"	INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE';
        foreach($fields as $f => $k){
            $sql .= ',
            "'.$f.'" TEXT';
        }
    $sql .=');';
    echo 'La table a été créée avec la requête suivante : <br>';
    echo $sql;
    $db->query($sql);
    echo '<br>Rendez-vous à la <a href="login.php">page d\'édition</a> afin de personnaliser le mot de passe.<br>'.PHP_EOL;
} else {
    // check if configuration was changed
    $cols = [];
    $results = $db->query('PRAGMA table_info('.TABLE_NAME.')');
    while ($row = $results->fetchArray()) {
        $cols[] = $row['name'];
    }
    $f = array_keys($fields);
    array_unshift($f,'id');

    $del = array_diff($cols, $f);
    $add = array_diff($f,$cols);

    // new field was added
    if (!empty($add)) {
        foreach ($add as $key => $index) {
            // On ajoute les nouveaux index
            $db->query('PRAGMA foreign_keys');
            $db->query('PRAGMA foreign_keys = "0"');
            $db->query('PRAGMA foreign_keys');
            $db->query('ALTER TABLE "main"."contacts" ADD COLUMN "'.$index.'" INTEGER');
            $db->query('PRAGMA database_list');
        }
    }
    // fields was removed
    if (!empty($del)) {
        // No confirmation to modify database
        if (!isset($_POST['yes'])) {
?><!doctype html>
            <head>
                <meta charset="UTF-8">
                <title>Attention !</title>
            </head>
            <body>
                <div id="main" role="main">
                    <p>Vous avez modifié la configuration en supprimant un ou des champs.</p>
                    <p>Vous pouvez modifier à nouveau le fichier de configuration pour ajouter les champs manquants.</p>
                    <p>Les champs manquants sont :</p>
                    <ul>
                        <?php foreach ($del as $key => $col) {
                            echo '<li>'.$col.'</li>'.PHP_EOL;
                        } ?>
                    </ul>
                    <p>Si vous continuez, les colonnes correspondantes et les données qu'elles contiennent seront supprimées.</p>
                    <form action="./index.php" method="post">
                        <p>Voulez-vous continuer ? </p>
                        <p>
                            <input name="yes" type="submit" value="OUI">
                        </p>
                    </form>
                </div>
            </body>
            </html>
<?php
exit();
        } else {
            $tmp_table = 'tmp'.md5(date('dmYiiss'));
            $columns = '';
            $select = '';

            foreach ($f as $key => $field) {
                switch ($field) {
                    case 'id':
                        $columns .= PHP_EOL.'"id" INTEGER PRIMARY KEY AUTOINCREMENT UNIQUE,'.PHP_EOL;
                        break;
                    default:
                        $columns .= '"'.$field.'" TEXT,'.PHP_EOL;
                        break;
                }
                $select .= $field.', ';
            }
            $columns = substr($columns,0,-2);
            $select = substr($select,0,-2);

            // we close connexion to avoid error "database table is locked"
            $db->close();
            unset($db);
            // we open new one
            $db = new MyDB();
            // we waiting for 5000 ms to be sure the locked is clear
            $db->busyTimeout(5000);

            // excess fields are removed
            $db->query('BEGIN TRANSACTION');
            $db->query('CREATE TEMPORARY TABLE "'.$tmp_table.'" ('.$columns.')');
            $db->query('INSERT INTO '.$tmp_table.' SELECT '.$select.' FROM '.TABLE_NAME);
            $db->query('PRAGMA foreign_keys');
            $db->query('PRAGMA foreign_keys = "0"');
            $db->query('PRAGMA foreign_keys');
            $db->query('DROP TABLE '.TABLE_NAME);
            $db->query('CREATE TABLE '.TABLE_NAME.'('.$columns.')');
            $db->query('INSERT INTO '.TABLE_NAME.' SELECT '.$select.' FROM '.$tmp_table);
            $db->query('DROP TABLE '.$tmp_table.'');
            $db->query('COMMIT');
        }
    }
}

/* edit mode logout handling */
session_start();
if (isset($_GET['editpw'])&& !empty($_GET['editpw'])){
    if ($_GET['editpw'] == "stop"){
        unset($_SESSION['admin']) ;
    }
}
$editmode = isset($_SESSION['admin']);


function avatarPath($id){
    return sprintf('avatars/%08d.jpg', intval($id));
}

