<?php

// strings-es.php - Spanish strings and titles
// ------------------------------------------------------------------------
// Copyright (c) 2001 Manuel Amador (Rudd-O) <amador@alomega.com>
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
// $Id: es.php,v 1.1 2002/10/19 20:05:58 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso-8859-1',
	'nouser' => 'Ese usuario no existe',
	'dupeofself' => 'Un error no puede ser duplicado de si mismo',
	'nobug' => 'Ese error no existe',
	'givesummary' => 'Por favor escriba un resumen',
	'givedesc' => 'Por favor escriba una descripcion',
	'noprojects' => 'No hay proyectos',
	'totalbugs' => 'Total de errores',
	'giveemail' => 'Por favor escriba una direccion válida de correo electronico',
	'givelogin' => 'Por favor escriba un nombre de usuario',
	'loginused' => 'Ese nombre de usuario está siendo utilizado',
	'newacctsubject' => 'Inicio de sesion phpBugTracker',
	'newacctmessage' => "Su contraseña phpBugTracker es %s",
	'nobugs' => 'No hay errores',
	'givename' => 'Por favor escriba un nombre',
	'edit' => 'Editar',
	'addnew' => 'Agregar nuevo',
	'nooses' => 'No hay sistemas operativos',
	'giveinitversion' => 'Por favor escriba la version inicial del proyecto',
	'giveversion' => 'Por favor escriba una version',
	'noversions' => 'No hay versiones',
	'nocomponents' => 'No hay componentes',
	'nostatuses' => 'No hay estados',
	'noseverities' => 'No hay severidades',
	'givepassword' => 'Por favor escriba una contraseña',
	'nousers' => 'No hay usuarios',
	'bugbadperm' => 'Ud. no puede modificar este error',
	'bugbadnum' => 'Ese error no existe',
	'datecollision' => 'Alguien actualizo este error desde la última vez.	La informacion del error ha sido actualizada con los últimos cambios',
	'passwordmatch' => 'Las contraseñas no coinciden.  Por favor vuelva a intentar.',
	'nobughistory' => 'No hay historia para ese error',
	'logintomodify' => 'Para modificar este error, inicie una sesion con su nombre de usuario.',
	'dupe_attachment' => 'Ese archivo ya está presente',
	'give_attachment' => 'Por favor especifique un archivo a recibir',
	'no_attachment_save_path' => 'No se encuentra la carpeta de archivos',
	'attachment_path_not_writeable' => 'La carpeta de archivos está protegida contra escritura',
	'attachment_move_error' => 'Hubo un error ubicando el archivo a la carpeta de archivos',
	'bad_attachment' => 'Ese archivo no existe',
	'attachment_too_large' => 'El archivo enviado excede el tamaño máximo de '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Esa funcion requiere privilegios administrativos que Ud. no posee',
	'project_only_all_groups' => 'No se pueden elegir grupos específicos si "Todos los grupos" está seleccionado',
	'previous_bug' => 'Previo',
	'next_bug' => 'Siguiente',
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user',
	'no_votes' => 'There are no votes for this bug',
	'user_filter' => array(
		0 => 'All users',
		1 => 'Active users',
		2 => 'Inactive users'),
	'dupe_dependency' => 'That bug dependency has already been added',
	'image_path_not_writeable' => 'The subdirectory "jpgimages" is not writeable by the web process, so the summary image can not be rendered'
	);

// Page titles
$TITLE = array(
	'enterbug' => 'Enviar un error',
	'editbug' => 'Editar un error',
	'newaccount' => 'Crear una nueva cuenta de usuario',
	'bugquery' => 'Búsqueda de errores',
	'buglist' => 'Lista de errores',
	'addcomponent' => 'Agregar componente',
	'editcomponent' => 'Editar componente',
	'addproject' => 'Agregar proyecto',
	'editproject' => 'Editar proyecto',
	'addversion' => 'Agregar version',
	'editversion' => 'Editar version',
	'project' => 'Proyectos',
	'os' => 'Sistemas operativos',
	'resolution' => 'Soluciones',
	'status' => 'Estados',
	'severity' => 'Severidad',
	'user' => 'Usuarios',
	'home' => 'Inicio',
	'reporting' => 'Reportar',
	'group' => 'Grupos'
	);

?>
