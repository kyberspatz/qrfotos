<?php
// Worker einbinden
include_once("_worker.php");

//Falls noch kein Setup geschehen ist, das Setup starten
if(!is_dir("assets/phpqrcode/")){header("Location: setup.php");exit;}

//Header einbinden
echo $header;

//Footer einbinden
echo $footer;
?>