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
	'nouser' => 'Användaren finns ej',
	'dupeofself' => 'Ett fel kan inte vara en kopia av sig själv',
	'nobug' => 'Den buggen finns ej',
	'givesummary' => 'Skriv in en sammanfattning',
	'givedesc' => 'Skriv in en beskrivning',
	'noprojects' => 'Hittade inga projekt',
	'totalbugs' => 'Totalt antal buggar',
	'giveemail' => 'Skriv in en giltig e-postadress',
	'givelogin' => 'Please enter a login',
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
	'noseverities' => 'No severities found',
	'givepassword' => 'Var god skriv in ditt lösenord',
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
	'attachment_path_not_writeable' => 'Kunde inte spara filen i den angivna sökvägen för sparningar',
	'attachment_move_error' => 'Ett fel uppstod när den inskickade filen skulle flyttas',
	'bad_attachment' => 'Bilagan existerar inte',
	'attachment_too_large' => 'Filen du angav är större än '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Du har inte den behövliga behörigheten för att använda den funktionen',
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
	'severity' => 'Severity',
	'user' => 'Användare',
	'home' => 'Hem',
	'reporting' => 'Rapportering',
	'group' => 'Groups'
	);
	
?>
