<?php

// ru_koi8-r.php - Russian strings and titles, KOI8-R encoding
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
// $Id: ru_koi8-r.php,v 1.11 2002/03/26 17:12:29 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'koi8-r',
	'nouser' => 'Такого пользователя не существует',
	'dupeofself' => 'Проблема не может быть дубликатом самой себя',
	'nobug' => 'Такой проблемы не существует',
	'givesummary' => 'Пожалуйста, укажите суть проблемы',
	'givedesc' => 'Пожалуйста, введите описание',
	'noprojects' => 'Проектов не найдено',
	'totalbugs' => 'Всего проблем',
	'giveemail' => 'Пожалуйста, укажите работающий email-адрес',
	'givelogin' => 'Пожалуйста, укажите логин',
	'loginused' => 'Такой логин уже занят',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Ваш пароль в phpBugTracker - %s",
	'nobugs' => 'Проблем не найдено',
	'givename' => 'Пожалуйста, укажите имя',
	'edit' => 'Правка',
	'addnew' => 'Добавить',
	'nooses' => 'Не найдено ОС',
	'giveinitversion' => 'Пожалуйста, укажите начальную версию для данного проекта',
	'giveversion' => 'Пожалуйста, укажите версию',
	'noversions' => 'Версий не найдено',
	'nocomponents' => 'Компонент не найдено',
	'nostatuses' => 'Состояний не найдено',
	'noseverities' => 'Описаний важности не найдено',
	'givepassword' => 'Пожалуйста, укажите пароль',
	'nousers' => 'Пользователей не найдено',
	'bugbadperm' => 'Вы не можете изменить эту проблему',
	'bugbadnum' => 'Такой проблемы не существует',
	'datecollision' => 'Кто-то обновил эту проблему пока вы ее просматривали. Информация о проблеме перезагружена, включая последние изменения.',
	'passwordmatch' => 'Эти пароли не совпадают -- пожалуйста, попробуйте еще раз',
	'nobughistory' => 'Для этой проблемы истории нет',
	'logintomodify' => 'Для изменения этой проблемы вы должны войти в систему',
	'dupe_attachment' => 'Такое приложение уже существует для данной проблемы',
	'give_attachment' => 'Пожалуйста, укажите файл для загрузки',
	'no_attachment_save_path' => 'Не могу найти где созранить файл!',
	'attachment_path_not_writeable' => 'Не могу создать файл в указанном месте',
	'attachment_move_error' => 'При перемещении загруженного файла произошла ошибка',
	'bad_attachment' => 'Такого приложения не существует',
	'attachment_too_large' => 'Указанный файл имеет размер больше чем '.number_format(ATTACHMENT_MAX_SIZE).' байт',
	'bad_permission' => 'У вас нет полномочий, необходимых дляданной функции',
	'project_only_all_groups' => 'You cannot choose specific groups when "All Groups" is chosen',
	'previous_bug' => 'Previous',
	'next_bug' => 'Next',
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
	'enterbug' => 'Ввод проблемы',
	'editbug' => 'Правка проблемы',
	'newaccount' => 'Создание нового входа в систему',
	'bugquery' => 'Запрос проблем',
	'buglist' => 'Список проблем',
	'addcomponent' => 'Добавить компонент',
	'editcomponent' => 'Правка компонента',
	'addproject' => 'Добавить проект',
	'editproject' => 'Правка проекта',
	'addversion' => 'Добавить версию',
	'editversion' => 'Правка версии',
	'project' => 'Проекты',
	'os' => 'Операционные системы',
	'resolution' => 'Резолюции',
	'status' => 'Состояния',
	'severity' => 'Важность',
	'user' => 'Пользователи',
	'home' => 'Главная страница',
	'reporting' => 'Отчеты',
	'group' => 'Groups'
	);
	
?>
