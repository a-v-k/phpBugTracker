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
// $Id: sl.php,v 1.1 2002/10/19 20:05:58 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso-8859-2',
	'nouser' => 'Uporabnik ne obstaja.',
	'dupeofself' => 'Hro�� ne more biti duplikat samega sebe.',
	'nobug' => 'Ta hro�� ne obstaja!',
	'givesummary' => 'Vnesite povzetek!',
	'givedesc' => 'Vnesite opis!',
	'noprojects' => 'Ni projektov!',
	'totalbugs' => 'Skupno �tevilo hro��ev',
	'giveemail' => 'Vnesite veljaven e-mail naslov',
	'givelogin' => 'Vnesite veljavno uporabni�ko ime',
	'loginused' => 'To uporabni�ko ime je �e v uporabi',
	'newacctsubject' => 'Prijava v phpBugTracker',
	'newacctmessage' => "Va�e phpBugTracker geslo je %s",
	'nobugs' => 'Najden ni bil noben hro��',
	'givename' => 'Vnesite ime',
	'edit' => 'Popravi',
	'addnew' => 'Dodaj novega',
	'nooses' => 'Operacijski sistemi niso specificirani!',
	'giveinitversion' => 'Vnesite za�etno verzijo projekta',
	'giveversion' => 'Vnesite verzijo',
	'noversions' => 'Ni verzij',
	'nocomponents' => 'Ni komponent',
	'nostatuses' => 'Ni statusov',
	'noseverities' => 'Ni resnosti',
	'givepassword' => 'Vnesite geslo',
	'nousers' => 'Ni uporabnikov',
	'bugbadperm' => 'Tega hro��a ne morete spremeniti',
	'bugbadnum' => 'Ta hro�� ne obstaja',
	'datecollision' => 'Nekdo je spemenil ta hro�� medtem ko ste ga gledali.	Prikazane informacije o hro��u odra�ajo zadnje spremembe.',
	'passwordmatch' => 'Gesli se ne ujemata. Poskusite znova!',
	'nobughistory' => 'Ta hro�� nima zgodovine',
	'logintomodify' => 'Za spreminjanje tega hro��a se morate prijaviti',
	'dupe_attachment' => 'Ta priponka k hro��u �e obstaja',
	'give_attachment' => 'Dolo�ite datoteko, ki jo �elite pripeti hro��u',
	'no_attachment_save_path' => 'Ne vem, kam shraniti priponko!',
	'attachment_path_not_writeable' => 'Nimam pravice shranjevati priponke!',
	'attachment_move_error' => 'Napaka pri premikanju priponke',
	'bad_attachment' => 'Ta priponka ne obstaja',
	'attachment_too_large' => 'Priponka je dalj�a od najdalj�e dopustne do�ine '.number_format(ATTACHMENT_MAX_SIZE).' bytov',
	'bad_permission' => 'Za to operacijo nimate dovoljenja',
	'project_only_all_groups' => 'Posameznih skupin ne morate dolo�ati kadar je v uporabi "Vse skupine"',
	'previous_bug' => 'Prej�nji',
	'next_bug' => 'Naslednji',
	'already_voted' => 'Za tega hro��a ste �e glasovali',
	'too_many_votes' => 'Dose�eno je bilo najve�je �tevilo glasov za uporabnika',
	'no_votes' => 'Za tega hro��a ni glasov',
	'user_filter' => array(
		0 => 'Vsi uporabniki',
		1 => 'Aktivni uporabniki',
		2 => 'Neaktivni uporabniki'),
	'dupe_dependency' => 'Odvisnost med hrosci je ze bila dodana',
	'image_path_not_writeable' => 'Ne morem pisati v imenik "jpgimages", zato slike s povzetkom ni bilo mogoce pripraviti'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Vnos hro��a',
	'editbug' => 'Spreminjanje hro��a',
	'newaccount' => 'Nov uporabnik',
	'bugquery' => 'Poizvedba po hro��ih',
	'buglist' => 'Seznam hro��ev',
	'addcomponent' => 'Dodajanje komponente',
	'editcomponent' => 'Spreminjanje komponente',
	'addproject' => 'Dodajanje projekta',
	'editproject' => 'Spreminjanje projekta',
	'addversion' => 'Dodajanje verzije',
	'editversion' => 'Spreminjanje verzije',
	'project' => 'Projekti',
	'os' => 'Operacijski sistemi',
	'resolution' => 'Razre�itve',
	'status' => 'Stanje',
	'severity' => 'Resnost',
	'user' => 'Uporabniki',
	'home' => 'Doma�a stran',
	'reporting' => 'Poro�anje',
	'group' => 'Skupine'
	);

?>
