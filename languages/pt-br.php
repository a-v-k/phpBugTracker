<?php

// strings-br.php - Brazilian Portuguese strings and titles
// Translation by Alexandre Ponso
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
// $Id: pt-br.php,v 1.10 2002/02/28 17:31:45 bcurtis Exp $

$STRING = array(
	'lang_charset' => 'iso8859-1',
	'nouser' => 'Este usuário não existe',
	'dupeofself' => 'Um bug não pode ser duplicata de si mesmo',
	'nobug' => 'Este bug não existe',
	'givesummary' => 'Por favor, digite um resumo',
	'givedesc' => 'Por favor, digite uma descrição',
	'noprojects' => 'Nenhum projeto encontrado',
	'totalbugs' => 'Total de Bugs',
	'giveemail' => 'Por favor, digite um endereço de email válido',
	'givelogin' => 'Por favor, digite um login',
	'loginused' => 'Este login já está sendo usado',
	'newacctsubject' => 'phpBugTracker Login',
	'newacctmessage' => "Sua senha phpBugTracker é %s",
	'nobugs' => 'Nenhum bug encontrado',
	'givename' => 'Por favor, digite um nome',
	'edit' => 'Editar',
	'addnew' => 'Adicionar novo',
	'nooses' => 'Nenhum OS encontrado',
	'giveinitversion' => 'Por favor, digite uma versão inicial para o projeto',
	'giveversion' => 'Por favor, digite uma versão',
	'noversions' => 'Nenhuma versão encontrada',
	'nocomponents' => 'Nenhum componente encontrado',
	'nostatuses' => 'Nenhum status encontrado',
	'noseverities' => 'No severities found',
	'givepassword' => 'Por favor, digite uma senha',
	'nousers' => 'Nenhum usuário encontrado',
	'bugbadperm' => 'Você não pode alterar este bug',
	'bugbadnum' => 'Este bug não existe',
	'datecollision' => 'Alguém já alterou este bug desde que você o viu.	A informação sobre o bug foi atualizada com as últimas alterações.',
	'passwordmatch' => 'As senhas não conferem -- por favor, tente novamente',
	'nobughistory' => 'Não há historico para este bug',
	'logintomodify' => 'Você deve estar autenticado para alterar este bug',
	'dupe_attachment' => 'Este anexo já existe para esse bug',
	'give_attachment' => 'Por favor, escolha um arquivo para upload',
	'no_attachment_save_path' => 'Não há diretório de destino!',
	'attachment_path_not_writeable' => 'Não foi possível criar um arquivo no diretório de destino',
	'attachment_move_error' => 'Ocorreu um erro ao mover o arquivo transmitido',
	'bad_attachment' => 'Este anexo não existe',
	'attachment_too_large' => 'O arquivo escolhido é maior que '.number_format(ATTACHMENT_MAX_SIZE).' bytes',
	'bad_permission' => 'Você não tem as permissões necessárias para essa função',
	'project_only_all_groups' => 'Você não pode escolher um grupo específico quando "All groups" esta selecionado',
	'previous_bug' => 'Anterior',
	'next_bug' => 'Próximo' ,
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => 'Adicionar um Bug',
	'editbug' => 'Editar Bug',
	'newaccount' => 'Criar uma nova conta',
	'bugquery' => 'Pesquisar Bug',
	'buglist' => 'Listar Bug',
	'addcomponent' => 'Adicionar Componente',
	'editcomponent' => 'Editar Componente',
	'addproject' => 'Adicionar Projeto',
	'editproject' => 'Editar Projeto',
	'addversion' => 'Adicionar Versão',
	'editversion' => 'Editar Versão',
	'project' => 'Projeto',
	'os' => 'Sistemas Operacionais',
	'resolution' => 'Normas',
	'status' => 'Status',
	'severity' => 'Severity',
	'user' => 'Usuários',
	'home' => 'Home',
	'reporting' => 'Relatórios',
	'group' => 'Grupos'
	);
	
?>
