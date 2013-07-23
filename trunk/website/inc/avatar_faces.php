<?php


//'default','blazing','bewildered','angry','blink','bowing','cheers','chu','cry','dam','despair','glitter','hit','hot','hum','love','oops','pain','shine','smile','stunned','troubled','vomit','wink'
$avatar_faces = array();
$avatar_faces['default'] = 'Standard';
$avatar_faces['hit'] = '(#o.e) (F1)';
$avatar_faces['smile'] = '^_^; (F2)';
$avatar_faces['troubled'] = '¬.¬ (F3)';
$avatar_faces['cry'] = '｡･ﾟ･(ﾉД｀)･ﾟ･｡ (F4)';
$avatar_faces['angry'] = '(◣_◢) (F5)';
$avatar_faces['bewildered'] = 'o.O (F6) ';
$avatar_faces['stunned'] = 'TT.. (F7)';
$avatar_faces['blaze'] = 'Flaming';
$avatar_faces['bowing'] = 'Drool';
$avatar_faces['cheers'] = 'Sweetness';
$avatar_faces['vomit'] = 'Queasy';
$avatar_faces['chu'] = 'Smoochies';
$avatar_faces['dam'] = 'Bleh';
$avatar_faces['despair'] = 'Whoa Whoa';
$avatar_faces['glitter'] = 'Sparkling Eyes';
$avatar_faces['hot'] = 'Dragon Breath';
$avatar_faces['hum'] = 'Constant Sigh';
$avatar_faces['love'] = 'Goo Goo';
$avatar_faces['pain'] = 'Ouch';
$avatar_faces['shine'] = 'Ray';
$avatar_faces['wink'] = 'Wink';

function MakeOKFace($face) {
	global $avatar_faces;
	return isset($avatar_faces[$face]) ? $face : 'default';
}