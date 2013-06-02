<?php
require_once 'inc/functions.php';

$icon = GetItemIcon(2046086);
echo $icon;

/*
$stance = 'heal';
$frames = $real_frame_count = 3;
$does_rewind = false;

$input = 'http://mplr.e.craftnet.nl/ignavatar/RoboticOil?stance='.$stance.'&stance_frame=';
if ($does_rewind)
	$frames += ($frames - 1); // Forth and back


$apng_writer = new APNGWriter('dump.png');
$apng_writer->WriteHeader(128, 128);




$apng_writer->WriteAnimationControl($frames, 0);
$anim_frame_id = 0;
for ($frame = 0; $frame < $frames; $frame++) {
	$anim_frame_id = $frame;
	if ($anim_frame_id >= $real_frame_count)
		$anim_frame_id = $frames - $frame;
	file_put_contents('temp'.$frame.'.png', file_get_contents($input.$anim_frame_id));
}

$sequence = 0;
for ($frame = 0; $frame < $frames; $frame++) {
	echo 'Writing frame '.$frame.'<br />';
	$png = new PNGReader('temp'.$frame.'.png');
	$png->LoadChunks();
	
	
	$frame_data = $png->GetChunk('IDAT');
	$apng_writer->WriteFrameControl($sequence, 128, 128, 0, 0, 200);
	$sequence++;
	
	if ($frame == 0) {
		$apng_writer->WriteChunk('IDAT', $frame_data[2]);
	}
	else {
		$apng_writer->WriteFrameData($sequence, $frame_data[2]);
		$sequence++;
	}

	$png->Close();
}

$apng_writer->WriteChunk('IEND', '');

$apng_writer->Close();
*/
?>