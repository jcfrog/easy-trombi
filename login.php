<?php
include 'connect.php';
if (isset($_POST['pwd'])){
    include 'PasswordTools.php';
    $passwordTools = new Core\Lib\PasswordTools(false,false);

    $psFile = md5($_POST['pwd']);
    $dbdir = dirname(DB_PATH).DIRECTORY_SEPARATOR;

    
    if (!file_exists($dbdir.$psFile)) {
        
        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dbdir));
        $files = array();
        foreach ($iterator as $info) {
            $f = basename($info->getFilename());
            if ( $f != '.' && $f != '..') {
                $files[] = $f;
            }
        }
        if (count($files) == 2) {
            file_put_contents($dbdir.$psFile,$passwordTools->create_hash($_POST['pwd']) );
            $htaccess = 'Options -Indexes'."\n".'ErrorDocument 403 '.dirname($_SERVER['SCRIPT_URI']).'/index.php'."\n".'Deny from all';
            file_put_contents($dbdir.'.htaccess',$htaccess);
        }
    }

    if (file_exists($dbdir.$psFile)) {
        $md5pw = file_get_contents($dbdir.$psFile);
        if ($passwordTools->validate_password($_POST['pwd'], $md5pw) ){
            session_start();
            $_SESSION['admin']=true;
            header("location:index.php");
            exit();
        } else{
            $errorMsg = "Mot de passe incorrect";
        }
    } else {
        $md5pw = '';
        $errorMsg = "Mot de passe incorrect";
    }

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link href="styles.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="login">
            <h1>Mode édition</h1>
            <form action="login.php" method="post">
                <label for="pwd">Mot de passe</label>
                <input type="text" id="pwd" name="pwd" placeholder="">
                <input type="submit" value="Valider">
            </form>
            <?php if (isset($errorMsg)) {
                echo '<p class="errormsg">'.$errorMsg.'</p>';
            }
            ?>
            <p>Retour <a href="index.php">Trombinoscope</a></p>
    </div>
</body>
</html>