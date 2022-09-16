<?php

// Den Worker laden
include_once("_worker.php");

// Den Header anzeigen
echo $header_view;

//Falls ein GET-String mitgesendet wurde (durch den Aufruf eines QR-Codes)
if(isset($_GET['bild']))
{
	// Zur Sicherheit wird das Dateiarray geholt.
	$files = array_slice(scandir($bildordner),2);
	
	// Der Eingabestring wird gesäubert
	$bild = trim(strip_tags($_GET['bild']));
	
	// Falls der String nicht mit einer Datei aus dem Array übereinstimmt, gibt es das Bild nicht.
	if(!in_array($bild,$files))
	{
		echo "Bild konnte nicht gefunden werden.";
		exit;
	} 
	
	// Falls es das Bild gibt
	else {
		
	// Falls kein Code zum entschlüsseln mitgesendet wurde	
	if(!isset($_GET['c']))
	{
		echo "Es gab einen Fehler.";
		exit;
	} 
	
	//Falls ein Code mitgesendet wurde
	else {
	
	// Das Bildpasswort wird gesäubert
	$bildpw = trim(strip_tags($_GET['c']));	
	
	// Das Bild wird entschlüsselt
	$bild = decrypt(file_get_contents($bildordner.$bild),$bildpw);
	
	// Das Bild wird angezeigt
	echo '<img src="data:image/png;base64,'.$bild.'">';
	}
}
}

// Der Footer wird eingebunden
echo $footer_view;
?>