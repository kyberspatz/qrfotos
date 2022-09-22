<?php

// Worker einbinden
include_once("../_worker.php");

// Header einbinden
echo $header_upload;	

// In der stichworte.php stehen die Stichworte zu den QR-Dateien. Falls es die Datei noch nicht gibt, wurde noch kein Bild erstellt.
if(!is_file("stichworte.php")){

// Fehlermeldung generieren	
echo '
<div class="w3-panel w3-red">
<p>Es konnten noch keine QR-Codes gefunden werden.</p>
</div> ';

// Footer anzeigen	
echo $footer;
}

// Falls es die stichworte.php doch schon gibt, dann
else {

echo '<p class="noPrint"><a href="javascript:window.print()"><button class="w3-button w3-black">Liste ausdrucken</button></a></p>';

// stichworte.php einbinden
include("stichworte.php");

// das Bildarray auslesen aus der JSON
$bildarray = json_decode($bildarray,1);

// jedes Bild aus dem QR-Verzeichnis lesen und mit den Stichworten versehen
foreach($bildarray as $bildname=>$stichworte)
	{
		$bildname = substr($bildname,0,strrpos($bildname,"."));
		if(substr($bildname,-1) !== "."){$bildname = $bildname.".png";} else {$bildname = $bildname."png";}
		echo '<img src="'.$path.$bildname.'" class="qr-liste">';
		echo '<span class="qr-liste">'.$stichworte.'</span>';
		echo "<hr>";
	}
}

?>