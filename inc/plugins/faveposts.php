<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

function faveposts_info()
{
	return array(
		"name"		=> "Posts favorisieren",
		"description"	=> "Erlaubt es Mitgliedern, Posts zu favorisieren, die anschließend im UserCP angezeigt werden.",
		"website"	=> "https://github.com/ItsSparksFly",
		"author"	=> "sparks fly",
		"authorsite"	=> "https://github.com/ItsSparksFly",
		"version"	=> "1.0",
		"compatibility" => "18*"
	);
}

function faveposts_install()
{
    global $db;
    
    # TODO: Alert
	
	$db->query("CREATE TABLE ".TABLE_PREFIX."faveposts (
		`fpid` int(11) NOT NULL AUTO_INCREMENT,
		`fpdid` int(11) NOT NULL,
		`uid` int(11) NOT NULL,
		`pid` int(11) NOT NULL,
		`timestamp` int(21) NOT NULL,
		`customtitle` varchar(500) COLLATE utf8_general_ci NOT NULL,
		PRIMARY KEY (`fpid`),
		KEY `fpid` (`fpid`)
		)
		ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

	$db->query("CREATE TABLE ".TABLE_PREFIX."faveposts_dirs (
		`fpdid` int(11) NOT NULL AUTO_INCREMENT,
		`uid` int(11) NOT NULL,
		`title` varchar(500) COLLATE utf8_general_ci NOT NULL,
		PRIMARY KEY (`fpdid`),
		KEY `fpdid` (`fpdid`)
		)
		ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci AUTO_INCREMENT=1");

}

