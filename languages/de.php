<?php

// strings-de.php - Deutsche Strings und Titel
// Translation by Stefan Plank & Stefan Kunstmann
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
// $Id: de.php,v 1.14 2002/04/11 15:25:13 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Dieser Benutzer extistiert nicht',
	'dupeofself' => 'Ein Bug kann kein Duplikat von sich selbst sein',
	'nobug' => 'Dieser Bug existiert nicht',
	'givesummary' => 'Bitte geben Sie einen Titel ein',
	'givedesc' => 'Bitte geben Sie eine Beschreibung ein',
	'noprojects' => 'Keine Projekte gefunden',
	'totalbugs' => 'Bugs gesamt',
	'giveemail' => 'Bitte geben Sie eine gültige E-mail Adresse ein.',
	'givelogin' => 'Bitte geben Sie einen Login an',
	'loginused' => 'Dieser Benutzername ist bereits in Gebrauch',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Ihr phpBugTracker Passwort ist %s",
	'nobugs' => 'Kein Bug gefunden',
	'givename' => 'Bitte geben Sie einen Namen ein',
	'edit' => 'Bearbeiten',
	'addnew' => 'Neu hinzufügen: ',
	'nooses' => 'Keine Betriebsysteme gefunden',
	'giveinitversion' => 'Bitte geben Sie eine Initialversion für das Projekt an',
	'giveversion' => 'Bitte geben Sie eine Version ein',
	'noversions' => 'Keine Version gefunden',
	'nocomponents' => 'Keine Komponenten gefunden',
	'nostatuses' => 'Keine Stati gefunden',
	'noseverities' => 'Keine Schwierigkeitsgrade gefunden',
	'givepassword' => 'Bitte geben Sie ein Passwort ein!',
	'nousers' => 'Keinen Benutzer gefunden',
	'bugbadperm' => 'Sie können diesen Bug nicht ändern',
	'bugbadnum' => 'Dieser Bug existiert nicht',
	'datecollision' => 'Jemand hat diesen Bug behoben seit Sie ihn gesehen haben. Die Buginformation wurde mit den letzten Änderungen erneut geladen.',
	'passwordmatch' => 'Diese Passwörter stimmen nicht -- Bitte probieren Sie es noch einmal',
	'nobughistory' => 'Es gibt keine Historie für diesen Bug',
	'logintomodify' => 'Sie müssen eingeloggt sein, um diesen Bug zu ändern.',
	'dupe_attachment' => 'Dieser Anhang existiert bereits für den Bug.',
	'give_attachment' => 'Bitte geben Sie eine Datei für den Upload an.',
	'no_attachment_save_path' => 'Konnte den Pfad zum Speichern nicht finden!',
	'attachment_path_not_writeable' => 'Konnte keine Datei im Speicherpfad erstellen',
	'attachment_move_error' => 'Es gab einen Fehler beim Bewegen der upzuloadenen Datei',
	'bad_attachment' => 'Dieser Dateianhang existiert nicht',
	'attachment_too_large' => 'Die angegebene Datei ist größer als '.number_format(ATTACHMENT_MAX_SIZE).' byte',
	'bad_permission' => 'Sie haben nicht die erforderlichen Rechte für diese Funktion',
	'project_only_all_groups' => 'Sie können keine spezifischen Gruppen angeben, wenn ALLE gewählt ist!',
	'previous_bug' => 'Letzter',
	'next_bug' => 'Nächster',
	'already_voted' => 'Sie haben für diesen Bug bereits gestimmt',
	'too_many_votes' => 'Die maximale Stimmzahl wurde bereits erreicht',
	'no_votes' => 'Für diesen Bug existieren keine Stimmen',
	'user_filter' => array(
		0 => 'Alle User',
		1 => 'Aktive User',
		2 => 'Inaktive User'),
	'dupe_dependency' => 'That bug dependency has already been added',
	'image_path_not_writeable' => 'The subdirectory "jpgimages" is not writeable by the web process, so the summary image can not be rendered'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Geben Sie einen Bug ein',
	'editbug' => 'Bug Bearbeiten',
	'newaccount' => 'Einen neuen Account anlegen',
	'bugquery' => 'Bugsuche',
	'buglist' => 'Bugliste',
	'addcomponent' => 'Komponente hinzufügen',
	'editcomponent' => 'Komponente bearbeiten',
	'addproject' => 'Projekt hinzufügen',
	'editproject' => 'Projekt bearbeiten',
	'addversion' => 'Version hinzufügen',
	'editversion' => 'Version bearbeiten',
	'project' => 'Projekte',
	'os' => 'Betriebssysteme',
	'resolution' => 'Lösungen',
	'status' => 'Stati',
	'severity' => 'Schwierigkeitsgrad',
	'user' => 'Benutzer',
	'home' => 'Home',
	'reporting' => 'Bericht',
	'group' => 'Gruppen'
	);
	
?>
