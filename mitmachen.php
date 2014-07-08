<?php
/* Gast will mitmachen  */
require "general_defs.inc.php";
require "general_methods.inc.php";
require "connect.inc.php";
check_session(true, array(), array("Gast"));

// Zur Vereinfachung
$user = $_SESSION['user'];

// -------------------------------------------------------------------------------------------
//	dataOK()
//	Gibt true oder einen Fehler-String zurueck.
//  Diese Methode steht derzeit nicht in den general_methods.inc.php, weil dort sonst 
//  connect.inc.php required waere.
//  
//  Ausserdem spart man sich alle Parameter, weil die hier eh bekannt sind.
//
//  TODO: Ueberlegen, ob es Sinn hat, connect.inc.php in genral_method.inc.php zu includen! 
//        Gibt es noch andere DB-Abfragen, die man dort gerne haette? 
//        Problem: Ohne connect.inc.php koennte man dann auch general_method.inc.php
//        nicht mehr includen!
// -------------------------------------------------------------------------------------------
function dataOK() {

  if ((!$_POST['username']) || ($_POST['username'] == "") ||
      (!$_POST['name'])     || ($_POST['name']     == "") ||
      (!$_POST['vorname'])  || ($_POST['vorname']  == "") ||
      (!$_POST['mail'])     || ($_POST['mail']     == "") ||
      (!$_POST['pass'])     || ($_POST['pass']     == "") ||
      (!$_POST['pass2'])    || ($_POST['pass2']    == "")) {
 	  return "Bitte alle Felder ausf&uuml;llen."; 
 	}
        
  if($_POST['pass'] != $_POST['pass2']) {
    return "Die Passw&ouml;rter stimmen nicht &uuml;berein.";
  }

  $err = check_login_name($_POST['username'], ILLEGAL_CHARS);
  if ($err !== true) return str_replace("Ung", "Benutzername: ung", $err);
   
  $err = check_login_name($_POST['pass'], " "); // only illegal character for password is space
  if ($err !== true) return str_replace("Ung", "Passwort: ung", $err);
  
	// all ok
  return true;
}

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>

<?php 
$head = create_head("Ich will mitmachen"); 
print $head;
?>

<body>

<?php
$menu = create_menu();
print $menu;

/***************************
 * Starttext konfigurieren
 ***************************/
if(!isset($_POST['submit'])) {
  $text = "Hier kannst Du Dir einen Account anlegen.<br><br>
           Sobald Dein Wetteinsatz beim Administrator eingetroffen<br>
           ist, wird Dein Account freigeschaltet.";

} else { // 'Submit' wurde gedrueckt
  $err = dataOk();
	if ($err !== true) {
		$text = "<br><br><font color=\"red\">$err</font><br>";
	} else {
		$text = "Deine Anmeldung wurde erfolgreich versendet!<br>
						 Du wirst bald eine Mail an <b>". $_POST['mail']. "</b> erhalten, worin die folgenden Schritte erl&auml;utert sind.<br><br>
						 Viel Spa&szlig; bei der beTTer - Tipprunde!";
	}
}

?>

<div align="center">
  <?php echo $text; ?>
  <br><br>
</div>
    
<form action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
<table width="400" bgcolor="#000000" border="0" cellpadding="5" cellspacing="1" align="center">
  <tr>
    <td bgcolor="#e7e7e7" align="center" colspan="2">
    <b>Ich will mitmachen</b>
    </td>
  </tr>
  <tr>
		<td width="170" bgcolor="#e7e7e7">Name</td>
		<td width="230" bgcolor="#ffffff">
		<input type="text" name="name" size="20" class="input"
		<?php if($_POST['name'] !="" ) {echo ' value="'.$_POST['name'].'"';} ?>></td>
  </tr>
  <tr>
		<td width="170" bgcolor="#e7e7e7">Vorname</td>
		<td width="230" bgcolor="#ffffff">
		<input type="text" name="vorname" size="20" class="input"
		<?php if($_POST['vorname'] !="" ) {echo ' value="'.$_POST['vorname'].'"';} ?>></td>
	</tr>
  <tr>
		<td width="170" bgcolor="#e7e7e7">Username</td>
		<td width="230" bgcolor="#ffffff">
		<input type="text" name="username" size="20" class="input"
		<?php if($_POST['username'] !="" ) {echo ' value="'.$_POST['username'].'"';} ?>></td>
	</tr>
  <tr>
		<td width="170" bgcolor="#e7e7e7">Passwort</td>
		<td width="230" bgcolor="#ffffff"><input type="password" name="pass" size="20" class="input"></td>
	</tr>
  <tr>
		<td width="170" bgcolor="#e7e7e7">Passwort wiederholen</td>
		<td width="230" bgcolor="#ffffff"><input type="password" name="pass2" size="20" class="input"></td>
	</tr>
	<tr>
		<td width="170" bgcolor="#e7e7e7">E-M@il</td>
		<td width="230" bgcolor="#ffffff">
		<input type="text" name="mail" size="20" class="input"
		<?php if($_POST['mail'] !="" ) {echo ' value="'.$_POST['mail'].'"';} ?>></td>
	</tr>
