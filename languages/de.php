<?php

// strings-de.php - Deutsche Strings und Titel
// Translation by Stefan Plank
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ------------------------------------------------------------------------
// Diese Datei ist Teil des phpBugTracker
// 
// phpBugTracker ist freie Software; Sie koennen sie weiterverteilen
// und/oder veraendern unter den Bedingungen der GNU General Public License,
// publiziert durch die Free Software Foundation; entweder nach Version 2
// der Lizenz, oder (ihrer Wahl nach) irgendeiner spaeterern Version.
//
// phpBugTracker wird in der Hoffnung verteilt, dasz es nuetzlich ist, aber 
// OHNE JEDE GARANTIE; selbst ohne die eingeschloszene Garantie der 
// VERMARKTBARKEIT [MERCHANTIBILITY] oder EIGNUNG FUER
// EINEN PARTIKULAEREN GEBRAUCH. Sie koennen mehr Details in der 
// GNU General Public License nachlesen.
//
// Sie sollten mit dem phpBugTracker eine Kopie der
// GNU General Public License bekommen haben; wenn nicht, schreiben Sie der
// Free Software Foundation, Inc., 59 Temple Place - Suite 330, Boston,
// MA 02111-1307, USA.
// ------------------------------------------------------------------------- 
// $Id: de.php,v 1.6 2001/12/04 14:32:23 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Dieser Benutzer extistiert nicht',
	'dupeofself' => 'Ein Bug kann kein Duplikat von sich selbst sein',
	'nobug' => 'Dieser Bug existiert nicht',
	'givesummary' => 'Bitte geben Sie einen Bericht ein',
	'givedesc' => 'Bitte geben Sie eine Beschreibung ein',
	'noprojects' => 'Keine Projekte gefunden',
	'totalbugs' => 'Total Bugs',
	'giveemail' => 'Bitte geben Sie eine g�ltige E-mail Adresse ein.',
	'givelogin' => 'Please enter a login',
	'loginused' => 'Dieser Benutzername ist schon in Gebrauch',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Ihr phpBugTracker Passwort ist %s",
	'nobugs' => 'Kein Bug gefunden',
	'givename' => 'Bitte geben Sie einen Namen ein',
	'edit' => 'Bearbeiten',
	'addnew' => 'Neuen hinzuf�gen',
	'nooses' => 'Keine Betriebsysteme gefunden',
	'giveinitversion' => 'Bitte geben Sie eine Initialversion f�r das Projekt an',
	'giveversion' => 'Bitte geben Sie eine Version ein',
	'noversions' => 'Keine Version gefunden',
	'nocomponents' => 'Keine Komponenten gefunden',
	'nostatuses' => 'Keine Stati gefunden',
	'noseverities' => 'No severities found',
	'givepassword' => 'Bitte geben Sie ein Passwort ein!',
	'nousers' => 'Keinen Benutzer gefunden',
	'bugbadperm' => 'Sie k�nnen diesen Bug nicht �ndern',
	'bugbadnum' => 'Dieser Bug existiert nicht',
	'datecollision' => 'Jemand hat diesen Bug behoben seit Sie ihn gesehen haben. Die Buginformation wurde mit den letzten �nderunen erneut geladen.',
	'passwordmatch' => 'Dieses Passw�rter stimmen nicht -- Bitte probieren Sie noch einmal',
	'nobughistory' => 'Es gibt keine History f�r diesen Bug',
	'logintomodify' => 'Sie m�ssen eingeloggt sein, um diesen Bug zu �ndern.',
	'dupe_attachment' => 'Dieser Anhang existiert bereits f�r den Bug.',
	'give_attachment' => 'Bitte geben Sie eine Datei f�r den Upload an.',
	'no_attachment_save_path' => 'Konnte den Pfad zum Speichern nicht finden!',
	'attachment_path_not_writeable' => 'Konnte keine Datei im Speicherpfad erstellen',
	'attachment_move_error' => 'Es gab einen Fehler beim Bewegen der upzuloadenen Datei',
	'bad_attachment' => 'Dieser Dateianhang existiert nicht',
	'attachment_too_large' => 'Die angegebene Datei ist gr��er als '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Sie haben nicht die erforderlichen Rechte f�r diese Funktion'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Geben Sie einen Fehler ein',
	'editbug' => 'Fehler Bearbeiten',
	'newaccount' => 'Neuen Account anlegen',
	'bugquery' => 'Fehlersuche Bug Query',
	'buglist' => 'Fehlerliste',
	'addcomponent' => 'Komponente hinzuf�gen',
	'editcomponent' => 'Komponente bearbeiten',
	'addproject' => 'Projekt hinzuf�gen',
	'editproject' => 'Projekt bearbeiten',
	'addversion' => 'Version hinzuf�gen',
	'editversion' => 'Version bearbeiten',
	'project' => 'Projekte',
	'os' => 'Betriebssysteme',
	'resolution' => 'Aufl�sungen',
	'status' => 'Stati',
	'severity' => 'Severity',
	'user' => 'Benutzer',
	'home' => 'Home',
	'reporting' => 'Bericht',
	'group' => 'Groups'
	);
	
?>
