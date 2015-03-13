<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, info@raidpoints.net
 * Licensed under the GNU GPL.
 * ------------------
 * $Rev$ $Date$
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

class RaidGroups_Plugin_Class extends EQdkp_Plugin
{

    function RaidGroups_plugin_class($pm)
    {
        global $eqdkp_root_path, $user, $db;

        $this->eqdkp_plugin($pm);
        $this->pm->get_language_pack('raidgroups');

		/*
		* Log Events
		*/
		$this->add_log_action('{L_ACTION_RAIDGROUP_ADDED}', $user->lang['action_raidgroup_added']); //gehTODO - Do I need to add the $user->lang[] string here? seemded redundant based on code review of logs.php
		$this->add_log_action('{L_ACTION_RAIDGROUP_DELETED}', $user->lang['action_raidgroup_deleted']);
		$this->add_log_action('{L_ACTION_RAIDGROUP_UPDATED}', $user->lang['action_raidgroup_updated']);

        $this->add_data(array(
            'name'          => 'Raid Groups',
            'code'          => 'raidgroups',
            'path'          => 'raidgroups',
            'contact'       => 'loganfive@blacktower.com',
            'template_path' => 'plugins/raidgroups/templates/',
            'version'       => '1.0.2')
        );

        $this->add_menu('admin_menu', $this->gen_admin_menu());

        // Define installation
		//  - raidgroup_raid_ids is stored using serialize(). always use unserialize() when retrieving
        // -----------------------------------------------------
        $this->add_sql(SQL_INSTALL, "CREATE TABLE IF NOT EXISTS __raidgroups_raidgroups (
										  `raidgroup_id` MEDIUMINT(8) NOT NULL AUTO_INCREMENT,
										  `raidgroup_name` VARCHAR(255) NOT NULL,
										  `raidgroup_raid_ids` VARCHAR(255) NOT NULL,
										  `raidgroup_display` ENUM( 'Y', 'N' ) NOT NULL DEFAULT 'Y',
										  `raidgroup_display_order` SMALLINT(6) NOT NULL,
										  `raidgroup_added_by` VARCHAR(30) NOT NULL,
										  `raidgroup_updated_by` VARCHAR(30),
										  PRIMARY KEY (raidgroup_id));");
	
        // Define uninstallation
        // -----------------------------------------------------
        $this->add_sql(SQL_UNINSTALL, "DROP TABLE IF EXISTS __raidgroups_raidgroups;");
    }

    function gen_admin_menu()
    {
        if ( $this->pm->check(PLUGIN_INSTALLED, 'raidgroups') )
        {
            global $db, $user, $eqdkp;
            $admin_menu = array(
                    'raidgroups' => array(
                    0 => $user->lang['raidgroups'],
                    1 => array('link' => path_default('plugins/' . $this->get_data('path') . '/addraidgroup.php'), 
                               'text' => $user->lang['add'], 
                               'check' => 'a_raid_add'),
                    2 => array('link' => path_default('plugins/' . $this->get_data('path') . '/index.php'),   
                               'text' => $user->lang['list'],  
                               'check' => 'a_raid_'),
                )
             );

            return $admin_menu;
        }
        return;
    }

}
?>