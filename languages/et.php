<?php

// et.php - Estonian strings and titles
// ------------------------------------------------------------------------
// Copyright (c) 2002 Alvar Soome (FinSoft) <alvar@webmedia.ee>
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
// $Id: et.php,v 1.2 2002/03/28 15:05:44 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso-8859-1',
	'nouser' => 'Kasutajat ei eksisteeri',
	'dupeofself' => 'Viga ei saa olla iseenda dublikaat',
	'nobug' => 'Viga ei eksisteeri',
	'givesummary' => 'Sisestage kokkuvõte',
	'givedesc' => 'Sisestage kirjeldus',
	'noprojects' => 'Projekte ei leitud',
	'totalbugs' => 'Kokku vigu',
	'giveemail' => 'Palun sisestage õige emaili aadress',
	'givelogin' => 'Andke kasutajanimi',
	'loginused' => 'See kasutajanimi on juba olemas',
	'newacctsubject' => 'phpBugTrackeri sisselogimine',
	'newacctmessage' => "Teie phpBugTrackeri parool on %s",
	'nobugs' => 'Vigu ei leitud',
	'givename' => 'Palun sisestage nimi',
	'edit' => 'Muuda',
	'addnew' => 'Lisa uus',
	'nooses' => 'Ei leitud OP süsteeme',
	'giveinitversion' => 'Sisestage projekti hekteversioon',
	'giveversion' => 'Sisestage versioon',
	'noversions' => 'Versioone ei leitud',
	'nocomponents' => 'Komponente ei leitud',
	'nostatuses' => 'Staatuseid ei leitud',
	'noseverities' => 'Raskusaste',
	'givepassword' => 'Sisestage parool',
	'nousers' => 'Kasutajaid ei leitud',
	'bugbadperm' => 'Te ei saa seda viga muuta',
	'bugbadnum' => 'Seda viga ei eksisteeri',
	'datecollision' => 'Keegi muutis seda viga samal ajal kui Teie seda vaatasite.	Vea info on uuesti sisse loetud koos uute muudatustega.',
	'passwordmatch' => 'Parooli ei ühti, sisestage uuesti',
	'nobughistory' => 'Selle vea kohta pole ajalugu',
	'logintomodify' => 'Te peate sisse logitud olema, et viga muuta',
	'dupe_attachment' => 'Selline lisa on juba veal olemas',
	'give_attachment' => 'Määrake fail üleslaadimiseks',
	'no_attachment_save_path' => 'Ei leitud kataloogi kuhu fail salvestada!',
	'attachment_path_not_writeable' => 'Ei suutnud määratud kataloogi faili salvestada',
	'attachment_move_error' => 'Tekkis viga lisa liigutamisel',
	'bad_attachment' => 'Seda lisa ei eksisteeri',
	'attachment_too_large' => 'Teie antud fail on suurem kui '.number_format(ATTACHMENT_MAX_SIZE).' baiti',
	'bad_permission' => 'Teil pole selleks tegevuseks vajalikke õigusi',
	'project_only_all_groups' => 'Te ei saa valida eraldi gruppe kui "Kõik grupid" on valitud',
	'previous_bug' => 'Eelmine',
	'next_bug' => 'Järgmine',
	'already_voted' => 'Te olete juba hääletanud',
	'too_many_votes' => 'Te olete ületanud maksimaalse häälte arvu kasutaja kohta',
	'no_votes' => 'Sellel veal ei ole hääli',
	'user_filter' => array(
		0 => 'Kõik kasutajad',
		1 => 'Aktiivsed kasutajad',
		2 => 'Suletud/mitteaktiivsed kasutajad'),
	'dupe_dependency' => 'Selle vea sõltuvus on juba paika seatud',
	'image_path_not_writeable' => 'Alamkataloog "jpgimages" ei ole üle võrgu kirjutatav,  seega ei suuda kokkuvõtte pilti renderdada'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Lisa viga',
	'editbug' => 'Muuda viga',
	'newaccount' => 'Loo uus kasutaja',
	'bugquery' => 'Veapäring',
	'buglist' => 'Veanimekiri',
	'addcomponent' => 'Lisa komponent',
	'editcomponent' => 'Muuda komponenti',
	'addproject' => 'Lisa projekt',
	'editproject' => 'Muuda projekti',
	'addversion' => 'Lisa versioon',
	'editversion' => 'Muuda versiooni',
	'project' => 'Projektid',
	'os' => 'Operatsioonisüsteemid',
	'resolution' => 'Resolutsioonid',
	'status' => 'Staatused',
	'severity' => 'Raskusastmed',
	'user' => 'Kasutajad',
	'home' => 'Esileht',
	'reporting' => 'Raport',
	'group' => 'Grupid'
	);
	
?>
