<?php

// strings-se.php - Swedish strings and titles
// Translation by Patrik Grip-Jansson
// ------------------------------------------------------------------------
// Copyright (c) 2001 The phpBugTracker Group
// ------------------------------------------------------------------------
// This file is part of phpBugTracker
//
// phpBugTracker is free software; you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation; either version 2 of the License, or
// (at your option) any later version.
// 
// phpBugTracker is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
// 
// You should have received a copy of the GNU General Public License
// along with phpBugTracker; if not, write to the Free Software Foundation,
// Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
// ------------------------------------------------------------------------
// $Id: se.php,v 1.9 2002/02/28 17:31:45 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Anv�ndaren finns ej',
	'dupeofself' => 'Ett fel kan inte vara en kopia av sig sj�lv',
	'nobug' => 'Den buggen finns ej',
	'givesummary' => 'Skriv in en sammanfattning',
	'givedesc' => 'Skriv in en beskrivning',
	'noprojects' => 'Hittade inga projekt',
	'totalbugs' => 'Totalt antal buggar',
	'giveemail' => 'Skriv in en giltig e-postadress',
	'givelogin' => 'Please enter a login',
	'loginused' => 'Anv�ndarnamnet �r redan taget',
	'newacctsubject' => 'phpBugTracker inloggning',
	'newacctmessage' => "Ditt l�senord i phpBugTracker �r %s",
	'nobugs' => 'Inga buggar hittades',
	'givename' => 'Ditt namn',
	'edit' => 'Redigera',
	'addnew' => 'L�gg till ny',
	'nooses' => 'Inga OS hittades',
	'giveinitversion' => 'Skriv in ett f�rsta versions-ID f�r projektet',
	'giveversion' => 'Skriv in ett versions-ID',
	'noversions' => 'Inga versioner funna',
	'nocomponents' => 'Inga komponenter funna',
	'nostatuses' => 'Ingen status hittades',
	'noseverities' => 'No severities found',
	'givepassword' => 'Var god skriv in ditt l�senord',
	'nousers' => 'Inga anv�ndare hittades',
	'bugbadperm' => 'Du kan inte �ndra p� den h�r buggen',
	'bugbadnum' => 'Den buggen existerar inte',
	'datecollision' => 'N�gon har uppdaterat den buggen sen du tittade p� den. Bugginformationen har laddats om med de senaste �ndringarna',
	'passwordmatch' => 'L�senorden �r inte lika -- var god f�rs�k igen',
	'nobughistory' => 'Det finns ingen historik f�r den buggen',
	'logintomodify' => 'Du m�ste vara inloggad f�r att redigera buggen',
	'dupe_attachment' => 'Den bilagan finns redan med i buggen',
	'give_attachment' => 'Ange vilken fil du vill skicka in',
	'no_attachment_save_path' => 'Kunde inte hitta n�gon plats att lagra filen p�',
	'attachment_path_not_writeable' => 'Kunde inte spara filen i den angivna s�kv�gen f�r sparningar',
	'attachment_move_error' => 'Ett fel uppstod n�r den inskickade filen skulle flyttas',
	'bad_attachment' => 'Bilagan existerar inte',
	'attachment_too_large' => 'Filen du angav �r st�rre �n '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Du har inte den beh�vliga beh�righeten f�r att anv�nda den funktionen',
	'project_only_all_groups' => 'You cannot choose specific groups when "All Groups" is chosen',
	'previous_bug' => 'Previous',
	'next_bug' => 'Next',
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Rapportera bugg',
	'editbug' => 'Redigera bugg',
	'newaccount' => 'Skapa nytt konto',
	'bugquery' => 'Buggs�kning',
	'buglist' => 'Bugglista',
	'addcomponent' => 'L�gg till komponent',
	'editcomponent' => 'Redigera komponent',
	'addproject' => 'L�gg till projekt',
	'editproject' => 'Redigera projekt',
	'addversion' => 'L�gg till version',
	'editversion' => 'Redigera version',
	'project' => 'Projekt',
	'os' => 'Operativsystem',
	'resolution' => 'Uppf�ljning',
	'status' => 'Status',
	'severity' => 'Severity',
	'user' => 'Anv�ndare',
	'home' => 'Hem',
	'reporting' => 'Rapportering',
	'group' => 'Groups'
	);
	
?>
