<?php
include 'connect.php';

// check id param
if (isset($_GET['id'])) {
    $id = $_GET['id'];
} else {
    $id = -1;
}

?>
<!DOCTYPE html>
<html lang="en"></html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fiche contact</title>

    <link href="styles.css" rel="stylesheet">
    <script src="xtras/jquery.min.js"></script>

</head>
<body>

<script>
    var user = null;
    var id = <?php echo $id; ?>;
    $(document).ready(function() {
        if (id >= 0) {
            loadData(id);
        }
    });

    let fields = [
            <?php
            $n = 0;
            foreach ($fields as $f => $v) {
                if ($n > 0) echo (", ");
                echo ("\"$f\"");
                $n++;
            }
            ?>
        ];

    function displayUser(){
        fields.forEach((field, idx) => {
                if (user) {
                    if (user[field]) $("#" + field).val(user[field]);
                    else $("#" + field).val("");
                } else {
                    $("#" + field).val("");
                }
            });

        if (user && user.avatarpath) {
            console.log(user.avatar);
            var ts = new Date().getTime(); // to avoid cache problems
            $("#avatar").attr("src", user.avatarpath + "?" + ts);
        } else {
            $("#avatar").attr("src", "avatars/user.png");
        }
    }
    
    function loadData(userId) {
        $.post("./dbio.php", {
                action: "loadbyid",
                id: userId
            },
            function(json) {
                console.log(json);
                var data = JSON.parse(json);
                user = data.data;
                console.log(user);
                displayUser();
            }
        );
    }
</script>


<div class="container">
    <p> <a href="index.php">Trombinoscope</a> </p>

    <div class="tb-picture tb-picture-big">
        <img id="avatar" src="avatars/user.png" alt="avatar">
    </div>




    <?php
        include 'config.php';
        foreach ($fields as $f => $v) {
            echo '<div class="formgroup">';
            echo '<label>' . $v["label"] . '</label> ';
            if ($v["input"] == "textarea") {
                echo '<textarea readonly id="' . $f . '" name="' . $f . '" cols="80" rows="5"';
                if(isset($v['other']) ) { foreach($v['other'] as $index => $value) {echo ' '.$index .($value !== null ? '="'.$value.'"' : '');} } echo '></textarea>'.PHP_EOL;
            } else {
                echo '<input readonly type="' . $v["input"] . '" name="' . $f . '" id="' . $f . '" value=""';
                if(isset($v['other']) ) { foreach($v['other'] as $index => $value) {echo ' '.$index .($value !== null ? '="'.$value.'"' : '');} } echo '>'.PHP_EOL;
            }
            echo ('</div>');
        }
    ?>
    

</div>


</body>
