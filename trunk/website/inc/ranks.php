<?php

// Members
define("RANK_PERMABANNED", -200);
define("RANK_BANNED", -100);
define("RANK_AWAITING_ACTIVATION", 0);
define("RANK_NORMAL", 100);

// Special
define("RANK_PLUS", 200); //Membership
define("RANK_DEVELOPER", 300);

// Staff
define("RANK_MODERATOR", 900);
define("RANK_NEXON", 950);
define("RANK_ADMIN", 1000);

$ranks = array(
	-200   => 'Permanently Banned',
	-100 => 'Banned',
	
	0 => 'Mapler', // displays as member even though awaiting activation.
	100 => 'Mapler',
	
	200 => 'Mapler+',
	300 => 'Mapler+', // mapler plus includes developer status and access?
	
	900 => 'Staff',
	950 => 'Nexon',
	1000 => 'Staff'
);

function GetRankTitle($rank) {
	global $ranks;
	if (!isset($ranks[$rank])) return 'Mapler'; //Mapler instead of "Unknown".
	return $ranks[$rank];
}
?>