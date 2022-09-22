<?php

// Falls es den PHP-QR-Parser noch nicht gibt, wird das Setup gestartet.
if(!is_dir("assets/phpqrcode")){
	
// Falls das Setup noch nicht manuell gestartet wurde, wird eine Info angezeigt und ein Knopf, um das Setup zu starten.	
if(!isset($_POST['setupgo']))
{
	?>
	<div style=" font-family: Arial, sans-serif;">		
	<h2 style=" font-family: Arial, sans-serif;">Fotobox QR-Codes Setup</h2>
	<p>Es müssen noch externe Daten heruntergeladen werden: ein PHP QR-Generator, ein CSS-Framework. Klick 'Setup starten', um die Daten automatisch herunterzuladen und auf dem Server zu speichern. Hinweis: Alle PHP-Dateien der Fotobox brauchen Schreibrechte.</p>
	<form action="?" method="POST">
	<input type="hidden" name="setupgo">
	<button type="submit">Setup starten</button>
	</form>
	</div>
	<?php
	exit;
}

// Falls der Knopf "Setup starten" gedrückt wurde
else{

// Falls es das Verzeichnis "assets" noch nicht gibt, wird es erstellt.
if(!is_dir("assets")){mkdir("assets");}

// Falls es das Verzeichnis "qrcodes" noch nicht gibt, wird es erstellt.
if(!is_dir("qrcodes")){mkdir("qrcodes");}

// Falls es das Verzeichnis "fotos" noch nicht gibt, wird es erstellt.
if(!is_dir("fotos")){mkdir("fotos");}

// Falls es das Verzeichnis "fotos" noch nicht gibt, wird es erstellt.
if(!is_dir("upload")){mkdir("upload");}

// upload.php wird in das Verzeichnis /upload verschoben
if(is_file("upload.php")){rename("upload.php", "upload/upload.php");}

// qr_code_liste.php wird in das Verzeichnis /upload verschoben
if(is_file("qr_code_liste.php")){rename("qr_code_liste.php", "upload/qr_code_liste.php");}

// upload.index.php wird in das Verzeichnis /upload verschoben und in index.php umbenannt
if(is_file("upload_index.php")){rename("upload_index.php", "upload/index.php");}

// Falls es die Datei "w3.css" noch nicht gibt, wird es erstellt. Es ist das CSS-Framework, dass die Seite aufhübscht.
if(!is_file("assets/w3.css")){file_put_contents("assets/w3.css",file_get_contents("https://www.w3schools.com/w3css/4/w3.css"));}

// Nun das Herzstück, der PHP-QR-Code-Parser. Die Datei liegt auf Sourceforge und wird direkt abgerufen.
// Danke an https://stackoverflow.com/posts/8889126/revisions um zu zeigen, wie eine .zip entpackt wird
$file = "phpqrcode-2010100721_1.1.4.zip";
file_put_contents("assets/".$file,file_get_contents("https://sourceforge.net/projects/phpqrcode/files/releases/".$file."/download"));

// get the absolute path to $file
$path = pathinfo(realpath("assets/".$file), PATHINFO_DIRNAME);

$zip = new ZipArchive;
$res = $zip->open("assets/".$file);
if ($res === TRUE) 
{
	$zip->extractTo($path);
	$zip->close();
	
// Die .zip-Datei wird nicht mehr gebraucht und nun gelöscht.
	unlink("assets/".$file);
	
	
	
} else {

// Falls jetzt etwas nicht funktioniert, liegt es vermutlich an den nicht korrekt gesetzten Schreibrechten.
echo "Konnte .zip-Datei $file nicht öffnen. Sind die Schreibrechte korrekt gesetzt?"; exit;
}
}
}



   

// Nochmal ein Check, ob jetzt alles wichtige da ist
if(is_dir("assets/phpqrcode") && is_dir("qrcodes") &&is_dir("assets") && is_dir("fotos") && is_dir("upload") && is_file("assets/w3.css"))
// Falls alles wichtigen Dateien da sind
{
include("_worker.php"); echo $header;

// Letzter Check: Wurde die URL gesetzt? Das ist absolut wichtig für die korrekte Generierung der QR-Codes
if(strlen($config["url"]>3))
{
	echo '<p>Setup okay. Alles funktioniert.<p>';
	header("Location: index.php");
}

} 
// Falls nicht alle Dateien da sind, ist irgendetwas beim Setup schief gelaufen.
else {echo '<p style=" font-family: Arial, sans-serif;">Das Setup ist nicht vollständig.</p>';}
?>