<?php

// it.php - Italian strings and titles
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
// $Id: it.php,v 1.1 2002/10/19 20:05:58 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'us-ascii',
	'nouser' => 'L\'utente non esiste',
	'dupeofself' => 'Un bug non puo\' essere un duplicato di se stesso',
	'nobug' => 'Il bug non esiste',
	'givesummary' => 'Inserire un sommario',
	'givedesc' => 'Inserire una descrizione',
	'noprojects' => 'Nessun progetto trovato',
	'totalbugs' => 'Bug totali',
	'giveemail' => 'Inserire un indirizzo valido di posta elettronica',
	'givelogin' => 'Inserire un login',
	'loginused' => 'Il login e\' gia\' in uso',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "La vostra password di phpBugTracker e\' %s",
	'nobugs' => 'Nessun bug trovato',
	'givename' => 'Inserire un nome',
	'edit' => 'Modificare',
	'addnew' => 'Aggiungere',
	'nooses' => 'Nessun S.O. trovato',
	'giveinitversion' => 'Inserire una versione iniziale per il progetto',
	'giveversion' => 'Inserire una versione',
	'noversions' => 'Nessuna versione trovata',
	'nocomponents' => 'Nessun componente trovato',
	'nostatuses' => 'Nessuno stato trovato',
	'noseverities' => 'Nessuna severita\' trovata',
	'givepassword' => 'Inserire una password',
	'nousers' => 'Nessun utente trovato',
	'bugbadperm' => 'Non potete cambiare questo bug',
	'bugbadnum' => 'Il bug non esiste',
	'datecollision' => 'Il bug e\' stato modificato il bug dopo che l\'avete visualizzato.        L\'informazione sul bug e\' stata ricaricata con le ultime modifiche.',
	'passwordmatch' => 'Le password non corrispondono -- Riprovare',
	'nobughistory' => 'Non c\'e\' nessuna storia per il bug',
	'logintomodify' => 'Bisogna aver fatto il login per modificare questo bug',
	'dupe_attachment' => 'L\'attachment esiste gia\' per questo bug',
	'give_attachment' => 'Specificare un file da caricare',
	'no_attachment_save_path' => 'Manca il path in cui salvare il file!',
	'attachment_path_not_writeable' => 'Non si puo\' creare un file nel path di salvataggio',
	'attachment_move_error' => 'Si e\' verificato un errore spostando il file caricato',
	'bad_attachment' => 'L\' attachment non esiste',
	'attachment_too_large' => 'La dimensione del file specificato e\' maggiore di '.number_format(ATTACHMENT_MAX_SIZE).' byte',
	'bad_permission' => 'Mancano i permessi richiesti per questa funzione',
	'project_only_all_groups' => 'Non si possono scegliere gruppi specifici quando l\'opzione "Tutti i gruppi" e\' selezionata',
	'previous_bug' => 'Precedente',
	'next_bug' => 'Successivo',
	'already_voted' => 'Avete gia\' votato per questo bug',
	'too_many_votes' => 'Avete raggiunto il numero massimo di voti per utente',
	'no_votes' => 'Non ci sono voti per questo bug',
	'user_filter' => array(
		0 => 'Tutti gli utenti',
		1 => 'Utenti attivi',
		2 => 'Utenti Inattivi'),
	'dupe_dependency' => 'La dipendenza per il bug e\' gia\' stata aggiunta',
	'image_path_not_writeable' => 'The subdirectory "jpgimages" is not writeable by the web process, so the summary image can not be rendered'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Inserisci Bug',
	'editbug' => 'Modifica Bug',
	'newaccount' => 'Crea nuovo account',
	'bugquery' => 'Interrogazione Bug',
	'buglist' => 'Lista dei Bug',
	'addcomponent' => 'Aggiungi Componente',
	'editcomponent' => 'Modifica Componente',
	'addproject' => 'Aggiungi Progetto',
	'editproject' => 'Modifica progetto',
	'addversion' => 'Aggiungi Versione',
	'editversion' => 'Modifica Versione',
	'project' => 'Progetti',
	'os' => 'Sistemi Operativi',
	'resolution' => 'Risoluzioni',
	'status' => 'Stati',
	'severity' => 'Severita\'',
	'user' => 'Utenti',
	'home' => 'Home',
	'reporting' => 'Reporting',
	'group' => 'Gruppi'
	);

?>
