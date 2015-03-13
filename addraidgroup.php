<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, info@raidpoints.net
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: addraidgroup.php,v 1.1 2006/09/23 20:17:27 garrett Exp $
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

class Add_RaidGroup extends EQdkp_Admin
{
    var $raidgroup     = array();            // Holds raidgroup data if URI_RAIDGROUP is set          @var raid
    var $old_raidgroup = array();            // Holds raidgroup data from before POST                 @var old_raid

    function Add_RaidGroup()
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        parent::eqdkp_admin();

        $this->setRaidGroup();

        // Vars used to confirm deletion
        $this->set_vars(array(
            'confirm_text'  => $user->lang['confirm_delete_raidgroup'],
            'uri_parameter' => URI_RAIDGROUP)
        );

        $this->assoc_buttons(array(
            'add' => array(
                'name'    => 'add',
                'process' => 'process_add',
                'check'   => 'a_raid_add'),
            'update' => array(
                'name'    => 'update',
                'process' => 'process_update',
                'check'   => 'a_raid_upd'),
            'delete' => array(
                'name'    => 'delete',
                'process' => 'process_delete',
                'check'   => 'a_raid_del'),
            'form' => array(
                'name'    => '',
                'process' => 'display_form',
                'check'   => 'a_raid_'))
        );

