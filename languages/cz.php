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
        'nouser' => 'Tento u�ivatel neexistuje',
        'dupeofself' => 'BUG nem��e b�t shodn� se s�m se sebou',
        'nobug' => 'Tento BUG neexistuje',
        'givesummary' => 'Pros�m vlo�te souhrn',
        'givedesc' => 'Pros�m vlo�te popis',
        'noprojects' => 'Projekt nebyl nalezen',
        'totalbugs' => 'Celkov� chyb',
        'giveemail' => 'Pros�m vlo�te spr�vnou e-mailovou adresu',
        'givelogin' => 'pros�m vlo�te login (u�ivatelsk� jm�no)',
        'loginused' => 'Tento login ji� u��v� n�kdo jin�',
        'newacctsubject' => 'phpBugTracker P�ihl�en�',
        'newacctmessage' => "V�e phpBugTracker heslo je %s",
        'nobugs' => 'Nenalezeny ��dn� chyby',
        'givename' => 'Pros�m vlo�te jm�no',
        'edit' => 'Upravit',
        'addnew' => 'P�idat',
        'nooses' => 'Nenalezen OS',
        'giveinitversion' => 'Pros�m vlo�te po��te�n� verzi projektu ',
        'giveversion' => 'Pros�m vlo�te verzi',
        'noversions' => 'Verze nenalezena',
        'nocomponents' => 'Komponenta nenalezena',
        'nostatuses' => 'Status nebyl nalezen',
        'noseverities' => 'D�le�itost nenalezena',
        'givepassword' => 'Pros�m vlo�te heslo',
        'nousers' => 'U�ivatel(�) nenalezen(i)',
        'bugbadperm' => 'Nem��ete zm�nit BUG',
        'bugbadnum' => 'Tenhle BUG neexistuje',
        'datecollision' => 'N�kdo aktualizoval BUG od Va�� posledn� n�v�t�vy.        Do informac� o BUGu byli zaneseny nejnov�j�� zm�ny',
        'passwordmatch' => 'Hesla nesouhlas� --pros�m zadejte je znova',
        'nobughistory' => 'Tento BUG nem� hystorii',
        'logintomodify' => 'Mus�te b�t zalogov�n pro upravu tohoto BUGu',
        'dupe_attachment' => 'P��loha pro tento BUG je ji� nahr�na',
        'give_attachment' => 'Pros�m vyberte soubor pro upload',
        'no_attachment_save_path' => 'Cesta k ulo�en�mu souboru nenalezena',
        'attachment_path_not_writeable' => 'Nelze vytvo�it soubor na zadan� cest�',
        'attachment_move_error' => 'Nastala chyba p�i p�esouv�n� nahran�ho souboru',
        'bad_attachment' => 'Tato p��loha neexistuje',
        'attachment_too_large' => 'V�mi specifikovan� soubor je v�t�� ne� '.number_format(ATTACHMENT_MAX_SIZE).' byt�',
        'bad_permission' => 'Nem�te pr�va na po�adovanou funkci',
        'project_only_all_groups' => 'Nelze vybrat specifickou skupoinu kdy� jsou vybr�ny v�echny skupiny',
        'previous_bug' => 'P�edchoz�',
        'next_bug' => 'N�sleduj�c�',
        'already_voted' => 'Ji� jste hlasoval k tomuto BUGu',
        'too_many_votes' => 'Dos�hl jste maxim�ln�ho po�tu hlas� na jednoho u�ivatele',
        'no_votes' => 'Nikdo je�t� nehlasoval',
        'user_filter' => array(
                0 => 'V�ichni u�ivatel�',
                1 => 'Aktivn� u�ivatel�',
                2 => 'Neaktivn� u�ivatel�')
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
        'resolution' => 'Rozli�en�',
        'status' => 'Statusy',
        'severity' => 'D�le�itosti',
        'user' => 'U�ivatel�',
        'home' => 'Dom�',
        'reporting' => 'Hl�en�',
        'group' => 'Skupiny'
        );

?>
