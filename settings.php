<?php
/******************************
 * [EQDKP Plugin] Raid Groups
 * Copyright 2006, Garrett Hunter, loganfive@blacktower.com
 * Licensed under the GNU GPL.
 * ------------------
 * $Id: config.php,v 1.1 2006/09/23 20:17:27 garrett Exp $
 *
 ******************************/

/******** From common.php ************/
// URI Parameters
define('URI_RAIDGROUP', 	'rg');
define('URI_DISPLAY_ORDER', 'do');
define('URI_RAIDGROUP_ID', 	'ri');
define('URI_UP',			1);
define('URI_DOWN',			0);

// Database Table names
define('RG_RAIDGROUPS_TABLE',    ($table_prefix . 'raidgroups_raidgroups'));

// Misc defines
define('MIN_AUTO_INCREMENT',	1); // initial value set by an auto_increment column in MySQL.

?>