        // Build the raid array
        // ---------------------------------------------------------
        if ( $this->url_id )
        {
            $sql = "SELECT *
                    FROM __raidgroups_raidgroups
                    WHERE (`raidgroup_id`='" . $this->url_id . "')";
            $result = $db->query($sql);
            if ( !$row = $db->fetch_record($result) )
            {
                message_die($user->lang['error_invalid_raidgroup_provided']);
            }
            $db->free_result($result);

            $this->setRaidGroup($row);
        }
    }

    function error_check()
    {
        global $user, $in;

		$this->fv->is_filled('raidgroup_name', $user->lang['fv_required_raidgroup_name']);

        if ( !$in->exists('raidgroup_raid_ids') )  {
            $this->fv->errors['raidgroup_raid_ids'] = $user->lang['fv_required_raidgroup_raid_ids'];
        }

		if ( $this->is_duplicate_name($in->get('raidgroup_name'), $this->url_id) && !$in->exists('delete') ) {
            $this->fv->errors['raidgroup_name'] = $user->lang['fv_duplicate_raidgroup_name'];
		}

        return $this->fv->is_error();
    }

    /**
     * Process Add
     */
    function process_add()
    {
        global $db, $user, $in;

        $success_message = '';

        //
        // Insert the raid group
        //
		$raidgroup_name = $in->get('raidgroup_name');

    	$sql = "SELECT MAX(raidgroup_display_order) 
		          FROM __raidgroups_raidgroups";
		$display_order = $db->query_first($sql);

		$display = ( $in->get('raidgroup_display') == "Y" ) ? "Y" : "N";
        $query = $db->build_query('INSERT', array(
            'raidgroup_name'  			=> $db->escape($raidgroup_name),
            'raidgroup_raid_ids' 		=> serialize($in->getArray('raidgroup_raid_ids','int')),
            'raidgroup_display' 		=> $display,
            'raidgroup_display_order' 	=> ($display_order + 1),
            'raidgroup_added_by' 		=> $this->admin_user,
        ));
        $db->query("INSERT INTO __raidgroups_raidgroups " . $query);

        //
        // Logging
        //
		$this->setRaidGroup();

        $log_action = array(
            'header'         			=> '{L_ACTION_RAIDGROUP_ADDED}', //gehLOG
            '{L_NAME}'   				=> $db->escape($raidgroup_name),
            '{L_RAIDGROUP_RAID_NAMES}' 	=> $this->raidgroup['raidgroup_raid_names'],
            '{L_RAIDGROUP_DISPLAY}' 	=> $this->raidgroup['raidgroup_display'],
            '{L_ADDED_BY}'   			=> $this->admin_user);

        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_add_raidgroup_success'], $raidgroup_name);
        $link_list = array(
            $user->lang['add_raidgroup']	=> 'addraidgroup.php',
            $user->lang['list_raidgroups']  => 'index.php');
        $this->admin_die($success_message, $link_list);

    }

	/**
	 * checks for an existing raidgroup name,
	 * @param $rg_name string
	 */
	function is_duplicate_name ($rg_name, $rg_id) {
        global $db, $eqdkp, $user, $tpl, $pm;

        $sql = "SELECT raidgroup_id 
		          FROM __raidgroups_raidgroups 
				 WHERE (`raidgroup_name` = '" . $db->escape($rg_name) . "')";
        $raidgroup_id = $db->query_first($sql);

        // Error out if member name exists
        if ( isset($raidgroup_id) && $raidgroup_id != $rg_id ) {
			return true;
		} else {
			return false;
		}
	}
    // ---------------------------------------------------------
    // Process Update
    // ---------------------------------------------------------
    function process_update()
    {
        global $db, $user, $in;

        //
        // Get the old data
        //
        $this->get_old_data();

        //
        // Update the raid
        //
        $query = $db->build_query('UPDATE', array(
            'raidgroup_name'		=> $in->get('raidgroup_name'),
            'raidgroup_raid_ids'    => serialize($in->getArray('raidgroup_raid_ids','int')),
            'raidgroup_display'     => ($in->get('raidgroup_display') == "Y" ) ? "Y" : "N",
            'raidgroup_updated_by' 	=> $this->admin_user)
        );
        $db->query("UPDATE __raidgroups_raidgroups SET " . $query . " WHERE (`raidgroup_id` = '" . $this->url_id . "')");

		// -----------------------
		// Get logging information
		// -----------------------

		$raid_names_array = $this->get_raid_names($in->getArray('raidgroup_raid_ids','int'));

        //
        // Logging
        //
		$display = $in->get('raidgroup_display') == "Y" ? "Y" : "N";

        $log_action = array(
            'header'               		=> '{L_ACTION_RAIDGROUP_UPDATED}', //gehLOG
            'id'                   		=> $this->url_id,
            '{L_NAME_BEFORE}' 			=> $this->old_raidgroup['raidgroup_name'],
            '{L_RG_RAID_NAMES_BEFORE}' 	=> $this->old_raidgroup['raidgroup_raid_names'],
            '{L_RG_DISPLAY_BEFORE}'		=> $this->old_raidgroup['raidgroup_display'],
            '{L_NAME_AFTER}'			=> $this->find_difference($this->old_raidgroup['raidgroup_name'], $in->get('raidgroup_name')),
            '{L_RG_RAID_NAMES_AFTER}' 	=> implode(", ", $this->find_difference(explode(", ",stripslashes($this->old_raidgroup['raidgroup_raid_names'])),explode(", ",sanitize($raid_names_array)))),
            '{L_RG_DISPLAY_AFTER}'		=> $this->find_difference($this->old_raidgroup['raidgroup_display'],$display),
            '{L_UPDATED_BY}'       		=> $this->admin_user);
        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf($user->lang['admin_update_raidgroup_success'], $in->get('raidgroup_name'));
        $link_list = array(
            $user->lang['add_raidgroup']	=> 'addraidgroup.php',
            $user->lang['list_raidgroups']  => 'index.php');
        $this->admin_die($success_message, $link_list);

    }

    // ---------------------------------------------------------
    // Process Delete (confirmed)
    // ---------------------------------------------------------
    function process_confirm()
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        //
        // Get the old data
        //
        $this->get_old_data();

        //
        // Remove the raid group itself
        //
        $db->query("DELETE FROM __raidgroups_raidgroups WHERE (`raidgroup_id` = '" . $this->url_id . "')");

        //
        // Logging
        //
        $log_action = array(
            'header'        			=> '{L_ACTION_RAIDGROUP_DELETED}', //gehLANG
            'id'            			=> $this->url_id,
            '{L_NAME}' 					=> $this->old_raidgroup['raidgroup_name'],
            '{L_RAIDGROUP}' 			=> $this->old_raidgroup['raidgroup_name'],
            '{L_RAIDGROUP_RAID_NAMES}' 	=> $this->old_raidgroup['raidgroup_raid_names'],
            '{L_RAIDGROUP_DISPLAY}'   	=> $this->old_raidgroup['raidgroup_display'],
		);

        $this->log_insert(array(
            'log_type'   => $log_action['header'],
            'log_action' => $log_action)
        );

        //
        // Success message
        //
        $success_message = sprintf ($user->lang['admin_delete_raidgroup_success'],$this->old_raidgroup['raidgroup_name']); //gehLANG
        $link_list = array(
            $user->lang['add_raidgroup']   => 'addraidgroup.php',
            $user->lang['list_raidgroups'] => 'index.php');
        $this->admin_die($success_message, $link_list);
    }

    // ---------------------------------------------------------
    // Process helper methods
    // ---------------------------------------------------------
    /**
    * Populate the old_raid array
    */
    function get_old_data()
    {
        global $db;

        $sql = "SELECT *
                FROM __raidgroups_raidgroups
                WHERE (`raidgroup_id` = '" . $this->url_id . "')";
        $result = $db->query($sql);

        while ( $row = $db->fetch_record($result) )
        {
            $this->old_raidgroup = array(
                'raidgroup_name'     	=> $row['raidgroup_name'],
                'raidgroup_raid_ids' 	=> unserialize($row['raidgroup_raid_ids']),
                'raidgroup_display'  	=> $row['raidgroup_display'],
            );

			$this->old_raidgroup['raidgroup_raid_names'] = $this->get_raid_names($this->old_raidgroup['raidgroup_raid_ids']);
        }
        $db->free_result($result);
    }

    // ---------------------------------------------------------
    // Display form
    // ---------------------------------------------------------
    function display_form()
    {
        global $db, $eqdkp, $user, $tpl, $pm;

        //
        // Build list of available events (we use events until thundarr issues EQDKP 1.4)
        //
        $sql = "SELECT DISTINCT (event_name), event_id
                FROM __events
                ORDER BY event_name";
        $result = $db->query($sql);

        while ( $row = $db->fetch_record($result) ) {

			if (isset($this->raidgroup['raidgroup_raid_ids']) && !empty($this->raidgroup['raidgroup_raid_ids'])) {
				$selected = ( in_array($row['event_id'], $this->raidgroup['raidgroup_raid_ids']) ) ? 'selected="selected"' : '';
			} else {
				$selected = '';
			}

            $tpl->assign_block_vars('raids_row', array(
                'VALUE'  => $row['event_id'],
                'SELECTED' => $selected,
                'OPTION' => stripslashes($row['event_name']))
            );
        }
        $db->free_result($result);

        $tpl->assign_vars(array(
            // Form vars
            'F_ADD_RAIDGROUP'       => 'addraidgroup.php',
            'RAIDGROUP_ID'          => $this->url_id,
            'RAIDGROUP_NAME'		=> $this->raidgroup['raidgroup_name'],
            'RAIDGROUP_DISPLAY'		=> ( $this->raidgroup['raidgroup_display'] == "Y" ) ? 'checked="checked"' : "",
			'URI_RAIDGROUP'			=> URI_RAIDGROUP,

            // Language
            'L_ADD_RAIDGROUP_TITLE' => $user->lang['addraidgroup_title'],
            'L_RESET'               => $user->lang['reset'],
			'L_NAME'				=> $user->lang['name'],
			'L_RAID_NAMES'			=> $user->lang['raidgroup_raid_names'],
			'L_DISPLAY'				=> $user->lang['raidgroup_display'],

			// Button Lables
            'L_ADD_RAIDGROUP'       => $user->lang['add_raidgroup'],
            'L_UPDATE_RAIDGROUP'    => $user->lang['update_raidgroup'],
            'L_DELETE_RAIDGROUP'    => $user->lang['delete_raidgroup'],

            // Form validation
            'FV_RAIDGROUP_NAME'     => $this->fv->generate_error('raidgroup_name'),
            'FV_RAIDGROUP_RAID_IDS' => $this->fv->generate_error('raidgroup_raid_ids'),

            // Buttons
            'S_ADD' => ( !$this->url_id ) ? true : false)
        );

		$eqdkp->set_vars(array(
			'page_title'    => page_title($user->lang['is_title_raidgroups']),
			'template_path' => $pm->get_data('raidgroups', 'template_path'),
			'template_file' => 'addraidgroup.html',
			'display'       => true)
		);
    }

	function setRaidGroup($row = '') {
        global $in;

		if (is_array($row) ){
			$display = ( $row['raidgroup_display'] == "Y" ) ? "Y" : "N";
			$this->raidgroup = array(
				'raidgroup_name'		=> $row['raidgroup_name'],
				'raidgroup_raid_ids'  	=> is_array($row['raidgroup_raid_ids']) ? $row['raidgroup_raid_ids'] : unserialize($row['raidgroup_raid_ids']),
				'raidgroup_display'		=> $display,
			);
	
			if (!empty($this->raidgroup['raidgroup_raid_ids'])) {
				$this->raidgroup['raidgroup_raid_names'] = $this->get_raid_names($this->raidgroup['raidgroup_raid_ids']);
			}
		} else {
			$display = ( $in->getArray('raidgroup_display','int') == "Y" ) ? "Y" : "N";
			$this->raidgroup = array(
				'raidgroup_name'		=> $in->get('raidgroup_name'),
				'raidgroup_raid_ids'  	=> is_array($in->getArray('raidgroup_raid_ids','int')) ? $in->getArray('raidgroup_raid_ids','int') : unserialize($in->get('raidgroup_raid_ids')),
				'raidgroup_display'		=> $display,
			);
	
			if (!empty($this->raidgroup['raidgroup_raid_ids'])) {
				$this->raidgroup['raidgroup_raid_names'] = $this->get_raid_names($this->raidgroup['raidgroup_raid_ids']);
			}
		}
	}

	function get_raid_names ($raid_ids = array()) {
        global $db;

		//
		// raid_ids are nice but names are so much more useful. get them now!
		//
		$in_clause = "('".implode("','",$raid_ids)."')";

		$sql = "SELECT DISTINCT(event_name)
				  FROM __events
				 WHERE (`event_id` IN ".$in_clause.")";
		$event_results = $db->query($sql);

		while( $event_row = $db->fetch_record($event_results) ) {
		  $event_names[] = $event_row['event_name'];
		}

		return implode(", ",$event_names);
	}
}

$Add_RaidGroup = new Add_RaidGroup;
$Add_RaidGroup->process();

?>