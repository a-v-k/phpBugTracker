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
// $Id: cz_iso-8859-2.php,v 1.8 2002/06/13 09:09:14 firma Exp $

$STRING = array(
	'lang_charset' => 'ISO-8859-2',
	'nouser' => 'Tento u¾ivatel neexistuje',
	'dupeofself' => 'BUG nemù¾e být shodný se sám se sebou',
	'nobug' => 'Tento BUG neexistuje',
	'givesummary' => 'Prosím vlo¾te souhrn',
	'givedesc' => 'Prosím vlo¾te popis',
	'noprojects' => 'Projekt nebyl nalezen',
	'totalbugs' => 'Celkovì chyb',
	'giveemail' => 'Prosím vlo¾te správnou e-mailovou adresu',
	'givelogin' => 'Prosím vlo¾te login (u¾ivatelské jméno)',
	'loginused' => 'Tento login ji¾ u¾ívá nìkdo jiný',
	'newacctsubject' => 'phpBugTracker Pøihlá¹ení',
	'newacctmessage' => "Va¹e phpBugTracker heslo je %s",
	'nobugs' => 'Nenalezeny ¾ádné chyby',
	'givename' => 'Prosím vlo¾te jméno',
	'edit' => 'Upravit',
	'addnew' => 'Pøidat',
	'nooses' => 'Nenalezen OS',
	'giveinitversion' => 'Prosím vlo¾te poèáteèní verzi projektu',
	'giveversion' => 'Prosím vlo¾te verzi',
	'noversions' => 'Verze nenalezena',
	'nocomponents' => 'Komponenta nenalezena',
	'nostatuses' => 'Status nebyl nalezen',
	'noseverities' => 'Dùle¾itost nenalezena',
	'givepassword' => 'Prosím vlo¾te heslo',
	'nousers' => 'U¾ivatel(é) nenalezen(i)',
	'bugbadperm' => 'Nemù¾ete zmìnit BUG',
	'bugbadnum' => 'Tento BUG neexistuje',
	'datecollision' => 'Nìkdo aktualizoval BUG od Va¹í poslední náv¹tìvy.	Do informací o BUGu byli zaneseny nejnovìj¹í zmìny.',
	'passwordmatch' => 'Hesla nesouhlasí --prosím zadejte je znovu',
	'nobughistory' => 'Tento BUG nemá hystorii',
	'logintomodify' => 'Musíte být pøihlá¹en(a) pro upravu tohoto BUGu',
	'dupe_attachment' => 'Pøíloha pro tento BUG je ji¾ nahrána',
	'give_attachment' => 'Prosím vyberte soubor pro upload',
	'no_attachment_save_path' => 'Cesta k ulo¾enému souboru nenalezena',
	'attachment_path_not_writeable' => 'Nelze vytvoøit soubor na zadané cestì',
	'attachment_move_error' => 'Nastala chyba pøi pøesouvání nahraného souboru',
	'bad_attachment' => 'Tato pøíloha neexistuje',
	'attachment_too_large' => 'Vámi specifikovaný soubor je vìt¹í ne¾ '.number_format(ATTACHMENT_MAX_SIZE).' bytù',
	'bad_permission' => 'Nemáte potøebná práva na po¾adovanou funkci',
	'project_only_all_groups' => 'Nelze vybrat specifickou skupinu kdy¾ jsou vybrány v¹echny skupiny',
	'previous_bug' => 'Pøedchozí',
	'next_bug' => 'Následující',
	'already_voted' => 'Ji¾ jste hlasoval(a) k tomuto BUGu',
	'too_many_votes' => 'Dosáhl jste maximálního poètu hlasù na jednoho u¾ivatele',
	'no_votes' => 'Nikdo je¹tì nehlasoval',
	'user_filter' => array(
		0 => 'V¹ichni u¾ivatelé',
		1 => 'Aktivní u¾ivatelé',
		2 => 'Neaktivní u¾ivatelé'),
	'dupe_dependency' => 'Tato závislost ji¾ byla pøidána',
	'image_path_not_writeable' => 'Webprocess nemù¾e zapisovat do podadresáøe "jpgimages", pøehledový obrázek nemù¾e být vygenerován.',
	'password_changed' => 'Va¹e heslo bylo zmìnìno',
	'prefs_changed' => 'Va¹e nastavení byla zmìnìna',
	'databaselist' => 'Seznam databází',
	'database' => 'Databáze',
	'databases' => 'Databáze',
	'name' => 'Název',
	'description' => 'Popis',
	'sortorder' => 'Poøadí tøídìní',
	'suredeletedb' => 'Opravdu chcete odstranit tuto databázi?',
	'delete' => 'Odstranit',
	'version' => 'Verze'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Vlo¾te BUG',
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
	'severity' => 'Dùle¾itosti',
	'user' => 'U¾ivatelé',
	'home' => 'Domù',
	'reporting' => 'Hlá¹ení',
	'group' => 'Skupiny',
	'bugvotes' => 'Volby bugù',
	'bughistory' => 'Historie bugù',
	'viewbug' => 'Prohlédnout bug',
	'addattachment' => 'Pøidat pøílohu',
	'accountcreated' => 'Úèet vytvoøen',
	'changessaved' => 'Zmìny ulo¾eny',
	'preferences' => 'U¾ivatelská nastavení',
	'usertools' => 'U¾ivatelské nástroje',
	'edituser' => 'Zmìnit u¾ivatele',
	'adduser' => 'Pøidat u¾ivatele',
	'editstatus' => 'Zmìnit status',
	'addstatus' => 'Pøidat status',
	'editseverity' => 'Zmìnit dùle¾itost',
	'addseverity' => 'Pøidat dùle¾itost',
	'editresolution' => 'Zmìnit rozhodnutí',
	'addresolution' => 'Pøidat rozhodnutí',
	'editos' => 'Zmìnit operaèní systém',
	'addos' => 'Pøidat operaèní systém',
	'editgroup' => 'Zmìnit skupinu',
	'addgroup' => 'Pøidat skupinu',
	'configuration' => 'Nastavení',
	'adddatabase' => 'Pøidat databázi',
	'editdatabase' => 'Upravit databázi',
	'database' => 'Databáze',
	'documentation' => 'Dokumentace',
	'_opt_All' => 'V¹e',
        '_Project' => 'Projekt',
        '_Version' => 'Verze',
        '_Component' => 'Komponenta',
        '_Status' => 'Status',
        '_SortBy' => 'Tøídit podle',
	    '_SortBy_BugNumber' => 'Èísla BUGu',
	    '_SortBy_Severity' => 'Dùle¾itosti',
	    '_SortBy_Reporter' => 'Reportéra',
	    '_SortBy_Status' => 'Statutu',
	    '_SortBy_Priority' => 'Priority',
	    '_SortAsc' => 'Vzestupnì',
	    '_SortDesc' => 'Sestupnì',
        '_SaveThisQueryAs' => 'Ulo¾t tento dotaz jako',
        '_ResetBackToDefaultQuery' => 'Zpìt k pùvodnímu dotazu',
        '_SubmitQuery' => 'Odeslat dotaz',
        '_SavedQueries' => 'Ulo¾ené dotazy',
	'_1GoToThe' => 'Pøejít na',
	'_1AdvancedQueryPage' => 'stránku pokroèilého vyhledávání',
	'_SureDeleteSavedQuery' => 'Jste si jisti, ¾e chcete smazat tento ulo¾ený dotaz?'
    );
?>