<?php if (!(isset($_POST['submit']) && $err === true)) { // korrekte Eingabe: kein "Absenden"-Button anzeigen ?>
  <tr>
    <td bgcolor="#e7e7e7" align="center" colspan="2">
    <input type="submit" name="submit" value="Absenden" class="button">
    </td>
  </tr>
<? } // Ende korrekte Eingabe ?>
</table>
<p align=center> <img src=flags/Logo.gif> </p>
</form>

<?php
if (isset($_POST['submit']) && $err === true) {
  /*********************************
   * Alles korrekt eingegeben
   *********************************/
	$Befehl     = "SELECT EMail FROM user where user='admin'";
	$Ergebnis   = mysql_db_query ($dbName, $Befehl, $connect);
	$ausgabe    = mysql_fetch_array ($Ergebnis);
	$adminsmail = $ausgabe['EMail'];

	$Befehl2    = "SELECT `Datum`,`Anpfiff` FROM `spiel` ORDER BY Datum ASC, Anpfiff ASC LIMIT 1";
	$Ergebnis2  = mysql_db_query ($dbName, $Befehl2, $connect);
	$ausgabe2   = mysql_fetch_array ($Ergebnis2);
	$startDatum = $ausgabe2['Datum'];
  $startZeit  = $ausgabe2['Anpfiff'];	
	$start      = date_time_to_full_date($startDatum, $startZeit);

	// sp�tester Zeitpunkt zum Registrieren: Anpfiff - 1 Tag (also genau 24h vorher)
  $fullStartDatum = date_to_timestamp($startDatum. " ". $startZeit);
  $latestReg = add_date($fullStartDatum, 0, 0, -1, 0, 0, 0);
	$fullLatestReg = timestamp_to_full_date($latestReg);

  if ($testausgabe) {
		print "startDatum = ". $startDatum . "<br>";
		print "startZeit  = ". $startZeit . "<br>";
		
    print "fullStartDatum = ". $fullStartDatum. "<br>";
    print "latestReg = ". timestamp_to_full_date($fullStartDatum). "<br>";
		print "fullLatestReg = ". $fullLatestReg. "<br>";
	}
	
	$vorname	=$_POST[vorname];
	$name	    =$_POST[name];
	$mail		  =$_POST[mail];
	$username	=$_POST[username];
	$pass		  =$_POST[pass];
	
	/******************
	** URL ermittlen **
	******************/
	//URL zum LogIn zusammenfuegen fuer inhalt im mailversand
	$url  = "http://";
	$url .= $_SERVER['HTTP_HOST'];
	$url .= $_SERVER['PHP_SELF'];
	$weg = strrchr($url,"/"); //eigene PHP-Datei loeschen, damit auf index verwiesen wird
	$url = str_replace($weg,"",$url);
		
  
  $text="Neuer User will mitmachen \n
  	Vorname: $vorname \n
  	Name: $name \n
  	E-Mail: $mail \n
  	Username: $username \n
  	Passwort: $pass\n\n
	Nachdem Du Dich als  a d m i n  eingeloggt hast,\n
	(Hier geht's zum Login: $url)
	\n\n
	kannst Du zum Anlegen dieses Users auch einfach den folgenden Link anklicken:\n
	$url/neu.php?username=$username&password=$pass&password2=$pass&email=$mail&submit=Benutzer+anlegen
	\n 
 Andernfalls trage die oben angegebenen Daten in dem Formular \"Neuen User anlegen\" ein.";
   
  mail($adminsmail, "neuer user: $username", $text);
	
	/* FIXME!!! 
	   Hier die Admin-Daten aus der DB auslesen:
	     - Vorname + Name
		 - Betrag
		 - BLZ (opt.)
		 - KTN (opt.)
		 - sp�tester Anmeldezeitpunkt + Turnierbeginn
		Wenn Kontodaten nicht angegeben, Text unten anpassen: "..in bar geben."
	*/
		
	$text_user = "
Hallo $vorname,\n
um Deinen beTTer-Account freischalten zu lassen, musst Du dem Administrator noch
Deinen Einsatz zukommen lassen, und zwar bis zum %latest_reg%. 
Turnierbeginn ist am %turnier_start%. 

Du kannst ihm den Einsatz entweder in bar geben oder �berweisen an:\n
  Manuel Kleefu�
  BLZ: 67291700
  KTN: 25745701
  Betrag: 10 Euro
  Verwendungszweck: $username

Bei Fehlangaben bekommst Du Deinen Einsatz zur�ck, kannst aber nicht teilnehmen.	
Du kannst �ber folgende Adresse mit dem Administrator Kontakt aufnehmen:
$adminsmail

Viel Erfolg bei beTTer w�nscht Dir
Dein Admin ;)
";

  $text_user = str_replace("%turnier_start%", $start        , $text_user); // ersetzte %turnier_start%
  $text_user = str_replace("%latest_reg%"   , $fullLatestReg, $text_user); // ersetzte %latest_reg%	
  mail($mail, "Dein Einsatz", $text_user);
   
	print ("<h5>");
	echo '<br><p align="center">Formular wurde erfolgreich versandt!<br>';
	print("Der Adminstrator wird mit Dir ueber $_POST[mail] Kontakt aufnehmen<br><br>");
	print("</h5>");
	print("<br><br> <a href=\"index.php\">Zur�ck zur Startseite</a><br>");
}

?>
</body>
</html>
