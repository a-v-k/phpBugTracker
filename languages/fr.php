<?php

// fr.php - French strings and titles
// Translation by Pierre Wargnier
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
// $Id: fr.php,v 1.8 2002/03/01 00:41:31 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Cet utilisateur n\'existe pas',
	'dupeofself' => 'Un bogue ne peut pas être doublon de lui-même',
	'nobug' => 'Ce bogue n\'existe pas',
	'givesummary' => 'Veuillez saisir un résumé',
	'givedesc' => 'Veuillez saisir une description',
	'noprojects' => 'Pas de projet trouvé',
	'totalbugs' => 'Total Bogues',
	'giveemail' => 'Veuillez saisir une adresse électronique valide',
	'givelogin' => 'Please enter a login',
	'loginused' => 'Cet identifiant de connection est déjà utilisé',
	'newacctsubject' => 'Identifiant de connection phpBugTracker',
	'newacctmessage' => "Votre mot de passe phpBugTracker est %s",
	'nobugs' => 'Pas de bogue trouvé',
	'givename' => 'Veuillez saisir un nom',
	'edit' => 'Editer',
	'addnew' => 'Nouveau',
	'nooses' => 'Pas d\'environement trouvé',
	'giveinitversion' => 'Veuillez saisir une version initiale pour ce projet',
	'giveversion' => 'Veuillez saisir une version',
	'noversions' => 'Pas de version trouvée',
	'nocomponents' => 'Pas de composant trouvé',
	'nostatses' => 'Pas de statut trouvé',
	'givepassword' => 'Veuillez saisir un mot de passe',
	'nousers' => 'Pas d\'utilisateur trouvé',
	'bugbadperm' => 'Vous ne pouvez pas modifier ce bogue',
	'bugbadnum' => 'Ce bogue n\'existe pas',
	'datecollision' => 'Ce bogue a été modifié depuis votre dernière lecture.	Il a été rechargé avec ses dernières modifications.',
	'passwordmatch' => 'Les mots de passe ne correspondent pas -- veuillez re-essayer',
	'nobughistory' => 'Il n\'y a pas d\'historique pour ce bogue',
	'logintomodify' => 'Vous devez être connecté pour modifier ce bogue',
	'dupe_attachment' => 'Ce fichier attaché existe déjà pour ce bogue',
	'give_attachment' => 'Veuillez spécifier un fichier à envoyer',
	'no_attachment_save_path'  => 'Imossible de savoir ou stocker le fichier!',
	'attachment_path_not_writeable' => 'Impossible de créer un fichier dans le répertoire de sauvegarde',
	'attachment_move_error' => 'Une erreur est survenue lors du déplacement du fichier envoyé',
	'bad_attachment' => 'Ce fichier attaché n\'existe pas',
	'attachment_too_large' => 'Le fichier envoyé dépasse la limite des '.number_format(ATTACHMENT_MAX_SIZE).' octets',
	'bad_permission' => 'Vous n\'avez pas les droits pour réaliser cette action',
	'noseverities' => 'Pas de criticités trouvées',
	'project_only_all_groups' => 'You cannot choose specific groups when "All Groups" is chosen',
	'previous_bug' => 'Previous',
	'next_bug' => 'Next',
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user',
	'no_votes' => 'There are no votes for this bug',
	'user_filter' => array(
		0 => 'All users',
		1 => 'Active users',
		2 => 'Inactive users')
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Saisir un bogue',
	'editbug' => 'Editer un bogue',
	'newaccount' => 'Créer un nouveau compte',
	'bugquery' => 'Recherche de bogues',
	'buglist' => 'Liste de bogues',
	'addcomponent' => 'Ajouter un composant',
	'editcomponent' => 'Editer un composant',
	'addproject' => 'Ajouter un projet',
	'editproject' => 'Editer un projet',
	'addversion' => 'Ajouter une version',
	'editversion' => 'Editer une version',
	'project' => 'Projets',
	'os' => 'Environements',
	'resolution' => 'Resolutions',
	'status' => 'Statuts',
	'user' => 'Utilisateurs',
	'home' => 'Sommaire',
	'reporting' => 'Tableaux de bord',
	'severity' => 'Criticité',
	'group' => 'Groups'
	);
	
?>
