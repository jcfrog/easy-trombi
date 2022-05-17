<?php
include("config.php");
if (isset($_POST["pwd"])){
    session_start();
    if (md5($_POST["pwd"]) == $md5pw){
        $_SESSION["admin"]=true;
        header("location:index.php");
    }else{
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
            <?php if ($errorMsg) {
                echo("<p class='errormsg'>$errorMsg</p>");
            }
            ?>
            <p>Retour <a href="index.php">Trombinoscope</a></p>
    </div>
</body>
</html>