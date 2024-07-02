<?php include 'connect.php';?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $siteTitle;?></title>
    <link href="styles.css" rel="stylesheet">
    <link href="styles-custom.css" rel="stylesheet">
    <link rel="icon" type="image/png" href="favicon.ico" />
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

        $search = isset($_GET['q']) ? $_GET['q'] : '';
        $sql = 'SELECT * FROM ' . TABLE_NAME . ' WHERE ';
        foreach ($mandatories as $mandatory) {
            $sql .= $mandatory . ' LIKE :search OR ';
        }
        $sql = rtrim($sql, ' OR ');
        $sql .= ' ORDER BY ' . current($mandatories) . ' LIMIT :first, :perpage';
        $query = $db->prepare($sql);
        $query->bindValue(':search', '%' . $search . '%', SQLITE3_TEXT);

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

        <h1>
            <?php if ($siteLogo != '') echo '<img src="'.$siteLogo.'" alt="logo">';?>
            <?php echo $siteTitle;?>
        </h1>
        <?php if ($editmode) echo '<div style="float:right;">
            <a href="base.php">Créer fiche</a> •
            <a href="?editpw=stop">Sortir du mode édition</a>
        </div>'.PHP_EOL; ?>
        <p><?php echo $siteSubtitle;?></p>

        <form action="index.php" method="get">
            <input type="text" name="q" placeholder="Rechercher..." required>
            <input type="submit" value="Rechercher">
        </form>



        <?php

        // if search
        if ($search != ''){
            echo '<p>Résultats de la recherche pour <b>'.$search.'</b> :</p>'.PHP_EOL;
            echo '<p><a href="index.php">Retour à la liste complète</a></p>'.PHP_EOL;
        }


        function dispRow($row, $editMode, $fields){
            $html = '';
            $path = avatarPath(intval($row["id"]));
            if (!file_exists($path)){
                $path = "avatars/user.png";
            }else{
                // add modification time as a param to avoid cache problems
                $path .= "?t=".filemtime($path);
            }
            $html .= '<div class="tb-card">';
            if (!$editMode) $html .= '<div class="change-pic"><a href="file.php?id='.$row["id"].'">🔍</a></div>'.PHP_EOL;
            if ($editMode) $html .= '<div class="change-pic"><a href="base.php?id='.$row["id"].'">🖊️</a></div>'.PHP_EOL;
            if ($editMode) $html .=  '<div class="delete-file" data-id="'.$row["id"].'">🗑️</div>'.PHP_EOL;
                $html .= '<div class="tb-picture">';
                    $html .= '<img src="'. $path .'" alt="">';
                $html .= '</div>';
                $html .= '<div class="tb-info">';
                $html .= '<div class="tbi-firstname">' . $row["firstname"] . ' <span class="tbi-name">' . $row["name"] . "</span></div>";
                //foreach($row as $k => $v){
                foreach($fields as $k => $v){
                    $v = $row[$k];
                    if (($k != 'id') && ($k != 'name') && ($k != 'firstname')) {
                        $vdisp = $v ;
                        //if (strlen($v) > 50) $vdisp = substr($v,0,50).'...';
                        if (strlen($v) > 0) {

                            if ($fields[$k]['input'] == 'date'){
                                $date = strtotime($v);
                                $vdisp = strftime('%d %B',$date);
                            }
                            if ($fields[$k]['display-label'] == true)
                                $html .= '<div class="tbi-'.$k.'"><span class="tbi-label tbi-label-'.$k.'">'.$fields[$k]['label'].'</span> : '.$vdisp.'</div>'.PHP_EOL;
                            else
                                $html .= '<div class="tbi-'.$k.'">'.$vdisp.'</div>'.PHP_EOL;
                        }
                    }
                }
                $html .= '</div>';
            $html .= '</div>';
            return $html;
        }

        if ($nb == 0){
            echo '<p>Aucune fiche trouvée... Passez en <a href="login.php">mode édition</a> pour en créer une.</p>'.PHP_EOL;
        }else{
            while ($row = $res->fetchArray(SQLITE3_ASSOC)) {
                echo dispRow($row, $editmode, $fields);
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