function faveposts_activate() {
    global $db, $post;
    include MYBB_ROOT."/inc/adminfunctions_templates.php";
    find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'button_edit\']}')."#i", '{$post[\'faveposts\']}{$post[\'button_edit\']}');

    $insert_array = array(
		'title'		=> 'postbit_faveposts',
        'template'	=> $db->escape_string('<a href="#faveposts{$post[\'pid\']}" title="{$lang->faveposts}" class="postbit_edit"><span>{$lang->faveposts_fave_button}</span></a>
        <div id="faveposts$post[pid]" class="favepostspop">
  <div class="favepostpopup">
  <form method="post" action="misc.php?action=faveposts&pid={$post[\'pid\']}">
  <table class="tborder">
  <tr>
  <td class="thead" colspan="2">{$lang->faveposts_fave_button}</td>
  </tr>
  <tr>
  <td class="trow1">{$lang->faveposts_title}<br /><span class="smalltext">{$lang->faveposts_title_description}</span></td>
  <td class="trow1"><input type="text" name="customtitle" value="{$post[\'subject\']}" /></td>
  </tr>
  <tr>
  <td class="trow1">{$lang->faveposts_folder}<br /><span class="smalltext">{$lang->faveposts_folder_description}</span></td>
  <td class="trow1"><select name="fpdid">{$folder_bit}</select></td>
  </tr>
  <tr>
  <td class="trow1" colspan="2" align="center">
  <input type="submit" value="{$lang->faveposts_fave_button}" />
  </td>
  </tr>
  </table>
  </form>
  </div>
  <a href="#closepop" class="closepop"></a>
</div>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'postbit_unfaveposts',
		'template'	=> $db->escape_string('<a href="misc.php?action=unfave&pid={$post[\'pid\']}" title="{$lang->faveposts}" class="postbit_edit"><span>{$lang->faveposts_unfave_button}</span></a>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'usercp_faveposts_nav',
        'template'	=> $db->escape_string('<div class="thead">{$lang->faveposts_faved}</div>
        <div class="tcat"><a href="usercp.php?action=faveposts">{$lang->faveposts_faved_all}</a></div>
        <div class="tcat"><a href="usercp.php?action=favefolders">{$lang->faveposts_folders_new}</a></div>
        <div class="thead">{$lang->faveposts_folders}</div>
        <div class="tcat"><a href="usercp.php?action=faveposts&folder=0">{$lang->faveposts_folders_general}</a></div>
        {$folder_bit}'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'usercp_faveposts_nav_folders',
		'template'	=> $db->escape_string('<div class="tcat"><a href="usercp.php?action=faveposts&folder={$folder[\'fpdid\']}">{$folder[\'title\']}</a> <a href="#editfolder{$folder[\'fpdid\']}" alt="Bearbeiten" title="Bearbeiten"><i class="fas fa-pencil-alt"></i></a> <a href="usercp.php?action=del_favefolders&fpdid={$folder[\'fpdid\']}" alt="Löschen" title="Bearbeiten"><i class="fas fa-trash-alt"></i></a></div> 
        <div id="editfolder{$folder[\'fpdid\']}" class="favepostspop">
  <div class="favepostpopup">
  <div class="tborder" style="padding: 10px;">
  <form method="post" action="usercp.php?action=edit_favefolders&fpdid={$folder[\'fpdid\']}">
  <center>
  <input type="text" value="{$folder[\'title\']}" name="title" /><br /><br />
  <input type="submit" value="{$lang->faveposts_submit_folder}" />
  </form>
  </div>
  </div>
  <a href="#closepop" class="closepop"></a>
  </div>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'usercp_faveposts',
		'template'	=> $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->edit_options}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%" border="0" align="center">
        <tr>
        {$usercpnav}
        <td valign="top">
        {$errors}
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tr>
        <td class="thead" colspan="2"><strong>{$lang->faveposts_page}</strong></td>
        </tr>
        <tr>
        <td valign="top" width="25%">{$faveposts_nav}</td>
        <td valign="top">
        <div style="margin: 20px; text-align: justify;">
        {$lang->faveposts_desc}<br /><br />
        <table border="0" cellspacing="5" cellpadding="5" class="tborder smalltext">
        <tr>
        <td class="thead">{$lang->faveposts_saved}</td>
        <td class="thead">{$lang->faveposts_folder}</td>
        <td class="thead">{$lang->faveposts_saved}</td>
        </tr>
        {$faveposts_bit}
        </table>
        {$multipage}
        </div>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        {$footer}
        </body>
        </html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'usercp_faveposts_bit',
		'template'	=> $db->escape_string('<tr>
        <td class="trow2" align="center"><a href="showthread.php?action={$post[\'tid\']}&pid={$post[\'pid\']}#{$post[\'pid\']}" target="_blank">{$favepost[\'customtitle\']}</a> <a href="misc.php?action=unfave&pid={$faveposts[\'pid\']}" title="{$lang->faveposts}"><i class="fas fa-trash-alt"></i></a></td>
        <td class="trow2" align="center">{$foldertitle}</td>
        <td class="trow2" align="center">{$savedate}</td>
        </tr>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    $insert_array = array(
		'title'		=> 'usercp_favefolders',
		'template'	=> $db->escape_string('<html>
        <head>
        <title>{$mybb->settings[\'bbname\']} - {$lang->edit_options}</title>
        {$headerinclude}
        </head>
        <body>
        {$header}
        <table width="100%" border="0" align="center">
        <tr>
        {$usercpnav}
        <td valign="top">
        {$errors}
        <table border="0" cellspacing="{$theme[\'borderwidth\']}" cellpadding="{$theme[\'tablespace\']}" class="tborder">
        <tr>
        <td class="thead" colspan="2"><strong>{$lang->faveposts_folders_new}</strong></td>
        </tr>
        <tr>
        <td valign="top" width="25%">{$faveposts_nav}</td>
        <td valign="top"><br />
        <div style="margin: 20px; text-align: justify;">
        <form method="post" action="usercp.php?action=do_favefolders">
        <table border="0" cellspacing="5" cellpadding="5" class="tborder smalltext">
        <tr>
        <td class="thead">{$lang->faveposts_folders_name}</td>
        </tr>
        <tr>
        <td class="trow2" align="center">
        <input type="text" name="title" />
        </td>
        </tr>
        <tr>
        <td class="trow2" align="center">
        <input type="submit" value="{$lang->folders_new}" />
        </td>
        </tr>
        </table>
        </form>
        </div>
        </td>
        </tr>
        </table>
        </td>
        </tr>
        </table>
        </form>
        {$footer}
        </body>
        </html>'),
		'sid'		=> '-1',
		'version'	=> '',
		'dateline'	=> TIME_NOW
	);
    $db->insert_query("templates", $insert_array);

    # TODO: create templates for faveposts and favefolders usercp

    $css = array(
		'name' => 'faveposts.css',
		'tid' => 1,
        "stylesheet" =>	'
        .favepostspop 
        { 
            position: fixed; 
            top: 0; 
            right: 0; 
            bottom: 0; 
            left: 0; 
            background: hsla(0, 0%, 0%, 0.5); 
            z-index: 1111; 
            opacity:0; 
            -webkit-transition: .5s ease-in-out; 
            -moz-transition: .5s ease-in-out; 
            transition: .5s ease-in-out; 
            pointer-events: none; 
        } 
        
        .favepostspop:target { 
            opacity:1;
            pointer-events: auto; 
        } 
        
        .favepostspop > .favepostpopup { 
            background: transparent; 
            width: 450px; 
            position: relative; 
            margin: 10% auto; 
            padding: 25px; 
            z-index: 33333; 
        } 
        
        .closepop { 
            position: absolute; 
            right: -5px; 
            top:-5px; 
            width: 100%; 
            height: 100%; 
            z-index: 2222; 
        }
        ',
		'cachefile' => $db->escape_string(str_replace('/', '', faveposts.css)),
		'lastmodified' => time()
	);

	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";

	$sid = $db->insert_query("themestylesheets", $css);
	$db->update_query("themestylesheets", array("cachefile" => "css.php?stylesheet=".$sid), "sid = '".$sid."'", 1);

	$tids = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($tids)) {
		update_theme_stylesheet_list($theme['tid']);
	}
}

function faveposts_is_installed()
{
	global $db;
	if($db->table_exists('faveposts'))
	{
		return true;
	}
	return false;
}

function faveposts_uninstall()
{
	global $db, $cache;

	if($db->table_exists("faveposts")) {
  		$db->drop_table("faveposts");
      }
      
      if($db->table_exists("faveposts_dirs")) {
  		$db->drop_table("faveposts_dirs");
      }
      
	rebuild_settings();
}

function faveposts_deactivate()
{
    global $db;

    // drop css
	require_once MYBB_ADMIN_DIR."inc/functions_themes.php";
	$db->delete_query("themestylesheets", "name = 'faveposts.css'");
	$query = $db->simple_select("themes", "tid");
	while($theme = $db->fetch_array($query)) {
		update_theme_stylesheet_list($theme['tid']);
    }
    
	include MYBB_ROOT."/inc/adminfunctions_templates.php";
	find_replace_templatesets("postbit_classic", "#".preg_quote('{$post[\'faveposts\']}')."#i", '', 0);
	$db->delete_query("templates", "title LIKE '%faveposts%'");
}

$plugins->add_hook("postbit", "faveposts_postbit");
function faveposts_postbit(&$post)
{
	global $lang, $templates, $db, $mybb;
    $lang->load('faveposts');
    $uid = (int)$mybb->user['uid'];
    $pid = (int)$post['pid'];
    // check if post is saved by this user already
    $sql = "SELECT * FROM ".TABLE_PREFIX."faveposts WHERE uid = '{$uid}' AND pid = '{$pid}'";
    $query = $db->query($sql);
    // and decide which button to show
    if(mysqli_num_rows($query) > 0) {
        eval("\$post['faveposts'] = \"".$templates->get("postbit_unfaveposts")."\";");
    } else { 
        // get this user's folders
        $folder_bit = "";
        $folder_bit .= "<option value=\"0\">Allgemein</option>";
        $query2 = $db->simple_select("faveposts_dirs", "*", "uid = '$uid'");
        while($folders = $db->fetch_array($query2)) {
            $folder_bit .= "<option value=\"{$folders['fpdid']}\">{$folders['title']}</option>";
        }
        eval("\$post['faveposts'] = \"".$templates->get("postbit_faveposts")."\";");  
    }
	return $post;
}

$plugins->add_hook("misc_start", "faveposts_misc");
function faveposts_misc() {
	
	global $db, $mybb;

	$mybb->input['action'] = $mybb->get_input('action');	
	
	if($mybb->input['action'] == "faveposts") {
        $pid = $mybb->get_input('pid');
        $fpdid = $mybb->get_input('fpdid');
		$uid = $mybb->user['uid'];
		$post = get_post($pid);
		$tid = $post['tid'];
		$customtitle = $db->escape_string($mybb->get_input('customtitle'));
		if(empty($customtitle)) {
			$customtitle = $db->escape_string($post['subject']);
		}
		$insert_array = array(
			"uid" => (int)$uid,
            "pid" => (int)$pid,
            "fpdid" => (int)$fpdid,
			"customtitle" => $customtitle,
			"timestamp" => TIME_NOW
		);
		$db->insert_query("faveposts", $insert_array);
		redirect("showthread.php?tid={$tid}&pid={$pid}#pid{$pid}");
	}
	
	if($mybb->input['action'] == "unfave") {
		$pid = $mybb->get_input('pid');
		$uid = $mybb->user['uid'];
		$post = get_post($pid);
		$tid = $post['tid'];
		$db->delete_query("faveposts", "pid = {$pid} AND uid = {$uid}");
		redirect("showthread.php?tid={$tid}&pid={$pid}#pid{$pid}");
	}

}

$plugins->add_hook("usercp_start", "faveposts_usercp");
function faveposts_usercp() {
	global $db, $mybb, $templates, $header, $footer, $headerinclude, $faveposts_bit, $faveposts_nav, $folder_bit, $lang, $usercpnav;
	$lang->load('faveposts');
	$uid = $mybb->user['uid'];
    $mybb->input['action'] = $mybb->get_input('action');

    // build navigation 
    $folder_bit = "";
    $query = $db->simple_select("faveposts_dirs", "title,fpdid", "uid = '{$mybb->user['uid']}'");
    // list folders
    while($folder = $db->fetch_array($query)) {
        eval("\$folder_bit = \"".$templates->get("usercp_faveposts_nav_folders")."\";");  
    }
    eval("\$faveposts_nav = \"".$templates->get("usercp_faveposts_nav")."\";");
    

	if($mybb->input['action'] == "faveposts") {
        $folder = $mybb->input['folder'];
        $faveposts_bit = "";

        // include posts without folder
        $fquery = "";
        if(isset($folder)) {
            $fquery = "AND fpdid = {$folder}";
        }
		
        // multipage
        $query = $db->simple_select("faveposts", "COUNT(*) AS numfaves", "uid = '{$uid}'" . $fquery);
        $favescount = $db->fetch_field($query, "numfaves");
        $perpage = 10;
        $page = intval($mybb->input['page']);
        if($page) {
            $start = ($page-1) *$perpage;
        }
        else {
            $start = 0;
            $page = 1;
        }
        $end = $start + $perpage;
        $lower = $start+1;
        $upper = $end;
        if($upper > $favescount) {
            $upper = $favescount;
        }

        // list saved posts
        $multipage = multipage($favescount, $perpage, $page, $_SERVER['PHP_SELF']."?action=faveposts");
        $sql = "SELECT * FROM ".TABLE_PREFIX."faveposts WHERE uid = '{$uid}' " . $fquery . " ORDER BY timestamp ASC LIMIT $start, $perpage";
        $query = $db->query($sql);
        while($favepost = $db->fetch_array($query)) {
            if($favepost['fpdid'] == 0) {
                $foldertitle = "Allgemein";
            } else {
                $foldertitle = $db->fetch_field($db->simple_select("faveposts_dirs", "title", "fpdid = '{$favepost['fpdid']}'"), "title");
            }
            $savedate = date("d.m.Y", $favepost['timestamp']);
            $pid = $favepost['pid'];
            $post = get_post($pid);
            eval("\$faveposts_bit .= \"".$templates->get("usercp_faveposts_bit")."\";");
        }
            
        eval("\$page = \"".$templates->get("usercp_faveposts")."\";");
        output_page($page);  		
    }

    # TODO: edit faveposts
    
    if($mybb->input['action'] == "favefolders") {
        eval("\$page = \"".$templates->get("usercp_favefolders")."\";");
        output_page($page);  
    }

    // create new folders
    if($mybb->input['action'] == "do_favefolders") {
        $insert_array = [
            "uid" => $mybb->user['uid'],
            "title" => $db->escape_string($mybb->get_input('title'))
        ];
        $db->insert_query("faveposts_dirs", $insert_array);

        redirect("usercp.php?action=favefolders");
    }
    
    // delete folder...
    if($mybb->input['action'] == "del_favefolders") {
        $fpdid = $mybb->get_input('fpdid');
        $fpduid = $db->fetch_field($db->simple_select("faveposts_dirs", "uid", "fpdid = '{$fpdid}'"), "uid");

        // ...only if it's your own
        if($mybb->user['uid'] == $fpduid) {
            $db->delete_query("faveposts_dirs", "fpdid = '{$fpdid}'");
        } else { error_no_permission(); }

        redirect("usercp.php?action=favefolders");
    }
    
    // edit folder...
    if($mybb->input['action'] == "edit_favefolders") {
        $fpdid = $mybb->get_input('fpdid');
        $fpduid = $db->fetch_field($db->simple_select("faveposts_dirs", "uid", "fpdid = '{$fpdid}'"), "uid");

        // ...only if it's your own
        if($mybb->user['uid'] == $fpduid) {
            $insert_array = [
                "title" => $db->escape_string($mybb->get_input('title'))
            ];
            $db->update_query("faveposts_dirs", $insert_array, "fpdid = '{$fpdid}'");
        } else { error_no_permission(); }

        redirect("usercp.php?action=favefolders");
    }
}
?>