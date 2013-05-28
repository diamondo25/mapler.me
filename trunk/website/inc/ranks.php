<?php

// Members
define("RANK_PERMABANNED", -200);
define("RANK_BANNED", -100);
define("RANK_AWAITING_ACTIVATION", 0);
define("RANK_NORMAL", 100);

// Special
define("RANK_PLUS", 200); //Membership
define("RANK_DEVELOPER", 300);
define("RANK_NEXON_VOLUNTEER", 310); //Membership

// Staff
define("RANK_MODERATOR", 900);
define("RANK_NEXON", 950);
define("RANK_ADMIN", 1000);

$_account_ranks = array(
	RANK_PERMABANNED	=> 'Permanently Banned',
	RANK_BANNED 		=> 'Banned',
	
	RANK_AWAITING_ACTIVATION 	=> 'Mapler', // displays as member even though awaiting activation.
	RANK_NORMAL 				=> 'Mapler',
	
	RANK_PLUS 		=> 'Mapler+',
	RANK_DEVELOPER 	=> 'Mapler+', // mapler plus includes developer status and access?
	RANK_NEXON_VOLUNTEER 	=> 'Nexon Volunteer', // volunteers (maryse, grant, ciel, etc)
	
	RANK_MODERATOR 	=> 'Moderator',
	RANK_NEXON 		=> 'Nexon America',
	RANK_ADMIN 		=> 'Team'
);

function GetRankTitle($rank) {
	global $_account_ranks;
	if (!isset($_account_ranks[$rank])) return 'Mapler'; //Mapler instead of "Unknown".
	return $_account_ranks[$rank];
}
?>