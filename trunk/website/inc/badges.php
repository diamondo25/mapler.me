<?php
$badges = array(

	//general badges
	0 => 'Black Wing', //BANHAMMER B1TCHZ
	1 => 'Mapler', //given to all members of the site.
	2 => 'Patriot', //member for one year
	27 => 'Employee', //Mapler.me Staff (Honorary Employee Medal)
	
	//adding characters
	3 => 'The First Step', //adding a single character
	4 => 'New Collector', //adding three characters
	5 => 'Renowned Collector', //adding six characters
	6 => 'Amazing Collector', //adding twelve characters
	7 => 'Master Collector', //adding eightteen characters
	
	//various
	8 => 'Donor', //donated to Mapler.me
	9 => 'Beta Tester', //participated in the Beta Testing
	10 => 'Developer', //Registered Developer
	26 => 'A Hero, No More', //Recieved an infraction or reported by staff for abuse.
	
	//social
	11 => 'Forever Alone', //No friends added after a month of registering.
	12 => 'Can has friend?', //Followed someone for the first time.
	13 => 'It begins.', //Someone followed you!
	14 => 'Eyes on Me', //followed over a hundred people.
	15 => 'The Hotness', //Has over a hundred followers.
	16 => 'Celebrity', //OMG UBER HAX.. I mean, over 500 followers.
	17 => 'Can\'t Live Alone', //Married to someone.
	18 => 'Placeholder', //derp
	19 => 'Placeholder', //hurp
	20 => 'Placeholder', //hurr
	
	//level accomplishments
	21 => 'Beginner', //have a character below level 10.
	22 => 'Junior', //level 30
	23 => 'Veteran', //level 60
	24 => 'Magnificent', //level 100
	25 => 'The Next Legend', //level 200
	
	//events + special medals
	500 => 'Socialite 2013', //website event: person with the most followers by the end of {time period}
	501 => 'Maple Idol', // Content. Players can vote who is the 'Maple Idol' of Mapler.me.

	//Medals to remember: Hawk's Eyes, Time Traveler, Ask Me Anything, Clairvoyant, (No Pain, No Game), 
);

function GetBadges($id) {
	global $badges;
	if (!isset($badges[$id])) return '???';
	return $badges[$id];
}
?>