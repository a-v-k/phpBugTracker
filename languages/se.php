<?php

// se.php - Swedish strings and titles
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
// $Id: se.php,v 1.13 2002/03/18 17:42:25 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Användaren finns ej',
	'dupeofself' => 'Ett fel kan inte vara en kopia av sig själv',
	'nobug' => 'Den buggen finns ej',
	'givesummary' => 'Skriv in en sammanfattning',
	'givedesc' => 'Skriv in en beskrivning',
	'noprojects' => 'Hittade inga projekt',
	'totalbugs' => 'Totalt antal buggar',
	'giveemail' => 'Skriv in en giltig e-postadress',
	'givelogin' => 'Ange användarnamn',
	'loginused' => 'Användarnamnet är redan taget',
	'newacctsubject' => 'phpBugTracker inloggning',
	'newacctmessage' => "Ditt lösenord i phpBugTracker är %s",
	'nobugs' => 'Inga buggar hittades',
	'givename' => 'Ditt namn',
	'edit' => 'Redigera',
	'addnew' => 'Lägg till ny',
	'nooses' => 'Inga OS hittades',
	'giveinitversion' => 'Skriv in ett första versions-ID för projektet',
	'giveversion' => 'Skriv in ett versions-ID',
	'noversions' => 'Inga versioner funna',
	'nocomponents' => 'Inga komponenter funna',
	'nostatuses' => 'Ingen status hittades',
	'noseverities' => 'Inga graderingar funna',
	'givepassword' => 'Skriv in ditt lösenord',
	'nousers' => 'Inga användare hittades',
	'bugbadperm' => 'Du kan inte ändra på den här buggen',
	'bugbadnum' => 'Den buggen existerar inte',
	'datecollision' => 'Någon har uppdaterat den buggen sen du tittade på den. Bugginformationen har laddats om med de senaste ändringarna',
	'passwordmatch' => 'Lösenorden är inte lika -- var god försök igen',
	'nobughistory' => 'Det finns ingen historik för den buggen',
	'logintomodify' => 'Du måste vara inloggad för att redigera buggen',
	'dupe_attachment' => 'Den bilagan finns redan med i buggen',
	'give_attachment' => 'Ange vilken fil du vill skicka in',
	'no_attachment_save_path' => 'Kunde inte hitta någon plats att lagra filen på',
	'attachment_path_not_writeable' => 'Kunde inte spara filen på platsen som angivits för fillagring',
	'attachment_move_error' => 'Ett fel uppstod när den inskickade filen skulle flyttas',
	'bad_attachment' => 'Bilagan existerar inte',
	'attachment_too_large' => 'Filen du angav är större än '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Du har inte behörighet för att använda den funktionen',
	'project_only_all_groups' => 'Du kan inte välja grupper när "Alla grupper" är vald',
	'previous_bug' => 'Föregående',
	'next_bug' => 'Nästa',
	'already_voted' => 'Du har redan röstat på den här buggen',
	'too_many_votes' => 'Du har överskridit maxantal röster per användare',
	'no_votes' => 'Det har inte lagts några röster på denna bugg',
	'user_filter' => array(
		0 => 'Alla användare',
		1 => 'Aktiva användare',
		2 => 'Inaktiva användare'),
	'dupe_dependency' => 'That bug dependency has already been added'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Rapportera bugg',
	'editbug' => 'Redigera bugg',
	'newaccount' => 'Skapa nytt konto',
	'bugquery' => 'Buggsökning',
	'buglist' => 'Bugglista',
	'addcomponent' => 'Lägg till komponent',
	'editcomponent' => 'Redigera komponent',
	'addproject' => 'Lägg till projekt',
	'editproject' => 'Redigera projekt',
	'addversion' => 'Lägg till version',
	'editversion' => 'Redigera version',
	'project' => 'Projekt',
	'os' => 'Operativsystem',
	'resolution' => 'Uppföljning',
	'status' => 'Status',
	'severity' => 'Gradering',
	'user' => 'Användare',
	'home' => 'Hem',
	'reporting' => 'Rapportering',
	'group' => 'Grupper'
	);
	
?>
