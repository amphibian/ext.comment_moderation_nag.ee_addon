<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Comment_moderation_nag
{
	var $settings        = array();
	var $name            = 'Comment Moderation Nag';
	var $version         = '1.0.2';
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
		
		if($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		// First, check to see if we have permission to moderate comments
		if($DSP->allowed_group('can_moderate_comments'))
		{
			// Next, make sure we're not already on the moderation screen
			if(isset($_GET['M']) && $_GET['M'] == 'view_comments' 
				&& isset($_GET['validate']) && $_GET['validate'] == 1)
				return $out;
				
			// Only proceed if the Comment module is actually insatlled
			$module_check = $DB->query("SELECT module_name FROM exp_modules WHERE module_name = 'Comment'");			
			if($module_check->num_rows > 0)
			{
				$query = $DB->query("
					SELECT count(comment_id) AS count 
					FROM exp_comments 
					WHERE status = 'c' 
					AND site_id = '".$DB->escape_str($PREFS->ini('site_id')) ."'
				");
				$total = $query->row['count'];
				
				// Lastly, only display the notice if there are comments to moderate
				if($total)
				{	
					$LANG->fetch_language_file('comment_moderation_nag');
					$c_word = ($total == 1) ? $LANG->line('comment_requires') : $LANG->line('comments_require');
					$find= "<div id='contentNB'>";
					$replace = "
					<div id='contentNB'>
					<div class='box'>
						<span class='highlight_bold'>".$total." ".$c_word." ".$LANG->line('moderation').". </span>
						<a href='".BASE.AMP."C=edit&amp;M=view_comments&amp;validate=1'>".
						$LANG->line('click_to_moderate').
						".</a>
						</span>
					</div>
					";
					$out = str_replace($find, $replace, $out);
				}
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
			        'settings'     => '',
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