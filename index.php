<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, info@raidpoints.net
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: index.php,v 1.1 2006/09/23 20:17:27 garrett Exp $
 *
 ******************************/

// EQdkp required files/vars
define('EQDKP_INC', true);
define('IN_ADMIN', true);
define('PLUGIN', 'raidgroups');
$eqdkp_root_path = './../../';
require_once($eqdkp_root_path . 'common.php');
require_once('settings.php');

$raidgroups = $pm->get_plugin('raidgroups');

if ( !$pm->check(PLUGIN_INSTALLED, 'raidgroups') )
{
    message_die('The Raid Groups plugin is not installed.');
}

class List_RaidGroup extends EQdkp_Admin
{
    function List_RaidGroup()
    {
        parent::eqdkp_admin();
        
        $this->assoc_buttons(array(
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_'),
            'display_order' => array(
                'name'    => 'display_order',
                'process' => 'process_display_order',
                'check'   => 'a_raid_')
        ));
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function process_display_order()
    {
        global $db, $in;
		
		switch ($_POST['display_order']) {
			case "up":
				// Move the raidgroup above (i.e. a lesser display_order value) me down one spot 
				$query = $db->build_query('UPDATE', array(
					'raidgroup_display_order'   => $in->get('raidgroup_display_order','int'),
					'raidgroup_updated_by' 		=> $this->admin_user)
				);
				$db->query("UPDATE __raidgroups_raidgroups SET " . $query . " WHERE (`raidgroup_display_order` = '" . ( $in->get('raidgroup_display_order') - 1 )."')");

				// Resquence the raidgroup moving up
				$query = $db->build_query('UPDATE', array(
					'raidgroup_display_order'   => ( ($in->get('raidgroup_display_order','int')) - 1 ),
					'raidgroup_updated_by' 		=> $this->admin_user)
				);
				$db->query("UPDATE __raidgroups_raidgroups SET " . $query . " WHERE (`raidgroup_id` = '" . $in->get('raidgroup_id') ."')");
				break;
			
			case "down":
				// Move the raidgroup below me (i.e. a greater display_order value) up one spot 
				$query = $db->build_query('UPDATE', array(
					'raidgroup_display_order'   => $in->get('raidgroup_display_order','int'),
					'raidgroup_updated_by' 		=> $this->admin_user)
				);
				$db->query("UPDATE __raidgroups_raidgroups SET " . $query . " WHERE (`raidgroup_display_order` = '" . ( ($in->get('raidgroup_display_order','int')) + 1 )."')");

				// Resquence the raidgroup moving up
				$query = $db->build_query('UPDATE', array(
					'raidgroup_display_order'   => ( ($in->get('raidgroup_display_order','int')) + 1 ),
					'raidgroup_updated_by' 		=> $this->admin_user)
				);
				$db->query("UPDATE __raidgroups_raidgroups SET " . $query . " WHERE (`raidgroup_id` = '" . $in->get('raidgroup_id','int') ."')");
				
				break;
		}
		
		$this->display_form();
		
	}
	
    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        //
        // Build list of raid groups
        //
        $sql = "SELECT *
                FROM __raidgroups_raidgroups
                ORDER BY raidgroup_display_order ASC";
        $raidgroup_result = $db->query($sql);

        while ( $raidgroup_row = $db->fetch_record($raidgroup_result) ) {
			
			//
			// get the min / max display values for managing the up / down button display
			//
			$sql = "SELECT MAX(raidgroup_display_order) as max_display_order
					  FROM __raidgroups_raidgroups";
			$max_display_order = $db->query_first($sql);
			$min_display_order = MIN_AUTO_INCREMENT;

			//
			//  get name of each member raid
			//
			$event_ids = "('".implode("','",unserialize($raidgroup_row['raidgroup_raid_ids']))."')";
			$sql = "SELECT event_name
					  FROM __events
					 WHERE (`event_id` IN " . $event_ids . ")
					ORDER BY event_name";
			$raids_result = $db->query($sql);

			$raid_names = array();
			while ( $raids_row = $db->fetch_record($raids_result) ) {
				$raid_names[] = sanitize($raids_row['event_name']); //gehTODO - need the sanitize?
			}
			$raid_names = implode(", ",$raid_names);
			
            $tpl->assign_block_vars('raidgroups_row', array(
                'RAIDGROUP_ID'				=> $raidgroup_row['raidgroup_id'],
                'RAIDGROUP_NAME' 			=> $raidgroup_row['raidgroup_name'],
                'RAIDGROUP_RAID_NAMES'		=> $raid_names,
                'RAIDGROUP_DISPLAY'			=> ( $raidgroup_row['raidgroup_display'] == 'Y' ) ? $user->lang['yes'] : $user->lang['no'],
                'RAIDGROUP_DISPLAY_ORDER'	=> $raidgroup_row['raidgroup_display_order'],

				'ROW_CLASS'     			=> $eqdkp->switch_row_class(),			
                'U_VIEW_RAIDGROUP'  		=> path_default('plugins/' . $pm->get_data('raidgroups', 'path') . '/addraidgroup.php') . path_params(URI_RAIDGROUP,$raidgroup_row['raidgroup_id']),
				'S_DOWN'					=> ( $raidgroup_row['raidgroup_display_order'] > $min_display_order ) ? true : false,
				'S_UP'						=> ( $raidgroup_row['raidgroup_display_order'] < $max_display_order ) ? true : false,
            ));
        }
        $db->free_result($raidgroup_result);

        $tpl->assign_vars(array(
           
            // Language
			'L_NAME'			=> $user->lang['name'],
			'L_RAID_NAMES'		=> $user->lang['raidgroup_raid_names'],
			'L_DISPLAY'			=> $user->lang['raidgroup_display'], 
			'L_DISPLAY_ORDER'	=> $user->lang['raidgroup_display_order'],
			'L_UP'				=> $user->lang['down'],
			'L_DOWN'			=> $user->lang['up'],
            
        ));
        
		$eqdkp->set_vars(array(
			'page_title'    => page_title($user->lang['is_title_raidgroups']),
			'template_path' => $pm->get_data('raidgroups', 'template_path'),
			'template_file' => 'listraidgroups.html',
			'display'       => true)
		);
    } // end display_form
	
}

$List_RaidGroup = new List_RaidGroup;
$List_RaidGroup->process();
	
?>