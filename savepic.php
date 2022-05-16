<?php
$img = $_POST['imgBase64'];
$id = $_POST['id'];
$img = str_replace('data:image/jpeg;base64,', '', $img);
$img = str_replace(' ', '+', $img);
$fileData = base64_decode($img);
//saving
$fileName = sprintf('avatars/%08d.jpg',$id);
if (file_put_contents($fileName, $fileData)){
    echo($fileName);
}else{
    echo("KO");
}
?>