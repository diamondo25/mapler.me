<?php


//'default','blazing','bewildered','angry','blink','bowing','cheers','chu','cry','dam','despair','glitter','hit','hot','hum','love','oops','pain','shine','smile','stunned','troubled','vomit','wink'
$avatar_faces = array();
$avatar_faces['default'] = 'Standard';
$avatar_faces['angry'] = 'Angry';
$avatar_faces['blaze'] = 'Flaming';
$avatar_faces['bowing'] = 'Drool';
$avatar_faces['cheers'] = 'Sweetness';
$avatar_faces['cry'] = 'Crying';
$avatar_faces['hot'] = 'Dragon Breath';
$avatar_faces['oops'] = 'Oops';
$avatar_faces['vomit'] = 'Barfing';
$avatar_faces['chu'] = 'o 3 o';
$avatar_faces['cry'] = 'T_T';
$avatar_faces['dam'] = 'Damn';
$avatar_faces['despair'] = 'Sick';
$avatar_faces['glitter'] = 'Yay';
$avatar_faces['hit'] = 'Ouch';
$avatar_faces['hot'] = 'Hot';
$avatar_faces['hum'] = 'Hum';
$avatar_faces['love'] = '<3';
$avatar_faces['pain'] = 'Ai';
$avatar_faces['shine'] = 'Ray';
$avatar_faces['smile'] = 'Happy';
$avatar_faces['stunned'] = 'Ehh';
$avatar_faces['troubled'] = 'Oh noes';
$avatar_faces['wink'] = 'Hey babe';

function MakeOKFace($face) {
	global $avatar_faces;
	return isset($avatar_faces[$face]) ? $face : 'default';
}