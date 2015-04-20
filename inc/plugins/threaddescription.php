<?php
/**
 * Thread Description
 * Copyright 2013 Starpaul20
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

// Neat trick for caching our custom template(s)
if(my_strpos($_SERVER['PHP_SELF'], 'forumdisplay.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'forumdisplay_thread_description';
}

if(my_strpos($_SERVER['PHP_SELF'], 'newthread.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'description';
}

if(my_strpos($_SERVER['PHP_SELF'], 'editpost.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'description';
}

if(my_strpos($_SERVER['PHP_SELF'], 'search.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'forumdisplay_thread_description';
}

if(my_strpos($_SERVER['PHP_SELF'], 'showthread.php'))
{
	global $templatelist;
	if(isset($templatelist))
	{
		$templatelist .= ',';
	}
	$templatelist .= 'showthread_description';
}

// Tell MyBB when to run the hooks
$plugins->add_hook("forumdisplay_thread", "threaddescription_forum_description");
$plugins->add_hook("showthread_start", "threaddescription_description");
$plugins->add_hook("search_results_thread", "threaddescription_forum_description");
$plugins->add_hook("newthread_start", "threaddescription_newthread");
$plugins->add_hook("newthread_do_newthread_end", "threaddescription_do_newthread");
$plugins->add_hook("editpost_end", "threaddescription_editpost");
$plugins->add_hook("editpost_do_editpost_end", "threaddescription_do_editpost");

// The information that shows up on the plugin manager
function threaddescription_info()
{
	global $lang;
	$lang->load("description", true);

	return array(
		"name"				=> $lang->threaddescription_info_name,
		"description"		=> $lang->threaddescription_info_desc,
		"website"			=> "http://galaxiesrealm.com/index.php",
		"author"			=> "Starpaul20",
		"authorsite"		=> "http://galaxiesrealm.com/index.php",
		"version"			=> "1.1",
		"codename"			=> "threaddescription",
		"compatibility"		=> "18*"
	);
}
 
// This function runs when the plugin is installed.
function threaddescription_install()
{
	global $db;
	threaddescription_uninstall();

	switch($db->type)
	{
		case "sqlite":
			$db->add_column("threads", "description", "varchar(240) NOT NULL default ''");
			break;
		default:
			$db->add_column("threads", "description", "varchar(240) NOT NULL default '' AFTER subject");
			break;
	}
}

// Checks to make sure plugin is installed
function threaddescription_is_installed()
{
	global $db;
	if($db->field_exists("description", "threads"))
	{
		return true;
	}
	return false;
}

// This function runs when the plugin is uninstalled.
function threaddescription_uninstall()
{
	global $db;
	if($db->field_exists("description", "threads"))
	{
		$db->drop_column("threads", "description");
	}
}

// This function runs when the plugin is activated.
function threaddescription_activate()
{
	global $db;

	$insert_array = array(
		'title'		=> 'description',
		'template'	=> $db->escape_string('<tr>
<td class="trow2"><strong>{$lang->description}</strong></td>
<td class="trow2"><input type="text" class="textbox" name="description" size="40" maxlength="240" value="{$description}" tabindex="2" /></td>
</tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'forumdisplay_thread_description',
		'template'	=> $db->escape_string('<em><span class="smalltext" style="background: url(\'images/nav_bit.png\') no-repeat left; padding-left: 18px;">{$description}</span></em><br />'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	$insert_array = array(
		'title'		=> 'showthread_description',
		'template'	=> $db->escape_string('<em><span class="smalltext">{$description}</span></em>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
	$db->insert_query("templates", $insert_array);

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("editpost", "#".preg_quote('{$posticons}')."#i", '{$threaddescription}{$posticons}');
	find_replace_templatesets("newthread", "#".preg_quote('{$posticons}')."#i", '{$threaddescription}{$posticons}');
	find_replace_templatesets("showthread", "#".preg_quote('{$thread[\'subject\']}</strong>')."#i", '{$thread[\'subject\']}</strong><br />{$thread[\'description\']}');
	find_replace_templatesets("forumdisplay_thread", "#".preg_quote('{$thread[\'profilelink\']}')."#i", '{$thread[\'description\']}{$thread[\'profilelink\']}');
	find_replace_templatesets("search_results_threads_thread", "#".preg_quote('{$thread[\'profilelink\']}')."#i", '{$thread[\'description\']}{$thread[\'profilelink\']}');
}

// This function runs when the plugin is deactivated.
function threaddescription_deactivate()
{
	global $db;
	$db->delete_query("templates", "title IN('description','forumdisplay_thread_description','showthread_description')");

	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("forumdisplay_thread", "#".preg_quote('{$thread[\'description\']}')."#i", '', 0);
	find_replace_templatesets("search_results_threads_thread", "#".preg_quote('{$thread[\'description\']}')."#i", '', 0);
	find_replace_templatesets("showthread", "#".preg_quote('<br />{$thread[\'description\']}')."#i", '', 0);
	find_replace_templatesets("newthread", "#".preg_quote('{$threaddescription}')."#i", '', 0);
	find_replace_templatesets("editpost", "#".preg_quote('{$threaddescription}')."#i", '', 0);
}

// Show description on forumdisplay and search results
function threaddescription_forum_description()
{
	global $thread, $templates;
	if(!empty($thread['description']))
	{
		$description = htmlspecialchars_uni($thread['description']);

		eval("\$thread['description'] = \"".$templates->get("forumdisplay_thread_description")."\";");
	}
}

// Show description on showthread
function threaddescription_description()
{
	global $thread, $templates;
	if(!empty($thread['description']))
	{
		$description = htmlspecialchars_uni($thread['description']);

		eval("\$thread['description'] = \"".$templates->get("showthread_description")."\";");
	}
}

// Add description on new thread
function threaddescription_newthread()
{
	global $lang, $mybb, $templates, $post_errors, $thread, $threaddescription, $description;
	$lang->load("description");

	if(isset($mybb->input['previewpost']) || $post_errors)
	{
		$description = htmlspecialchars_uni($mybb->get_input('description'));
	}
	else
	{
		$description = htmlspecialchars_uni($thread['description']);
	}

	eval("\$threaddescription = \"".$templates->get("description")."\";");
}

// Add description
function threaddescription_do_newthread()
{
	global $db, $mybb, $tid;

	$description = array(
		"description" => $db->escape_string($mybb->get_input('description'))
	);
	$db->update_query("threads", $description, "tid='{$tid}'");
}

// Show description on edit page
function threaddescription_editpost()
{
	global $lang, $mybb, $thread, $templates, $post_errors, $threaddescription, $description;
	$lang->load("description");

	$pid = $mybb->get_input('pid', MyBB::INPUT_INT);
	if($thread['firstpost'] == $pid)
	{
		if(isset($mybb->input['previewpost']) || $post_errors)
		{
			$description = htmlspecialchars_uni($mybb->get_input('description'));
		}
		else
		{
			$description = htmlspecialchars_uni($thread['description']);
		}

		eval("\$threaddescription = \"".$templates->get("description")."\";");
	}
}

// Update description
function threaddescription_do_editpost()
{
	global $db, $mybb, $tid;

	$description = array(
		"description" => $db->escape_string($mybb->get_input('description'))
	);
	$db->update_query("threads", $description, "tid='{$tid}'");
}

?>