<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Comment_moderation_nag
{
	var $settings        = array();
	var $name            = 'Comment Moderation Nag';
	var $version         = '1.0.1';
	var $description     = 'Prominently nags administrators to approve or delete pending comments.';
	var $settings_exist  = 'n';
	var $docs_url        = '';

	
	// -------------------------------
	//   Constructor - Extensions use this for settings
	// -------------------------------
	
	function Comment_moderation_nag($settings='')
	{
	    $this->settings = $settings;
	}
	// END


	function show_full_control_panel_end($out)
	{
		global $EXT, $DB, $PREFS, $LANG, $DSP;
		
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		if(isset($_GET['M']) && $_GET['M'] == 'view_comments' 
			&& isset($_GET['validate']) && $_GET['validate'] == 1)
			return $out;
		
		if ($DSP->allowed_group('can_moderate_comments'))
		{
			$query = $DB->query("
				SELECT count(comment_id) AS count 
				FROM exp_comments 
				WHERE status = 'c' 
				AND site_id = '".$DB->escape_str($PREFS->ini('site_id')) ."'
			");
			$total = $query->row['count'];
			
			if($total)
			{	
				$c_word = ($total == 1) ? ' comment requires' : ' comments require';
				$find= "<div id='contentNB'>";
				$replace = "
				<div id='contentNB'>
				<div class='box'>
					<span class='highlight_bold'>".$total.$c_word." moderation. </span>
					<a href='".BASE.AMP."C=edit&amp;M=view_comments&amp;validate=1'>
					Click here to approve or delete pending comments.</a>
					</span>
				</div>
				";
				$out = str_replace($find, $replace, $out);
			}
		}
		return $out;
	}   
	// END
		
   
	// --------------------------------
	//  Activate Extension
	// --------------------------------
	
	function activate_extension()
	{
	    global $DB;
	    
	    $hooks = array(
	    	'show_full_control_panel_end' => 'show_full_control_panel_end'
	    );
	    
	    foreach($hooks as $hook => $method)
	    {
		    $DB->query($DB->insert_string('exp_extensions',
		    	array(
					'extension_id' => '',
			        'class'        => "Comment_moderation_nag",
			        'method'       => $method,
			        'hook'         => $hook,
			        'settings'     => serialize($defaults),
			        'priority'     => 10,
			        'version'      => $this->version,
			        'enabled'      => "y"
					)
				)
			);	    
	    }
		
	}
	// END


	// --------------------------------
	//  Update Extension
	// --------------------------------  
	
	function update_extension($current='')
	{
	    global $DB;
	    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
	    if ($current < '1.0.1')
	    {
		}
	    
	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = 'Comment_moderation_nag'");
	}
	// END
	
	
	// --------------------------------
	//  Disable Extension
	// --------------------------------
	
	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'Comment_moderation_nag'");
	    
	}
	// END


}
// END CLASS