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
// $Id: pl_iso-8859-2.php,v 1.1 2002/07/29 12:11:56 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'ISO-8859-2',
	'nouser' => 'U¿ytkownik nie istnieje',
	'dupeofself' => 'B³±d nie mo¿e byæ duplikatem samego siebie',
	'nobug' => 'B³±d nie istnieje',
	'givesummary' => 'Wpisz podsumowanie',
	'givedesc' => 'Wpisz opis',
	'noprojects' => 'Nie znaleziono projektów',
	'totalbugs' => 'Razem b³êdów',
	'giveemail' => 'Podaj prawid³owy adres email',
	'givelogin' => 'Podaj login',
	'loginused' => 'Ten login jest ju¿ u¿ywany',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Twoje has³o do phpBugTracker\'a to %s",
	'nobugs' => 'Nie znaleziono ¿adnych b³êdów',
	'givename' => 'Podaj nazwê',
	'edit' => 'Edycja',
	'addnew' => 'Dodaj nowy',
	'nooses' => 'Nie znaleziono systemów operacyjnych',
	'giveinitversion' => 'Podaj inicjaln± wersjê dla projektu',
	'giveversion' => 'Podaj wersjê',
	'noversions' => 'Nie znaleziono wersji',
	'nocomponents' => 'Nie znaleziono komponentów',
	'nostatuses' => 'Nie znaleziono statusów b³êdów',
	'noseverities' => 'Nie znaleziono wag b³êdów',
	'givepassword' => 'Podaj has³o',
	'nousers' => 'Nie znaleziono u¿ytkowników',
	'bugbadperm' => 'Nie mo¿esz zmieniæ tego b³êdu',
	'bugbadnum' => 'B³±d nie istnieje',
	'datecollision' => 'Kto¶ zaktualizowa³ ten b³±d od czasu gdy go ogl±dasz. Informacje o b³êdzie zosta³y do¶wie¿one.',
	'passwordmatch' => 'Has³a nie zgadzaj± siê -- spróbuj ponownie',
	'nobughistory' => 'Ten b³±d nie ma historii',
	'logintomodify' => 'Musisz byæ zalogowany aby modyfikowaæ ten b³±d',
	'dupe_attachment' => 'Za³±cznik jest ju¿ skojarzony z tym b³êdem',
	'give_attachment' => 'Podaj nazwê pliku do za³adowania',
	'no_attachment_save_path' => 'Nie mogê znale¼æ ¶cie¿ki do zachowania pliku!',
	'attachment_path_not_writeable' => 'Nie mogê stworzyæ pliku w ¶cie¿ce zachowywania plików!',
	'attachment_move_error' => 'W czasie przenoszenia wgranego pliku wyst±pi³ b³±d',
	'bad_attachment' => 'Za³±cznik nie istnieje',
	'attachment_too_large' => 'Wybrany plik jest wiêkszy ni¿ '.number_format(ATTACHMENT_MAX_SIZE).' bajtów',
	'bad_permission' => 'Nie masz uprawnieñ wymaganych do tej funkcji',
	'project_only_all_groups' => 'Nie mo¿esz zmieniaæ konkretnych grup kiedy wybrano "Wszystkie grupy"',
	'previous_bug' => 'Poprzedni',
	'next_bug' => 'Nastêpny',
	'already_voted' => 'G³osowa³e¶ ju¿ na ten b³±d',
	'too_many_votes' => 'Osi±gn±³e¶ maksymaln± liczbê g³osów na przydzielon± u¿ytkownikowi',
	'no_votes' => 'Na ten b³±d nie oddano ¿adnych g³osów',
	'user_filter' => array(
		0 => 'Wszyscy u¿ytkownicy',
		1 => 'Aktywni u¿ytkownicy',
		2 => 'Nieaktywni u¿ytkownicy'),
	'dupe_dependency' => 'Ta zale¿no¶æ pomiêdzy b³êdami zosta³a ju¿ dodana',
	'image_path_not_writeable' => 'Podkatalog "jpgimages" nie mo¿e byæ zapisywany przez proces serwera WWW - nie mo¿na wygenerowaæ podsumowania'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Wpisz b³±d',
	'editbug' => 'Edytuj b³±d',
	'newaccount' => 'Stwórz nowe konto u¿ytkownika',
	'bugquery' => 'Przeszukiwanie bazy b³êdów',
	'buglist' => 'Lista b³êdów',
	'addcomponent' => 'Dodaj komponent',
	'editcomponent' => 'Edytuj komponent',
	'addproject' => 'Dodaj projekt',
	'editproject' => 'Edytuj projekt',
	'addversion' => 'Dodaj wersjê',
	'editversion' => 'Edytuj wersjê',
	'project' => 'Projekty',
	'os' => 'Systemy operacyjne',
	'resolution' => 'Rozpoznanie',
	'status' => 'Statusy',
	'severity' => 'Wagi',
	'user' => 'U¿ytkownicy',
	'home' => 'Strona g³ówna',
	'reporting' => 'Raportowanie',
	'group' => 'Grupy'
	);
	
?>
