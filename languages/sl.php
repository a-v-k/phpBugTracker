<?php

// sl.php - Slovenian strings and titles
// ------------------------------------------------------------------------
// Copyright (c) 2002 Klemen Zagar
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
// $Id: sl.php,v 1.2 2002/04/11 20:16:26 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso-8859-2',
	'nouser' => 'Uporabnik ne obstaja.',
	'dupeofself' => 'Hrošè ne more biti duplikat samega sebe.',
	'nobug' => 'Ta hrošè ne obstaja!',
	'givesummary' => 'Vnesite povzetek!',
	'givedesc' => 'Vnesite opis!',
	'noprojects' => 'Ni projektov!',
	'totalbugs' => 'Skupno število hrošèev',
	'giveemail' => 'Vnesite veljaven e-mail naslov',
	'givelogin' => 'Vnesite veljavno uporabniško ime',
	'loginused' => 'To uporabniško ime je že v uporabi',
	'newacctsubject' => 'Prijava v phpBugTracker',
	'newacctmessage' => "Vaše phpBugTracker geslo je %s",
	'nobugs' => 'Najden ni bil noben hrošè',
	'givename' => 'Vnesite ime',
	'edit' => 'Popravi',
	'addnew' => 'Dodaj novega',
	'nooses' => 'Operacijski sistemi niso specificirani!',
	'giveinitversion' => 'Vnesite zaèetno verzijo projekta',
	'giveversion' => 'Vnesite verzijo',
	'noversions' => 'Ni verzij',
	'nocomponents' => 'Ni komponent',
	'nostatuses' => 'Ni statusov',
	'noseverities' => 'Ni resnosti',
	'givepassword' => 'Vnesite geslo',
	'nousers' => 'Ni uporabnikov',
	'bugbadperm' => 'Tega hrošèa ne morete spremeniti',
	'bugbadnum' => 'Ta hrošè ne obstaja',
	'datecollision' => 'Nekdo je spemenil ta hrošè medtem ko ste ga gledali.	Prikazane informacije o hrošèu odražajo zadnje spremembe.',
	'passwordmatch' => 'Gesli se ne ujemata. Poskusite znova!',
	'nobughistory' => 'Ta hrošè nima zgodovine',
	'logintomodify' => 'Za spreminjanje tega hrošèa se morate prijaviti',
	'dupe_attachment' => 'Ta priponka k hrošèu že obstaja',
	'give_attachment' => 'Doloèite datoteko, ki jo želite pripeti hrošèu',
	'no_attachment_save_path' => 'Ne vem, kam shraniti priponko!',
	'attachment_path_not_writeable' => 'Nimam pravice shranjevati priponke!',
	'attachment_move_error' => 'Napaka pri premikanju priponke',
	'bad_attachment' => 'Ta priponka ne obstaja',
	'attachment_too_large' => 'Priponka je daljša od najdaljše dopustne dožine '.number_format(ATTACHMENT_MAX_SIZE).' bytov',
	'bad_permission' => 'Za to operacijo nimate dovoljenja',
	'project_only_all_groups' => 'Posameznih skupin ne morate doloèati kadar je v uporabi "Vse skupine"',
	'previous_bug' => 'Prejšnji',
	'next_bug' => 'Naslednji',
	'already_voted' => 'Za tega hrošèa ste že glasovali',
	'too_many_votes' => 'Doseženo je bilo najveèje število glasov za uporabnika',
	'no_votes' => 'Za tega hrošèa ni glasov',
	'user_filter' => array(
		0 => 'Vsi uporabniki',
		1 => 'Aktivni uporabniki',
		2 => 'Neaktivni uporabniki'),
	'dupe_dependency' => 'Odvisnost med hrosci je ze bila dodana',
	'image_path_not_writeable' => 'Ne morem pisati v imenik "jpgimages", zato slike s povzetkom ni bilo mogoce pripraviti'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Vnos hrošèa',
	'editbug' => 'Spreminjanje hrošèa',
	'newaccount' => 'Nov uporabnik',
	'bugquery' => 'Poizvedba po hrošèih',
	'buglist' => 'Seznam hrošèev',
	'addcomponent' => 'Dodajanje komponente',
	'editcomponent' => 'Spreminjanje komponente',
	'addproject' => 'Dodajanje projekta',
	'editproject' => 'Spreminjanje projekta',
	'addversion' => 'Dodajanje verzije',
	'editversion' => 'Spreminjanje verzije',
	'project' => 'Projekti',
	'os' => 'Operacijski sistemi',
	'resolution' => 'Razrešitve',
	'status' => 'Stanje',
	'severity' => 'Resnost',
	'user' => 'Uporabniki',
	'home' => 'Domaèa stran',
	'reporting' => 'Poroèanje',
	'group' => 'Skupine'
	);

?>
