<?php

// cz.php - Czech strings and titles encoded in Windows-1250 codepage
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
// $Id: cz.php,v 1.8 2002/05/19 12:06:56 firma Exp $

$STRING = array(
	'lang_charset' => 'Windows-1250',
	'nouser' => 'Tento uživatel neexistuje',
	'dupeofself' => 'BUG nemùže být shodný se sám se sebou',
	'nobug' => 'Tento BUG neexistuje',
	'givesummary' => 'Prosím vložte souhrn',
	'givedesc' => 'Prosím vložte popis',
	'noprojects' => 'Projekt nebyl nalezen',
	'totalbugs' => 'Celkovì chyb',
	'giveemail' => 'Prosím vložte správnou e-mailovou adresu',
	'givelogin' => 'Prosím vložte login (uživatelské jméno)',
	'loginused' => 'Tento login již užívá nìkdo jiný',
	'newacctsubject' => 'phpBugTracker Pøihlášení',
	'newacctmessage' => "Vaše phpBugTracker heslo je %s",
	'nobugs' => 'Nenalezeny žádné chyby',
	'givename' => 'Prosím vložte jméno',
	'edit' => 'Upravit',
	'addnew' => 'Pøidat',
	'nooses' => 'Nenalezen OS',
	'giveinitversion' => 'Prosím vložte poèáteèní verzi projektu',
	'giveversion' => 'Prosím vložte verzi',
	'noversions' => 'Verze nenalezena',
	'nocomponents' => 'Komponenta nenalezena',
	'nostatuses' => 'Status nebyl nalezen',
	'noseverities' => 'Dùležitost nenalezena',
	'givepassword' => 'Prosím vložte heslo',
	'nousers' => 'Uživatel(é) nenalezen(i)',
	'bugbadperm' => 'Nemùžete zmìnit BUG',
	'bugbadnum' => 'Tento BUG neexistuje',
	'datecollision' => 'Nìkdo aktualizoval BUG od Vaší poslední návštìvy.	Do informací o BUGu byli zaneseny nejnovìjší zmìny.',
	'passwordmatch' => 'Hesla nesouhlasí --prosím zadejte je znovu',
	'nobughistory' => 'Tento BUG nemá hystorii',
	'logintomodify' => 'Musíte být pøihlášen(a) pro upravu tohoto BUGu',
	'dupe_attachment' => 'Pøíloha pro tento BUG je již nahrána',
	'give_attachment' => 'Prosím vyberte soubor pro upload',
	'no_attachment_save_path' => 'Cesta k uloženému souboru nenalezena',
	'attachment_path_not_writeable' => 'Nelze vytvoøit soubor na zadané cestì',
	'attachment_move_error' => 'Nastala chyba pøi pøesouvání nahraného souboru',
	'bad_attachment' => 'Tato pøíloha neexistuje',
	'attachment_too_large' => 'Vámi specifikovaný soubor je vìtší než '.number_format(ATTACHMENT_MAX_SIZE).' bytù',
	'bad_permission' => 'Nemáte potøebná práva na požadovanou funkci',
	'project_only_all_groups' => 'Nelze vybrat specifickou skupinu když jsou vybrány všechny skupiny',
	'previous_bug' => 'Pøedchozí',
	'next_bug' => 'Následující',
	'already_voted' => 'Již jste hlasoval(a) k tomuto BUGu',
	'too_many_votes' => 'Dosáhl jste maximálního poètu hlasù na jednoho uživatele',
	'no_votes' => 'Nikdo ještì nehlasoval',
	'user_filter' => array(
		0 => 'Všichni uživatelé',
		1 => 'Aktivní uživatelé',
		2 => 'Neaktivní uživatelé'),
	'dupe_dependency' => 'Tato závislost již byla pøidána',
	'image_path_not_writeable' => 'Webprocess nemùže zapisovat do podadresáøe "jpgimages", pøehledový obrázek nemùže být vygenerován.',
	'password_changed' => 'Vaše heslo bylo zmìnìno',
	'prefs_changed' => 'Vaše nastavení byla zmìnìna',
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Vložte BUG',
	'editbug' => 'Upravte BUG',
	'newaccount' => 'Vytvoøit nový úèet',
	'bugquery' => 'BUG dotaz',
	'buglist' => 'Seznam BUGù',
	'addcomponent' => 'Pøidat komponentu',
	'editcomponent' => 'Editovat komponentu',
	'addproject' => 'Pøidat projekt',
	'editproject' => 'Editovat projekt',
	'addversion' => 'Pøidat verzi',
	'editversion' => 'Upravit verzi',
	'project' => 'Projekty',
	'os' => 'Operaèní systémy',
	'resolution' => 'Rozhodnutí',
	'status' => 'Statusy',
	'severity' => 'Dùležitosti',
	'user' => 'Uživatelé',
	'home' => 'Domù',
	'reporting' => 'Hlášení',
	'group' => 'Skupiny',
	'bugvotes' => 'Volby bugù',
	'bughistory' => 'Historie bugù',
	'viewbug' => 'Prohlédnout bug',
	'addattachment' => 'Pøidat pøílohu',
	'accountcreated' => 'Úèet vytvoøen',
	'changessaved' => 'Zmìny uloženy',
	'preferences' => 'Uživatelská nastavení',
	'edituser' => 'Zmìnit Uživatele',
	'adduser' => 'Pøidat Uživatele',
	'editstatus' => 'Zmìnit Status',
	'addstatus' => 'Pøidat Status',
	'editseverity' => 'Zmìnit Dùležitost',
	'addseverity' => 'Pøidat Dùležitost',
	'editresolution' => 'Zmìnit Rozhodnutí',
	'addresolution' => 'Pøidat Rozhodnutí',
	'editos' => 'Zmìnit Operaèní systém',
	'addos' => 'Pøidat Operaèní systém',
	'editgroup' => 'Zmìnit Skupinu',
	'addgroup' => 'Pøidat Skupinu',
	'configuration' => 'Nastavení',
	);
?>
