<?php
include("connect.php");
if (!$editmode){
    header("location:index.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion base contacts</title>

    <link href="styles.css" rel="stylesheet">
    <script src="xtras/jquery.min.js"></script>

</head>

<body>
    
    <div class="container">
        <p> <a href="index.php">Trombinoscope</a> </p>
        <div id="search">
            <p>Nombre de fiches en base: <span id="nbusers">0</span> </p>
            <div class="tb-button" id="create-user">Créer fiche</div>
            <h1>Recherche</h1>
            <input id="search-txt" type="text" value="" placeholder="🔍">
            <div id="search-results"></div>
        </div>
        <div id="contactpicdb">
            <div class="change-pic">🖊️</div>
        </div>
        <div id="form-area">

            <h1 id="form-title">Fiche</h1>
            <form method="post" action="base.php">

                <?php
                include("config.php");
                foreach ($fields as $f => $v) {
                    echo ('<div class="formgroup">');
                    echo ('<label>' . $v["label"] . '</label> ');
                    if ($v["input"] == "textarea") {
                        echo ('<textarea id="' . $f . '" name="' . $f . '" cols="80" rows="5"></textarea>');
                    } else {
                        echo ('<input type="' . $v["input"] . '" name="' . $f . '" id="' . $f . '" value="">');
                    }
                    echo ('</div>');
                }
                ?>
            </form>
            <div id="save-but" class="tb-button">Enregistrer</div>
            <div id="delete-but" class="tb-button tb-red">Effacer fiche</div>
        </div>

    </div>

    <script src="xtras/common.js"></script>
    <script>
        var user = null;

        function resetForm() {
            user = null;
            displayUser();
        }

        function saveUser() {
            if (user) {
                updateUserData();
            } else {
                updateUserData(true);
            }
        }

        function getNbUsers() {
            $.post("./dbio.php", {
                    action: "get-nbusers",
                },
                function(json) {
                    var data = JSON.parse(json);
                    $("#nbusers").text(data.nbusers);
                });
        }


        function chooseContact(e) {
            var id = $(e.target).attr("id");
            var contactId = parseInt(id.slice("choice-".length));
            loadData(contactId);
        }



        $(document).ready(() => {
            startClock();

            resetForm();
            getNbUsers();
            let params = new URLSearchParams(document.location.search);
            var id = params.get("id");
            if (id) {
                loadData(id);
            }
            $("#save-but").click(saveUser);
            $("#delete-but").click(deleteCurrentUser);
            $("#create-user").click(resetForm);

            initSearchField(chooseContact);

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

        function displayUser() {
            if (user) {
                $("#form-title").text("Fiche contact");
                $("#save-but").text("Enregistrer modifications");
                $("#contactpicdb").show();
                $("#create-user").show();
                $("#delete-but").show();
                $(".change-pic").click(() => {
                    window.location.href = "upload-picture.php?id=" + user.id;
                });
                if (user.avatarpath) {
                    $("#contactpicdb").css("background-image", "url(" + user.avatarpath + ")");
                } else {
                    $("#contactpicdb").css("background-image", "url(<?php echo (DEFAULT_PIC); ?>");
                }
            } else {
                $("#form-title").text("Créer nouvelle fiche contact");
                $("#save-but").text("Enregistrer");
                $("#contactpicdb").hide();
                $("#create-user").hide();
                $("#delete-but").hide();
            }
            fields.forEach((field, idx) => {
                if (user) {
                    if (user[field]) $("#" + field).val(user[field]);
                    else $("#" + field).val("");
                } else {
                    $("#" + field).val("");
                }
            });
        }

        function updateUserData(bCreateUser = false) {
            var updates = {};
            if ((!user) || bCreateUser) {
                updates["id"] = -1;
            } else {
                updates["id"] = user.id;
            }
            var nbFields = 0;


            fields.forEach((field, idx) => {

                var newVal = $("#" + field).val();

                var bUpdateField = false;
                if (user) {
                    bUpdateField = (user[field] != newVal) && !(newVal == "" && user[field] == null);
                } else {
                    bUpdateField = !(newVal == "");
                }

                if (bUpdateField) {
                    updates[field] = newVal;
                    nbFields++;
                }

            });

            console.log(updates);

            if (nbFields > 0) {
                $.post("./dbio.php", {
                        action: "update",
                        updates: JSON.stringify(updates)
                    },
                    function(json) {
                        console.log(json);
                        var r = JSON.parse(json);
                        if (r.errMsg) informUser(r.errMsg);
                        if (r.id) window.location = "base.php?id=" + r.id;
                    });
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
                    displayUser();
                }
            );
        }

        function deleteCurrentUser(){
            if (user){
                deleteUser(user.id)
            }
        }
    </script>

</body>

</html>