<?php

// gb2312.php - Chinese strings and titles
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
// $Id $
// ------------------------------------------------------------------------
// Translated by liaobin(liao_bin@371.net) 
// ------------------------------------------------------------------------

$STRING = array(
	'lang_charset' => 'gb2312',
	'nouser' => '用户不存在',
	'dupeofself' => '一个Bug不能同它本身重复',
	'nobug' => '这个Bug不存在',
	'givesummary' => '请给出概述',
	'givedesc' => '请给出具体描述',
	'noprojects' => '没有找到项目',
	'totalbugs' => '共有Bug',
	'giveemail' => '请输入一个有效的email名',
	'givelogin' => '请输入登录用户名',
	'loginused' => '这个登录用户名已经被使用',
	'newacctsubject' => 'phpBugTracker 登录',
	'newacctmessage' => "您的 phpBugTracker 密码是 %s",
	'nobugs' => '没有Bug找到',
	'givename' => '请输入姓名',
	'edit' => '编辑',
	'addnew' => '添加',
	'nooses' => '没有 OSes 找到',
	'giveinitversion' => '请输入项目的初始化版本号',
	'giveversion' => '请输入版本号',
	'noversions' => '没有版本号被找到',
	'nocomponents' => '没有项目单元被找到',
	'nostatuses' => '没有状态被找到',
	'noseverities' => '没有严重程度被找到',
	'givepassword' => '请输入一个密码',
	'nousers' => '没有用户找到',
	'bugbadperm' => '您不能更改这个Bug',
	'bugbadnum' => '这个Bug不存在',
	'datecollision' => '在您看过这个bug之后，有人更新了这个Bug.	这个 bug 的信息已经在最后一次变更后重载了.',
	'passwordmatch' => '密码不匹配 -- 请再试一遍',
	'nobughistory' => '这个bug没有历史',
	'logintomodify' => '您修改这个bug必须先登录',
	'dupe_attachment' => '这个bug的附件已经存在',
	'give_attachment' => '请给出需要上载的文件名',
	'no_attachment_save_path' => '没有找到附件文件的上载路径!',
	'attachment_path_not_writeable' => '附件上载路径不能写!',
	'attachment_move_error' => '移动附件时出错',
	'bad_attachment' => '附件不存在',
	'attachment_too_large' => '您上载的附件大于 '.number_format(ATTACHMENT_MAX_SIZE).' 字节',
	'bad_permission' => '您没有使用这个功能的权限',
	'project_only_all_groups' => 'You cannot choose specific groups when "All Groups" is chosen',
	'previous_bug' => 'Previous',
	'next_bug' => 'Next',
	'already_voted' => 'You have already voted for this bug',
	'too_many_votes' => 'You have reached the maximum number of votes per user',
	'no_votes' => 'There are no votes for this bug'
	);
	
// Page titles
$TITLE = array(
	'enterbug' => '输入一个bug',
	'editbug' => '编辑bug',
	'newaccount' => '创建新用户',
	'bugquery' => '查询Bug ',
	'buglist' => 'Bug 列表',
	'addcomponent' => '添加一个单元',
	'editcomponent' => '编辑一个单元',
	'addproject' => '添加一个项目',
	'editproject' => '编辑一个项目',
	'addversion' => '添加版本号',
	'editversion' => '编辑版本号',
	'project' => '项目',
	'os' => '操作系统',
	'resolution' => '解决方法',
	'status' => '状态',
	'severity' => '严重程度',
	'user' => '用户',
	'home' => '主页面',
	'reporting' => '报表',
	'group' => 'Groups'
	);
	
?>
