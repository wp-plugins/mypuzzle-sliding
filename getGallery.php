<?php 
header('Content-Type: application/json');

if (!isset($_GET["dir"]) || $_GET["dir"]==''){$dir = "gallery";} else {$dir = $_GET["dir"];}

function listDirImages($dir) { //$dir is the name of the directory you want to list the images.
    $files = scandir($dir); //scans the directory's files
    $preg = "/.(jpg|gif|png|jpeg)/i"; //match the following files, can be changed to limit or extend range, ie: png,jpeg,etc.

    $images = array();
    $id = 0;
    foreach($files as $img) { //loop through directory files
        if(substr($img, 0, 1) != '.') { //ignore anything starting with a period
            $images[basename($img)]= $img;
            $id++;
        }
    }
    $data = json_encode($images);
    echo $_GET['callback'] . '(' . $data . ');';
}

listDirImages($dir); //call function ../jigsaw/img

?>