<?php

// cz_iso-8859-2.php - Czech strings and titles encoded in iso-8859-2
// ------------------------------------------------------------------------
// Copyright (c) 2001, 2002 The phpBugTracker Group
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
// $Id: cz_iso-8859-2.php,v 1.3 2002/05/18 15:40:19 firma Exp $

$STRING = array(
	'lang_charset' => 'Windows-1250',
	'nouser' => 'Tento u�ivatel neexistuje',
	'dupeofself' => 'BUG nem��e b�t shodn� se s�m se sebou',
	'nobug' => 'Tento BUG neexistuje',
	'givesummary' => 'Pros�m vlo�te souhrn',
	'givedesc' => 'Pros�m vlo�te popis',
	'noprojects' => 'Projekt nebyl nalezen',
	'totalbugs' => 'Celkov� chyb',
	'giveemail' => 'Pros�m vlo�te spr�vnou e-mailovou adresu',
	'givelogin' => 'Pros�m vlo�te login (u�ivatelsk� jm�no)',
	'loginused' => 'Tento login ji� u��v� n�kdo jin�',
	'newacctsubject' => 'phpBugTracker P�ihl�en�',
	'newacctmessage' => "Va�e phpBugTracker heslo je %s",
	'nobugs' => 'Nenalezeny ��dn� chyby',
	'givename' => 'Pros�m vlo�te jm�no',
	'edit' => 'Upravit',
	'addnew' => 'P�idat',
	'nooses' => 'Nenalezen OS',
	'giveinitversion' => 'Pros�m vlo�te po��te�n� verzi projektu',
	'giveversion' => 'Pros�m vlo�te verzi',
	'noversions' => 'Verze nenalezena',
	'nocomponents' => 'Komponenta nenalezena',
	'nostatuses' => 'Status nebyl nalezen',
	'noseverities' => 'D�le�itost nenalezena',
	'givepassword' => 'Pros�m vlo�te heslo',
	'nousers' => 'U�ivatel(�) nenalezen(i)',
	'bugbadperm' => 'Nem��ete zm�nit BUG',
	'bugbadnum' => 'Tento BUG neexistuje',
	'datecollision' => 'N�kdo aktualizoval BUG od Va�� posledn� n�v�t�vy.	Do informac� o BUGu byli zaneseny nejnov�j�� zm�ny.',
	'passwordmatch' => 'Hesla nesouhlas� --pros�m zadejte je znovu',
	'nobughistory' => 'Tento BUG nem� hystorii',
	'logintomodify' => 'Mus�te b�t p�ihl�en(a) pro upravu tohoto BUGu',
	'dupe_attachment' => 'P��loha pro tento BUG je ji� nahr�na',
	'give_attachment' => 'Pros�m vyberte soubor pro upload',
	'no_attachment_save_path' => 'Cesta k ulo�en�mu souboru nenalezena',
	'attachment_path_not_writeable' => 'Nelze vytvo�it soubor na zadan� cest�',
	'attachment_move_error' => 'Nastala chyba p�i p�esouv�n� nahran�ho souboru',
	'bad_attachment' => 'Tato p��loha neexistuje',
	'attachment_too_large' => 'V�mi specifikovan� soubor je v�t�� ne� '.number_format(ATTACHMENT_MAX_SIZE).' byt�',
	'bad_permission' => 'Nem�te pot�ebn� pr�va na po�adovanou funkci',
	'project_only_all_groups' => 'Nelze vybrat specifickou skupinu kdy� jsou vybr�ny v�echny skupiny',
	'previous_bug' => 'P�edchoz�',
	'next_bug' => 'N�sleduj�c�',
	'already_voted' => 'Ji� jste hlasoval(a) k tomuto BUGu',
	'too_many_votes' => 'Dos�hl jste maxim�ln�ho po�tu hlas� na jednoho u�ivatele',
	'no_votes' => 'Nikdo je�t� nehlasoval',
	'user_filter' => array(
		0 => 'V�ichni u�ivatel�',
		1 => 'Aktivn� u�ivatel�',
		2 => 'Neaktivn� u�ivatel�'),
	'dupe_dependency' => 'Tato z�vislost ji� byla p�id�na',
	'image_path_not_writeable' => 'Webprocess nem��e zapisovat do podadres��e "jpgimages", p�ehledov� obr�zek nem��e b�t vygenerov�n.'
	'password_changed' => 'Va�e heslo bylo zm�n�no',
	'prefs_changed' => 'Va�e nastaven� byla zm�n�na',
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Vlo�te BUG',
	'editbug' => 'Upravte BUG',
	'newaccount' => 'Vytvo�it nov� ��et',
	'bugquery' => 'BUG dotaz',
	'buglist' => 'Seznam BUG�',
	'addcomponent' => 'P�idat komponentu',
	'editcomponent' => 'Editovat komponentu',
	'addproject' => 'P�idat projekt',
	'editproject' => 'Editovat projekt',
	'addversion' => 'P�idat verzi',
	'editversion' => 'Upravit verzi',
	'project' => 'Projekty',
	'os' => 'Opera�n� syst�my',
	'resolution' => 'Rozhodnut�',
	'status' => 'Statusy',
	'severity' => 'D�le�itosti',
	'user' => 'U�ivatel�',
	'home' => 'Dom�',
	'reporting' => 'Hl�en�',
	'group' => 'Skupiny'
	'bugvotes' => 'Volby bug�',
	'bughistory' => 'Historie bug�',
	'viewbug' => 'Prohl�dnout bug',
	'addattachment' => 'P�idat p��lohu',
	'accountcreated' => '��et vytvo�en',
	'changessaved' => 'Zm�ny ulo�eny',
	'preferences' => 'U�ivatelsk� nastaven�',
	'edituser' => 'Zm�nit U�ivatele',
	'adduser' => 'P�idat U�ivatele',
	'editstatus' => 'Zm�nit Status',
	'addstatus' => 'P�idat Status',
	'editseverity' => 'Zm�nit D�le�itost',
	'addseverity' => 'P�idat D�le�itost',
	'editresolution' => 'Zm�nit Rozhodnut�',
	'addresolution' => 'P�idat Rozhodnut�',
	'editos' => 'Zm�nit Opera�n� syst�m',
	'addos' => 'P�idat Opera�n� syst�m',
	'editgroup' => 'Zm�nit Skupinu',
	'addgroup' => 'P�idat Skupinu',
	'configuration' => 'Nastaven�',
	);
?>
