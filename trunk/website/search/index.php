<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';
require_once __DIR__.'/../inc/templates/search.header.template.php';

$__char_db = ConnectCharacterDatabase(CURRENT_LOCALE);
$locale_domain = $domain;
if (GMS) $locale_domain = 'gms.'.$locale_domain;
elseif (EMS) $locale_domain = 'ems.'.$locale_domain;
elseif (KMS) $locale_domain = 'kms.'.$locale_domain;
?>
<div class="span9">

<?php
	if ($check != 0 && ($check < 4 || $check > 20)) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif" />
		<p>The search request was invalid. Please use between <strong>four</strong> and <strong>twenty</strong> characters.</p>
	</center>
</div>
<?php
	require_once __DIR__.'/../inc/footer.php';
	die;
	}
?>

<div id="character_list">
<?php
if (!$check == '0') {
?>
<?php

$q = $__char_db->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `secs_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
WHERE 
	name LIKE '%".$__char_db->real_escape_string($searching)."%'
ORDER BY
	last_update DESC
LIMIT
	0, 6
");
	while ($row = $q->fetch_assoc()) {
?>
<div class="character-brick clickable-brick span3 char <?php echo strtolower($row['world_name']); ?>" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'" style="width:210px !important;margin-bottom:10px;margin-top:10px;">
		<center>
			<br />
			<img src="//<?php echo $locale_domain; ?>/avatar/<?php echo $row['name']; ?>"/><br/>
			<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br />
			<small>Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></small></p>
		</center>
</div>
<?php
}
?>
</div>
</div>
</div>
</div>
<?php
require_once __DIR__.'/../inc/footer.php';
die;
}
?>

<?php

	$q = $__char_db->query("
SELECT 
	*,
	w.world_name,
	`GetCharacterAccountID`(id) AS account_id,
	TIMESTAMPDIFF(SECOND, last_update, NOW()) AS `secs_since`
FROM
	`characters` chr
LEFT JOIN 
	world_data w
	ON
		w.world_id = chr.world_id
ORDER BY
	last_update DESC
LIMIT
	0, 6
");
	while ($row = $q->fetch_assoc()) {
?>
<div class="character-brick clickable-brick span3 char <?php echo strtolower($row['world_name']); ?>" onclick="document.location = '//<?php echo $domain; ?>/player/<?php echo $row['name']; ?>'" style="width:210px !important;margin-bottom:10px;">
				<center>
					<br />
						<img src="//<?php echo $locale_domain; ?>/avatar/<?php echo $row['name']; ?>"/><br/>
						<p class="lead"><img src="//<?php echo $domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br />
						<small>Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></small></p>
				</center>
			</div>
<?php
}
?>
</div>
</div>
</div>
</div>
<?php require_once __DIR__.'/../inc/footer.php'; ?>