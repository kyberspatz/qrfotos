<?php

// Trage hier die Domain ein, auf der das Script läuft
$config["url" ] = ""; // Zum Beispiel: https://meinecoolewebseite.de/fotobox/

// Ordner-Setup
$bildordner = "fotos/"; // Hier werden die Fotos gespeichert
$path = 'qrcodes/'; // Hier werden die QR-Codes gespeichert

// Gibt es eine maximale Begrenzung?
$begrenzung = true; // false oder true. Bei false ist das Limit dann automatisch durch die php.ini festgelegt.
$begrenzung_mb = 4; // in Megabyte

// Welche Dateiendungen sind erlaubt? Es ist nur ein Bildupload möglich.
$erlaubte_dateiendungen = array('png','jpg','gif','jpeg');

// Das hier ist ein Securestring für den QR-Code. BITTE VERÄNDERN!		
$securestring = "om2d0mn+seicdjxjm.ndg910eqhy99o100w5hmy3+s102rlgh9sugig.1e3goru78106cka11yb.r70wh";

// Header für die Uploadseite
$header_upload='<!DOCTYPE html>
<html lang="de">
<title>Fotobox QR-Code</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, max-image-preview:none, notranslate" />
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📷</text></svg>">
<link rel="stylesheet" href="../assets/w3.css">

<style>
@media print {
.noPrint {
    display:none;
  }
}

.qr-liste {max-width:20vw;font-size:1.3em;}
</style>
<body>
<div class="w3-container w3-black">
  <h1>Fotobox QR-Codes</h1>
  <p><a href="upload.php" class="w3-button w3-black noPrint">Foto-Upload</a></p>
<p><a href="qr_code_liste.php" class="w3-button w3-black noPrint">QR-Code Liste</a></p>
</div>
<div class="w3-container">
<h2></h2>
';

// Header für die Standardseiten
$header='<!DOCTYPE html>
<html lang="de">
<title>Fotobox QR-Code</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, max-image-preview:none, notranslate" />
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📷</text></svg>">
<link rel="stylesheet" href="assets/w3.css">
<style>

a {color:blue;}

@media print {
.noPrint {
    display:none;
  }
}

.qr-liste {max-width:20vw;font-size:1.3em;}
</style>
<body>
<div class="w3-container w3-black">
  <h1>Fotobox QR-Codes</h1>
</div>
<div class="w3-container">
<p>Wenn du einen QR-Code hast, dann scan ihn mit deinem Handy ein, um ein Bild anzuzeigen.</p>
<p>Falls Du ein Android hast: Du kannst dir einen QR-Scanner zum Beispiel im <a href="https://play.google.com/store/apps/details?id=com.secuso.privacyFriendlyCodeScanner" target="_blank">Google Play Store</a> oder im <a href="https://f-droid.org/de/packages/com.secuso.privacyFriendlyCodeScanner/" target="_blank">F-Droid Store</a> herunterladen.</p>
';

// Header für die Benutzer*innen-Seite
$header_view ='<!DOCTYPE html>
<html lang="de">
<title>Fotobox</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow, noarchive, nosnippet, max-image-preview:none, notranslate" />
<link rel="icon" href="data:image/svg+xml,<svg xmlns=%22http://www.w3.org/2000/svg%22 viewBox=%220 0 100 100%22><text y=%22.9em%22 font-size=%2290%22>📷</text></svg>">
<link rel="stylesheet" href="assets/w3.css">
<style>
@media print {
.noPrint {
    display:none;
  }
}
img { width:100%;
    max-width:600px;}
</style>
<body>
<div class="w3-container w3-black noPrint">
  <h1>Fotobox Bild</h1>
</div>
<h2></h2>
<div class="w3-container" style="text-align:center;">
';

// Footer für die Standardseiten
$footer = "</div>
</body>
</html";

// Footer für die Benutzer*innen-Seite
$footer_view = "</div>
</div>
</body>
</html";


//Danke an https://www.delftstack.com/howto/php/php-generate-random-string/ 
function secure_random_string($length) {
    $rand_string = '';
    for($i = 0; $i < $length; $i++) {
        $number = random_int(0, 36);
        $character = base_convert($number, 10, 36);
        $rand_string .= $character;
    }
 
    return $rand_string;
}

// Encrypt-Decrypt-Funktion: Herkunft: weiß ich nicht mehr
function encrypt($plaintext, $password) {
    $method = "AES-256-CBC";
    $key = hash('sha256', $password, true);
    $iv = openssl_random_pseudo_bytes(16);

    $ciphertext = openssl_encrypt($plaintext, $method, $key, OPENSSL_RAW_DATA, $iv);
    $hash = hash_hmac('sha256', $ciphertext . $iv, $key, true);

    return $iv . $hash . $ciphertext;
}

function decrypt($ivHashCiphertext, $password) {
    $method = "AES-256-CBC";
    $iv = substr($ivHashCiphertext, 0, 16);
    $hash = substr($ivHashCiphertext, 16, 32);
    $ciphertext = substr($ivHashCiphertext, 48);
    $key = hash('sha256', $password, true);

    if (!hash_equals(hash_hmac('sha256', $ciphertext . $iv, $key, true), $hash)) return null;

    return openssl_decrypt($ciphertext, $method, $key, OPENSSL_RAW_DATA, $iv);
	}
 
// Bildname: String aus 36 zufälligen Ziffern und Buchstaben
$bildname = secure_random_string(36);

// Bildpasswort: String aus 36 zufälligen Ziffern und Buchstaben
// Die Bilder liegen verschlüsselt auf dem Server. Das Passwort wird als String mit im QR sichtbar sein.
$bildpw = secure_random_string(36);

// Falls der abschließende Slash in der URL vergessen wurde, wird er hier nochmal automatisch korrigiert
if(substr($config["url" ],-1) !== "/"){$config["url" ] = $config["url" ]."/";}

// Falls noch keine URL konfiguriert wurde, eine Fehlermeldung ausgeben
if(strlen($config["url"])<3)
{
	$header.= '<div class="w3-panel w3-red"><p>Bitte in der <b>_worker.php</b> Zeile #4  noch die URL eintragen.</p></div>';	
	$header.= '<div class="w3-panel w3-red"><p>Und bitte das Verzeichnis /upload mit einem Passwort schützen.</div>';	
}

// Der Bildordner liegt ja ein Verzeichnis höher, deswegen hier noch eine kleine automatische Anpassung
$bildordner_upload = "../".$bildordner; $path = "../".$path;

?>