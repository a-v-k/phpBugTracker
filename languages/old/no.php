<?php

// no.php - Norwegian strings and titles
// Translation by Sven-Erik Andersen
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
// $Id: no.php,v 1.1 2002/10/19 20:05:58 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Denne brukeren eksisterer ikke',
	'dupeofself' => 'En feil kan ikke v�re en kopi av seg selv',
	'nobug' => 'Den feilen eksisterer ikke',
	'givesummary' => 'Vennligst skriv et sammendrag',
	'givedesc' => 'Vennligst skriv en beskrivelse',
	'noprojects' => 'Ingen prosjekter funnet',
	'totalbugs' => 'Totalt antall feil',
	'giveemail' => 'Vennligst skriv en gyldig email adresse',
	'givelogin' => 'Vennligst skriv inn et brukernavn',
	'loginused' => 'Det brukernavnet er allerede i bruk',
	'newacctsubject' => 'phpBugTracker Innlogging',
	'newacctmessage' => "Ditt phpBugTracker passord er %s",
	'nobugs' => 'Ingen feiler funnet',
	'givename' => 'Vennligst skriv et navn',
	'edit' => 'Rediger',
	'addnew' => 'Legg til ny',
	'nooses' => 'Ingen OSer funnet',
	'giveinitversion' => 'Vennligst skriv et versjonsnummer for prosjektet',
	'giveversion' => 'Vennligst skriv et versjonsnummer',
	'noversions' => 'Ingen versjoner funnet',
	'nocomponents' => 'Ingen komponenter funnet',
	'nostatses' => 'Ingen statuser funnet',
	'givepassword' => 'Vennligst skriv inn et passord',
	'nousers' => 'Ingen brukere funnet',
	'bugbadperm' => 'Du kan ikke endre denne feilen',
	'bugbadnum' => 'Denne feilen eksisterer ikke',
	'datecollision' => 'Noen har oppdatert feilen siden sist du s� den. Feil informasjonen har blitt lastet p� nytt med de siste endringer.',
	'passwordmatch' => 'Disse passordene er ikke like -- vennligst pr�v igjen',
	'nobughistory' => 'Det er ingen historie for denne feilen',
	'logintomodify' => 'Du m� v�re logget inn for � redigere denne feilen',
	'dupe_attachment' => 'Vedlegget eksisterer allerede for denne feilen',
	'give_attachment' => 'Vennligst spesifiser en fil for opplasting',
	'no_attachment_save_path' => 'Kunne ikke finne en plass � lagre denne feilen!',
	'attachment_path_not_writeable' => 'Kunne ikke opprette en fil i lagrings-stien',
	'attachment_move_error' => 'Det oppstod en feil under flytting av den opplastede filen',
	'bad_attachment' => 'Det vedlegget eksisterer ikke',
	'attachment_too_large' => 'Filen du har spesifisert er st�rre enn '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Du har ikke de n�dvendige rettighetene til det',
	'noseverities' => 'Ingen alvorligheter funnet',
	'project_only_all_groups' => 'You cannot choose specific groups when "All Groups" is chosen',
	'previous_bug' => 'Previous',
	'next_bug' => 'Next',
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user',
	'no_votes' => 'There are no votes for this bug',
	'user_filter' => array(
		0 => 'All users',
		1 => 'Active users',
		2 => 'Inactive users'),
	'dupe_dependency' => 'That bug dependency has already been added',
	'image_path_not_writeable' => 'The subdirectory "jpgimages" is not writeable by the web process, so the summary image can not be rendered'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Legg til en feil',
	'editbug' => 'Rediger feil',
	'newaccount' => 'Lag en ny konto',
	'bugquery' => 'Feil sp�rring',
	'buglist' => 'Feil-liste',
	'addcomponent' => 'Legg til komponent',
	'editcomponent' => 'Rediger komponent',
	'addproject' => 'Legg til prosjekt',
	'editproject' => 'Rediger prosjekt',
	'addversion' => 'Legg til versjon',
	'editversion' => 'Rediger versjon',
	'project' => 'Prosjekter',
	'os' => 'Operativ Systemer',
	'resolution' => 'Oppl�sninger',
	'status' => 'Statuser',
	'user' => 'Brukere',
	'home' => 'Hjem',
	'reporting' => 'Rapportering',
	'severity' => 'Alvorlighet',
	'group' => 'Groups'
	);
	
?>
