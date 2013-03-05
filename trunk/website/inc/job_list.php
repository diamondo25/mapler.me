<?php
$job_names = array(
	0   => 'Beginner',

	100 => 'Warrior',
		110 => 'Fighter',
		111 => 'Crusader',
		112 => 'Hero',

		120 => 'Page',
		121 => 'White Knight',
		122 => 'Paladin',

		130 => 'Spearman',
		131 => 'Dragon Knight',
		132 => 'Dark Knight',

	200 => 'Magician',
		211 => 'F/P Mage',
		210 => 'F/P Wizard',
		212 => 'F/P Arch Mage',

		221 => 'I/L Mage',
		220 => 'I/L Wizard',
		222 => 'I/L Arch Mage',

		230 => 'Cleric',
		231 => 'Priest',
		232 => 'Bishop',

	300 => 'Bowman',
		310 => 'Hunter',
		311 => 'Ranger',
		312 => 'Bow Master',

		320 => 'Crossbowman',
		321 => 'Sniper',
		322 => 'Crossbow Master',

	400 => 'Thief',
		410 => 'Assassin',
		411 => 'Hermit',
		412 => 'Night Lord',

		420 => 'Bandit',
		421 => 'Chief Bandit',
		422 => 'Shadower',

		430 => 'Blade Recruit',
		431 => 'Blade Acolyte',
		432 => 'Blade Specialist',
		433 => 'Blade Lord',
		434 => 'Blade Master',

	500 => 'Pirate',
		510 => 'Brawler',
		511 => 'Marauder',
		512 => 'Buccaneer',
		
		520 => 'Gunslinger',
		521 => 'Outlaw',
		522 => 'Corsair',

		501 => 'Cannoneer',
		530 => 'Cannoneer',
		531 => 'Cannoneer',
		532 => 'Cannoneer',

	508 => 'Jett',
	570 => 'Jett',
	571 => 'Jett',
	572 => 'Jett',

	800 => 'Manager',

	900 => 'GM',
	910 => 'Super GM',


	1000 => 'Noblesse',
	1100 => 'Dawn Warrior',
	1110 => 'Dawn Warrior',
	1111 => 'Dawn Warrior',
	1112 => 'Dawn Warrior', // 4th job does have a 'skill tab', but no skills

	1200 => 'Blaze Wizard',
	1210 => 'Blaze Wizard',
	1211 => 'Blaze Wizard',
	1212 => 'Blaze Wizard', // 4th job does have a 'skill tab', but no skills

	1300 => 'Wind Archer',
	1310 => 'Wind Archer',
	1311 => 'Wind Archer',
	1312 => 'Wind Archer', // 4th job does have a 'skill tab', but no skills

	1400 => 'Night Walker',
	1410 => 'Night Walker',
	1411 => 'Night Walker',
	1412 => 'Night Walker', // 4th job does have a 'skill tab', but no skills

	1500 => 'Thunder Breaker',
	1510 => 'Thunder Breaker',
	1511 => 'Thunder Breaker',
	1512 => 'Thunder Breaker', // 4th job does have a 'skill tab', but no skills

	2000 => 'Legend',
	2100 => 'Aran',
	2110 => 'Aran',
	2111 => 'Aran',
	2112 => 'Aran',

	2001 => 'Evan',
	2200 => 'Evan',
	2210 => 'Evan',
	2211 => 'Evan',
	2212 => 'Evan',
	2213 => 'Evan',
	2214 => 'Evan',
	2215 => 'Evan',
	2216 => 'Evan',
	2217 => 'Evan',
	2218 => 'Evan',

	2002 => 'Mercedes',
	2300 => 'Mercedes',
	2310 => 'Mercedes',
	2311 => 'Mercedes',
	2312 => 'Mercedes',

	2003 => 'Phantom',
	2400 => 'Phantom',
	2410 => 'Phantom',
	2411 => 'Phantom',
	2412 => 'Phantom',


	2700 => 'Luminous',
	2710 => 'Luminous',
	2711 => 'Luminous',
	2712 => 'Luminous',

	3000 => 'Citizen',
	3001 => 'Demon Slayer',
	3002 => 'Xenon',
	
	3100 => 'Demon Slayer',
	3101 => 'Demon Avenger',
	
	3110 => 'Demon Slayer',
	3111 => 'Demon Slayer',
	3112 => 'Demon Slayer',
	
	3120 => 'Demon Avenger',
	3121 => 'Demon Avenger',
	3122 => 'Demon Avenger',
	
	3200 => 'Battle Mage',
	3210 => 'Battle Mage',
	3211 => 'Battle Mage',
	3212 => 'Battle Mage',

	3300 => 'Wild Hunter',
	3310 => 'Wild Hunter',
	3311 => 'Wild Hunter',
	3312 => 'Wild Hunter',

	3500 => 'Mechanic',
	3510 => 'Mechanic',
	3511 => 'Mechanic',
	3512 => 'Mechanic',
	
	3600 => 'Xenon',
	3610 => 'Xenon',
	3611 => 'Xenon',
	3612 => 'Xenon',
	
	4002 => 'Kanna',
	4200 => 'Kanna',
	4210 => 'Kanna',
	4211 => 'Kanna',
	4212 => 'Kanna',
	
	5000 => 'Mihile',
	5100 => 'Mihile',
	5110 => 'Mihile',
	5111 => 'Mihile',
	5112 => 'Mihile',

	6000 => 'Kaiser',
	6100 => 'Kaiser',
	6110 => 'Kaiser',
	6111 => 'Kaiser',
	6112 => 'Kaiser',

	6001 => 'Angelic Buster',
	6500 => 'Angelic Buster',
	6510 => 'Angelic Buster',
	6511 => 'Angelic Buster',
	6512 => 'Angelic Buster',

	//Additional Jobs + Special

	1337 => 'System' // WAT
);

function GetJobname($id) {
	global $job_names;
	if (!isset($job_names[$id])) return 'Unknown ('.$id.')';
	return $job_names[$id];
}


function IsRealJob($id) { // Beginner and mining etc is not a real job! :@
	switch ($id) {
		case 0:
		case 1000:
		
		case 2000:
		case 2001:
		case 2002:
		case 2003:
		case 2004:
		
		case 3000:
		case 3001:
		case 3002:
		
		case 4002:
		
		case 5000:
		
		case 6000:
		case 6001:
		
		
		// All non-jobs
		case 7000:
		case 7100:
		
		case 8000:
		
		case 9000:
		case 9100:
		case 9200:
		case 9201:
		case 9202:
		case 9203:
		case 9204:
		
		
			return false;
	}
	
	return true;
}
