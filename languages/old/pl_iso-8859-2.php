<?php

// pl_iso-8859-2.php - Polish strings and titles encoded in iso-8859-2 codepage
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
// $Id: pl_iso-8859-2.php,v 1.1 2002/10/19 20:05:58 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'ISO-8859-2',
	'nouser' => 'U�ytkownik nie istnieje',
	'dupeofself' => 'B��d nie mo�e by� duplikatem samego siebie',
	'nobug' => 'B��d nie istnieje',
	'givesummary' => 'Wpisz podsumowanie',
	'givedesc' => 'Wpisz opis',
	'noprojects' => 'Nie znaleziono projekt�w',
	'totalbugs' => 'Razem b��d�w',
	'giveemail' => 'Podaj prawid�owy adres email',
	'givelogin' => 'Podaj login',
	'loginused' => 'Ten login jest ju� u�ywany',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Twoje has�o do phpBugTracker\'a to %s",
	'nobugs' => 'Nie znaleziono �adnych b��d�w',
	'givename' => 'Podaj nazw�',
	'edit' => 'Edycja',
	'addnew' => 'Dodaj nowy',
	'nooses' => 'Nie znaleziono system�w operacyjnych',
	'giveinitversion' => 'Podaj inicjaln� wersj� dla projektu',
	'giveversion' => 'Podaj wersj�',
	'noversions' => 'Nie znaleziono wersji',
	'nocomponents' => 'Nie znaleziono komponent�w',
	'nostatuses' => 'Nie znaleziono status�w b��d�w',
	'noseverities' => 'Nie znaleziono wag b��d�w',
	'givepassword' => 'Podaj has�o',
	'nousers' => 'Nie znaleziono u�ytkownik�w',
	'bugbadperm' => 'Nie mo�esz zmieni� tego b��du',
	'bugbadnum' => 'B��d nie istnieje',
	'datecollision' => 'Kto� zaktualizowa� ten b��d od czasu gdy go ogl�dasz. Informacje o b��dzie zosta�y do�wie�one.',
	'passwordmatch' => 'Has�a nie zgadzaj� si� -- spr�buj ponownie',
	'nobughistory' => 'Ten b��d nie ma historii',
	'logintomodify' => 'Musisz by� zalogowany aby modyfikowa� ten b��d',
	'dupe_attachment' => 'Za��cznik jest ju� skojarzony z tym b��dem',
	'give_attachment' => 'Podaj nazw� pliku do za�adowania',
	'no_attachment_save_path' => 'Nie mog� znale�� �cie�ki do zachowania pliku!',
	'attachment_path_not_writeable' => 'Nie mog� stworzy� pliku w �cie�ce zachowywania plik�w!',
	'attachment_move_error' => 'W czasie przenoszenia wgranego pliku wyst�pi� b��d',
	'bad_attachment' => 'Za��cznik nie istnieje',
	'attachment_too_large' => 'Wybrany plik jest wi�kszy ni� '.number_format(ATTACHMENT_MAX_SIZE).' bajt�w',
	'bad_permission' => 'Nie masz uprawnie� wymaganych do tej funkcji',
	'project_only_all_groups' => 'Nie mo�esz zmienia� konkretnych grup kiedy wybrano "Wszystkie grupy"',
	'previous_bug' => 'Poprzedni',
	'next_bug' => 'Nast�pny',
	'already_voted' => 'G�osowa�e� ju� na ten b��d',
	'too_many_votes' => 'Osi�gn��e� maksymaln� liczb� g�os�w na przydzielon� u�ytkownikowi',
	'no_votes' => 'Na ten b��d nie oddano �adnych g�os�w',
	'user_filter' => array(
		0 => 'Wszyscy u�ytkownicy',
		1 => 'Aktywni u�ytkownicy',
		2 => 'Nieaktywni u�ytkownicy'),
	'dupe_dependency' => 'Ta zale�no�� pomi�dzy b��dami zosta�a ju� dodana',
	'image_path_not_writeable' => 'Podkatalog "jpgimages" nie mo�e by� zapisywany przez proces serwera WWW - nie mo�na wygenerowa� podsumowania'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Wpisz b��d',
	'editbug' => 'Edytuj b��d',
	'newaccount' => 'Stw�rz nowe konto u�ytkownika',
	'bugquery' => 'Przeszukiwanie bazy b��d�w',
	'buglist' => 'Lista b��d�w',
	'addcomponent' => 'Dodaj komponent',
	'editcomponent' => 'Edytuj komponent',
	'addproject' => 'Dodaj projekt',
	'editproject' => 'Edytuj projekt',
	'addversion' => 'Dodaj wersj�',
	'editversion' => 'Edytuj wersj�',
	'project' => 'Projekty',
	'os' => 'Systemy operacyjne',
	'resolution' => 'Rozpoznanie',
	'status' => 'Statusy',
	'severity' => 'Wagi',
	'user' => 'U�ytkownicy',
	'home' => 'Strona g��wna',
	'reporting' => 'Raportowanie',
	'group' => 'Grupy'
	);
	
?>
