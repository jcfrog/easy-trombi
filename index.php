<?php include 'connect.php';?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle;?></title>
    <link href="styles.css" rel="stylesheet">
</head>

<body>
    <div class="container">
        <?php

        // pagination
        // On détermine sur quelle page on se trouve
        if (isset($_GET['page']) && !empty($_GET['page'])) {
            $currentPage = (int) strip_tags($_GET['page']);
        } else {
            $currentPage = 1;
        }
        $nb = $db->querySingle("SELECT COUNT(*) AS nb FROM " . TABLE_NAME);
        $pages = ceil($nb / NB_PER_PAGE);
        $first = ($currentPage * NB_PER_PAGE) - NB_PER_PAGE;
        reset($mandatories);

        $sql = 'SELECT * FROM ' . TABLE_NAME . ' WHERE 1 ORDER BY '.current($mandatories).' LIMIT :first, :perpage';
        $query = $db->prepare($sql);

        $query->bindValue(':first', $first, SQLITE3_INTEGER);
        $query->bindValue(':perpage', NB_PER_PAGE, SQLITE3_INTEGER);

        $res = $query->execute();
        ?>

        <?php
        if ($nb > NB_PER_PAGE){ //si pagination nécessaire
        ?>

        <div style="float:right;">
                <div class="pagination">

                    <?php
                    if ($currentPage > 1) {
                    ?>
                    <!-- Lien vers la page précédente (désactivé si on se trouve sur la 1ère page) -->
                    <div class="page-item">
                        <a href="./?page=<?= $currentPage - 1 ?>" class="page-link"><<</a>
                    </div>
                    <?php
                    }
                    ?>

                    <?php for ($page = 1; $page <= $pages; $page++) : ?>
                        <!-- Lien vers chacune des pages (activé si on se trouve sur la page correspondante) -->
                        <div class="page-item <?= ($currentPage == $page) ? "active" : "" ?>">
                            <a href="./?page=<?= $page ?>" class="page-link"><?= $page ?></a>
                        </div>
                    <?php endfor ?>

                    <?php
                    if ($currentPage < $pages) {
                    ?>
                    <!-- Lien vers la page suivante (désactivé si on se trouve sur la dernière page) -->
                    <div class="page-item">
                        <a href="./?page=<?= $currentPage + 1 ?>" class="page-link">>></a>
                    </div>
                    <?php
                    }
                    ?>

                </div>
        </div>

        <?php
        } // si pagination nécessaire
        ?>

        <h1><?php echo $siteTitle;?></h1>
        <?php if ($editmode) echo '<div style="float:right;">
            <a href="base.php">Créer fiche</a> •
            <a href="?editpw=stop">Sortir du mode édition</a>
        </div>'.PHP_EOL; ?>
        <p><?php echo $siteSubtitle;?></p>

        <?php
        if ($nb == 0){
            echo '<p>Aucune fiche trouvée... Passez en <a href="login.php">mode édition</a> pour en créer une.</p>'.PHP_EOL;
        }else{
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                $path = avatarPath(intval($row["id"]));
                if (!file_exists($path)){
                    $path = "avatars/user.png";
                }else{
                    // add modification time as a param to avoid cache problems
                    $path .= "?t=".filemtime($path);
                }
            ?>
                <div class="tb-card">
                    <?php if ($editmode) echo '<div class="change-pic"><a href="base.php?id='.$row["id"].'">🖊️</a></div>'.PHP_EOL; ?>
                    <?php if ($editmode) echo '<div class="delete-file" data-id="'.$row["id"].'">🗑️</div>'.PHP_EOL; ?>
                    <div class="tb-picture">
                        <img src="<?php echo $path; ?>" alt="">
                    </div>
                    <div class="tb-info">
                        <div class="tbi-name"><?php echo $row["firstname"] . " <b>" . $row["name"] . "</b>"; ?></div>
                        <?php
                        foreach($row as $k => $v){
                        if (($k != 'id') && ($k != 'name') && ($k != 'firstname')) {
                                $vdisp = $v ;
                                if ($fields[$k]['input'] == 'date'){
                                    $date = strtotime($v);
                                    $vdisp = strftime('%d %B',$date);
                                }
                                echo '<div class="tbi-'.$k.'">'.$vdisp.'</div>'.PHP_EOL;
                            }
                        }
                        ?>

                    </div>
                </div>
            <?php
            } // while
        }// if
        ?>


    </div>
    <div id='github-footer'>
        <p>Propulsé par <a href='https://github.com/jcfrog/easy-trombi'>Easy trombi</a>.</p>
        <?php if(!$editmode && $bDispLoginLink){ ?>
            <p> <a href="login.php">Mode édition</a></p>
        <?php } ?>
    </div>
    <script src="xtras/jquery.min.js"></script>
    <script src="xtras/common.js"></script>
</body>
</html>