<?php
$notice = @file_get_contents('../inc/notice.txt');
if (!empty($notice)) {
?>
	<div class="stream-block" style="box-shadow: 0px 0px 30px #FFF;">
		<p class="notice" style="margin:0;">
			<?php echo $notice; ?>
		</p>
	</div>
<?php
}

$socket = @fsockopen('mc.craftnet.nl', 23711, $errno, $errstr, 5);
//$socket = @fsockopen('127.0.0.1', 23711, $errno, $errstr, 3);

if (!$socket) {
?>
	<div class="stream-block">
	<p class="notice">Mapler.me's servers are currently offline or undergoing a maintenance! Clients are disabled.</p>
	</div>
<?php
}
elseif (true) {

	$size = fread($socket, 1);
	for ($i = 0; strlen($size) < 1 && $i < 10; $i++) {
		$size = fread($socket, 1);
	}
	if (strlen($size) == 1) {
		$size = ord($size[0]);
		$data = fread($socket, $size);
		for ($i = 0; strlen($data) < $size && $i < 10; $i++) {
			$data .= fread($socket, $size - strlen($data));
		}
		if (strlen($data) == $size) {
			$data = unpack('vversion/clocale/Vplayers', $data);
			
			switch ($data['locale']) {
				case 2: $data['locale'] = 'Korea'; $data['version'] = '1.2.'.$data['version']; break;
				case 8: $data['locale'] = 'Global'; $data['version'] /= 100; break;
				case 9: $data['locale'] = 'Europe'; $data['version'] /= 100; break;
			}
		
?>
	<div class="stream-block">
		<p class="notice">Mapler.me currently accepts MapleStory <?php echo $data['locale']; ?> V<?php echo $data['version']; ?><br />
		<b>*broken*</b> There are currently <?php echo $data['players']; ?> maplers online.</p>
	</div>
<?php
		}
	}
}