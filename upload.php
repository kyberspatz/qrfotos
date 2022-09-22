<?php

// Den Worker laden
include_once("../_worker.php");

// Den Header anzeigen
echo $header_upload;

// Falls noch nichts hochgeladen wurde, wird nun das Formular angezeigt.
if(!isset($_POST["submit"])) 
{
	
	if($begrenzung){ $upload_mb = $begrenzung_mb; } else {
		$max_upload = (int)(ini_get('upload_max_filesize'));
		$max_post = (int)(ini_get('post_max_size'));
		$memory_limit = (int)(ini_get('memory_limit'));
		$upload_mb = min($max_upload, $max_post, $memory_limit);
	}
	?>
	
	<p>Die maximale Größe für ein Bild beträgt <?php echo $upload_mb; ?> Megabyte.

	<form action="upload.php" method="post" enctype="multipart/form-data">
	Wähle ein Bild für den Upload:
	<p><input type="file" name="fileToUpload" id="fileToUpload" class="w3-button w3-white"></p>
	Gebe ein Stichwort oder mehrere Stichworte ein:
	<p><input type="Stichworte" name="stichworte" required placeholder="hier Stichwort(e) eingeben" class="w3-button w3-white" style="border:1px dashed black;"></p>
	<p><input type="submit" value="Bild hochladen" name="submit" class="w3-button w3-black"></p>
	</form>

<?php
} 
// Falls eine Datei zum Upload ausgewählt wurde, Stichworte eingetragen wurden und auf "Bild hochladen" geklickt wurde
else {

// Das Zielverzeichnis ist der Bildordner (Festgelegt im Worker)
$target_dir = $bildordner_upload;

// Die Zieldatei wird nochmals genau beschrieben
$target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);

// Der Upload ist tendenziell beanstandungslos
$uploadOk = 1;

// Um welchen Dateityp es sich handelt, wird nun herausgefunden
$imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

// Schauen, ob es sich wirklich um ein Bild handelt oder nur ein Fakebild.
if(isset($_POST["submit"]))
	{
	
	// Aber vorher wird geschaut, ob denn auch Stichworte eingetragen wurden.
	if(!isset($_POST['stichworte'])){echo "Bitte gib zu dem Upload mindestens ein Stichwort ein.";exit;}
	
	//Falls Stichworte eingetragen wurden
	$stichworte_neu = trim(strip_tags($_POST['stichworte']));
	
	// Jetzt folgt die Überprüfung, ob es sich bei der Datei auch wirklich um ein Bild handelt
	$check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
	
	// Falls der Check korrekt ist
	if($check !== false) 
	{
		// Dann wird der Upload freigegeben
		$uploadOk = 1;
	// Falls es sich nicht um ein Bild handelt
	} else {
    echo "Sorry; was du gerade versuchst hochzuladen, scheint kein Bild zu sein.";
    $uploadOk = 0;
  }
}

// Falls das Bild schon existiert
if (file_exists($target_file)) {
  echo "Sorry, die Datei gibt es schon.";
  $uploadOk = 0;
}

// Falls eine Begrenzung an MB aktiv ist (siehe Worker), dann wird der Upload abgelehnt, falls das Bild die maximale MB-Größe überschreitet.
if($begrenzung){
// Die Dateigröße wird überprüft. Ist das Bild zu groß, wird der Upload abgelehnt.
if ($_FILES["fileToUpload"]["size"] > ($begrenzung_mb*1000000)) {
  echo "<p>Sorry, das Bild ist zu groß. Das Bild darf maximal $begrenzung_mb MB groß sein.</p>";
  $uploadOk = 0;
}
}

// Hier erlauben wir nur bestimmte Dateiformate
if(!in_array($imageFileType,$erlaubte_dateiendungen)){
/*
if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg"
&& $imageFileType != "gif" ) {
	*/
  echo "<p>Sorry, nur folgende Dateiendungen sind erlaubt: <b>";
  for($a=0;$a<count($erlaubte_dateiendungen)-1;$a++)
	  {
		echo ".".$erlaubte_dateiendungen[$a].", ";
	  }
	echo "</b> oder <b>.".$erlaubte_dateiendungen[count($erlaubte_dateiendungen)-1];
  echo "</b>.</p>";
  $uploadOk = 0;
}

// Checken, ob der Upload in Ordnung ist, oder ob es Fehler gibt
if ($uploadOk == 0) {
  echo "Die Datei konnte nicht hochgeladen werden.";

// Falls alles Parameter stimmen, wird nun versucht, das Bild auf den Server hochzuladen.
} else {
	$bildendung = ".".$imageFileType;
	file_put_contents($_FILES["fileToUpload"]["tmp_name"],encrypt(base64_encode(file_get_contents($_FILES["fileToUpload"]["tmp_name"])),$bildpw));
  if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $bildordner_upload.$bildname.$bildendung)) {
	 
	// Falls es die Datei stichworte.php noch nicht gibt, wird sie nun angelegt. 
	if(!is_file("stichworte.php"))
	{
		$bildarray["$bildname.$bildendung"] = $stichworte_neu;
		$put = '<?php
	$bildarray = \''.json_encode($bildarray,1).'\';
?>';

file_put_contents("stichworte.php",$put);	

// Falls es die Datei stichworte.php gibt, wird sie nun ausgelesen und neu abgespeichert.
	} else {
		include("stichworte.php");
		$bildarray = json_decode($bildarray,1);
		$bildarray[$bildname.$bildendung] = $stichworte_neu;
		
$put[] = '<?php
	$bildarray = \''.json_encode($bildarray,1).'\';
?>';

	$put = implode("\n",$put);
	file_put_contents("stichworte.php",$put);
		
	}
	
// Jetzt wird der QR-Parser eingebunden
include '../assets/phpqrcode/qrlib.php';

//Das ist der Text für den QR-Code. er besteht aus der Domain-URL, dem Bildnamen und dem Passwort, mit dem das Bild dann entschlüsselt wird.

$text = $config["url" ]."view.php?bild=".$bildname.$bildendung."&c=".$bildpw;
$file = $path.$bildname.".png";
  
  
// $ecc ist die Fehlerkorrektur. Standard: 'L', 'H' = Hoch
$ecc = 'H';
$pixel_size = 10;
$frame_size = 10;
  
// Generiert den QR-Code und speichert ihn in dem QR-Verzeichnis ab
QRcode::png($text, $file, $ecc, $pixel_size, $frame_size);
	
// Der QR-Code wird schonmal als Vorschau angezeigt
echo "<p>Die Datei wurde erfolgreich hochgeladen.</p>";
echo "<center><img src='".$file."' style='max-width:20vw;'></center>";
echo "<hr>";

// Links
echo '<p><a href="../view.php?bild='.$bildname.$bildendung.'&c='.$bildpw.'" class="w3-button w3-black">Bildvorschau</a></p>';
echo '<p><a href="upload.php" class="w3-button w3-black">Noch ein Bild hochladen</a></p>';

  } else {
	  
	 // Falls das Hochladen doch nicht geklappt hat 
    echo "<p>Sorry, es gab einen Fehler beim Hochladen.</p>";
  }
}

exit;


















  

}
echo $footer;
?>
