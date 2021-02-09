<?php
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

$plugins->add_hook("postbit", "faveposts_postbit");
$plugins->add_hook("misc_start", "faveposts_misc");
$plugins->add_hook("usercp_start", "faveposts_usercp");

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
  <div class="favepostpopup">Folgt</div><a href="#closepop" class="closepop"></a>
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

	if($db->table_exists("faveposts"))
  	{
  		$db->drop_table("faveposts");
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

function faveposts_postbit(&$post)
{
	global $lang, $templates, $db, $mybb;
    $lang->load('faveposts');
    $uid = (int)$mybb->user['uid'];
    $pid = (int)$post['pid'];
    $sql = "SELECT * FROM ".TABLE_PREFIX."faveposts WHERE uid = '{$uid}' AND pid = '{$pid}'";
    $query = $db->query($sql);
    if(mysqli_num_rows($query) > 0) {
        $post['faveposts'] = eval($templates->render("postbit_unfaveposts"));
    } else { $post['faveposts'] = eval($templates->render("postbit_faveposts")); }
	return $post;
}

function faveposts_misc() {
	
	global $db, $mybb;

	$mybb->input['action'] = $mybb->get_input('action');	
	
	if($mybb->input['action'] == "faveposts") {
		$pid = $mybb->get_input('pid');
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

function faveposts_usercp() {
	global $db, $mybb, $templates,$header, $footer, $headerinclude, $faveposts_bit, $lang;
	#$lang->load('faveposts');
	$uid = $mybb->user['uid'];
	$mybb->input['action'] = $mybb->get_input('action');
	if($mybb->input['action'] == "faveposts") {
		$faveposts_bit = "";
		
    // Multipage
	$query = $db->simple_select("faveposts", "COUNT(*) AS numfaves", "uid = '{$uid}'");
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

    $multipage = multipage($favescount, $perpage, $page, $_SERVER['PHP_SELF']."?action=faveposts");
	$sql = "SELECT * FROM ".TABLE_PREFIX."faveposts WHERE uid = '{$uid}' ORDER BY timestamp ASC LIMIT $start, $perpage";
    $query = $db->query($sql);
    while($favepost = $db->fetch_array($query)) {
        $savedate = date("d.m.Y", $favepost['timestamp']);
		$pid = $favepost['pid'];
		$post = get_post($pid);
        eval("\$faveposts_bit .= \"".$templates->get("usercp_faveposts_bit")."\";");
    }
		
	eval("\$page = \"".$templates->get("usercp_faveposts")."\";");
    output_page($page);  
		
	}
	
}
?>