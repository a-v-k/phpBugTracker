<?php

// strings-cz.php - Czech strings and titles
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
// $Id: cz.php,v 1.1 2002/03/12 20:59:07 bcurtis Exp $

$STRING = array(
        'lang_charset' => 'us-ascii',
        'nouser' => 'Tento uživatel neexistuje',
        'dupeofself' => 'BUG nemùže být shodný se sám se sebou',
        'nobug' => 'Tento BUG neexistuje',
        'givesummary' => 'Prosím vložte souhrn',
        'givedesc' => 'Prosím vložte popis',
        'noprojects' => 'Projekt nebyl nalezen',
        'totalbugs' => 'Celkovì chyb',
        'giveemail' => 'Prosím vložte správnou e-mailovou adresu',
        'givelogin' => 'prosím vložte login (uživatelské jméno)',
        'loginused' => 'Tento login již užívá nìkdo jiný',
        'newacctsubject' => 'phpBugTracker Pøihlášení',
        'newacctmessage' => "Váše phpBugTracker heslo je %s",
        'nobugs' => 'Nenalezeny žádné chyby',
        'givename' => 'Prosím vložte jméno',
        'edit' => 'Upravit',
        'addnew' => 'Pøidat',
        'nooses' => 'Nenalezen OS',
        'giveinitversion' => 'Prosím vložte poèáteèní verzi projektu ',
        'giveversion' => 'Prosím vložte verzi',
        'noversions' => 'Verze nenalezena',
        'nocomponents' => 'Komponenta nenalezena',
        'nostatuses' => 'Status nebyl nalezen',
        'noseverities' => 'Dùležitost nenalezena',
        'givepassword' => 'Prosím vložte heslo',
        'nousers' => 'Uživatel(é) nenalezen(i)',
        'bugbadperm' => 'Nemùžete zmìnit BUG',
        'bugbadnum' => 'Tenhle BUG neexistuje',
        'datecollision' => 'Nìkdo aktualizoval BUG od Vaší poslední návštìvy.        Do informací o BUGu byli zaneseny nejnovìjší zmìny',
        'passwordmatch' => 'Hesla nesouhlasí --prosím zadejte je znova',
        'nobughistory' => 'Tento BUG nemá hystorii',
        'logintomodify' => 'Musíte být zalogován pro upravu tohoto BUGu',
        'dupe_attachment' => 'Pøíloha pro tento BUG je již nahrána',
        'give_attachment' => 'Prosím vyberte soubor pro upload',
        'no_attachment_save_path' => 'Cesta k uloženému souboru nenalezena',
        'attachment_path_not_writeable' => 'Nelze vytvoøit soubor na zadané cestì',
        'attachment_move_error' => 'Nastala chyba pøi pøesouvání nahraného souboru',
        'bad_attachment' => 'Tato pøíloha neexistuje',
        'attachment_too_large' => 'Vámi specifikovaný soubor je vìtší než '.number_format(ATTACHMENT_MAX_SIZE).' bytù',
        'bad_permission' => 'Nemáte práva na požadovanou funkci',
        'project_only_all_groups' => 'Nelze vybrat specifickou skupoinu když jsou vybrány všechny skupiny',
        'previous_bug' => 'Pøedchozí',
        'next_bug' => 'Následující',
        'already_voted' => 'Již jste hlasoval k tomuto BUGu',
        'too_many_votes' => 'Dosáhl jste maximálního poètu hlasù na jednoho uživatele',
        'no_votes' => 'Nikdo ještì nehlasoval',
        'user_filter' => array(
                0 => 'Všichni uživatelé',
                1 => 'Aktivní uživatelé',
                2 => 'Neaktivní uživatelé')
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
        'resolution' => 'Rozlišení',
        'status' => 'Statusy',
        'severity' => 'Dùležitosti',
        'user' => 'Uživatelé',
        'home' => 'Domù',
        'reporting' => 'Hlášení',
        'group' => 'Skupiny'
        );

?>
