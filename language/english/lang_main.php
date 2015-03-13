<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, info@raidpoints.net
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: lang_main.php,v 1.1 2006/09/23 20:17:27 garrett Exp $
 *
 ******************************/

if ( !defined('EQDKP_INC') )
{
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Initialize the language array if it isn't already
if (empty($lang) || !is_array($lang))
{
    $lang = array();
}

// %1\$<type> prevents a possible error in strings caused
//      by another language re-ordering the variables
// $s is a string, $d is an integer, $f is a float

$lang = array_merge($lang, array(
    // Page Titles
    'is_title_raidgroups' => "Raid Groups",
    'addraidgroup_title' =>"Add a Raid Group",

    // Labels
    'raidgroup'                 =>"Raid Group",
    'raidgroups'                =>"Raid Groups",
    'raidgroup_raid_names'      =>"Raid Names",
    'raidgroup_display'         =>"Display",
    'raidgroup_display_order'   =>"Order",
    'add_raidgroup'             =>"Add Raid Group",
    'update_raidgroup'          =>"Update Raid Group",
    'delete_raidgroup'          =>"Delete Raid Group",
    // Log Actions
    'action_raidgroup_added'    =>    "Raid Group Added",
    'action_raidgroup_deleted'  =>    "Raid Group Deleted",
    'action_raidgroup_updated'  =>    "Raid Group Updated",
    'rg_raid_names_before'      =>    "Raid Names Before",
    'rg_display_before'         =>    "Raid Names Before",
    'rg_raid_names_after'       =>    "Raid Names After",
    'rg_display_after'          =>    "Display After",

    // Log Messages
    'vlog_raidgroup_added'      =>"%1\$s added the raid group '%2\$s'.",
    'vlog_raidgroup_deleted'    =>"%1\$s deleted the raid group '%2\$s'.",
    'vlog_raidgroup_updated'    =>"%1\$s updated the raid group '%2\$s'.",

    // Success / Error Message
    'admin_add_raidgroup_success'   	=>"The raidgroup <strong>%1\$s</strong> has been added to the database for your guild.",
    'admin_delete_raidgroup_success' 	=>"The raidgroup <strong>%1\$s</strong> has been deleted from the database for your guild.",
    'admin_update_raidgroup_success' 	=>"The raidgroup <strong>%1\$s</strong> has been updated in the database for your guild.",
    'confirm_delete_raidgroup'      	=>"Are you sure you want to delete this raidgroup?",
    'admin_duplicate_raidgroup'         =>"Failed to add %1\$s; raid group exists as ID %2\$d",
    'error_invalid_raidgroup_provided' 	=>"Cannot find RaidGroup",

    // Form validation messages
    'fv_required_raidgroup_name'        =>"You must specify a name",
    'fv_required_raidgroup_raid_ids' 	=>"You must select at least one raid",
    'fv_duplicate_raidgroup_name'   	=>"RaidGroup name already exists, pick another name",

    // Links
    'add_raidgroup'      =>"Add Raid Group",
    'list_raidgroups'    =>"List Raid Groups",
    'up'                 =>"up",
    'down'               =>"down",
));
