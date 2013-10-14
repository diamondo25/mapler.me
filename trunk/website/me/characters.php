<?php
require_once __DIR__.'/../inc/header.php';
require_once __DIR__.'/../inc/job_list.php';


require_once __DIR__.'/../inc/templates/me.header.template.php';

$query = "SELECT
	chr.*,
	w.world_name,
	'{LOCALE}' as `locale`
FROM
	characters chr
LEFT JOIN
	users usr
	ON
		usr.ID = chr.userid
LEFT JOIN
	world_data w
	ON
		w.world_id = chr.world_id
WHERE
	usr.account_id = ".$__url_useraccount->GetID()."
ORDER BY
	chr.world_id ASC,
	chr.last_update DESC
";


$cache = array();

foreach ($_supported_locales as $locale) {
	$db = ConnectCharacterDatabase($locale);
	
	$q = $db->query(str_replace('{LOCALE}', $locale, $query));
	while ($row = $q->fetch_assoc()) {
		if (isset($character_display_options[$row['locale'].':'.$row['name']])) {
			if ($character_display_options[$row['locale'].':'.$row['name']] == 2) { // Always hide... :)
				continue;
			}
		}
		$cache[] = $row;
	}
	$q->free();
}

if (count($cache) == 0) {
?>
	<center>
		<img src="//<?php echo $domain; ?>/inc/img/no-character.gif"/>
		<p><?php echo $__url_useraccount->GetNickName(); ?> hasn't added any characters yet!</p>
	</center>
	</div>
<?php
}
else {
?>
	<div class="span9">
<?php
	$characters_per_row = 3;
	$i = 0;
	foreach ($cache as $row) {
		if ($i % $characters_per_row == 0) {
			if ($i > 0) {
?>
		</div>
<?php
			}
?>
		<div class="row">
<?php
		}
		$i++;
?>
			<div class="character-brick profilec span3 clickable-brick" onclick="document.location = '//<?php echo $row['locale'].'.'.$domain; ?>/character/<?php echo $row['name']; ?>'">
				<center>
					<br />
						<img src="//<?php echo $row['locale'].'.'.$domain; ?>/avatar/<?php echo $row['name']; ?>"/>
					<br />
					<p class="lead"><img src="//<?php echo $row['locale'].'.'.$domain; ?>/inc/img/worlds/<?php echo $row['world_name']; ?>.png" /> <?php echo $row['name']; ?><br />
					<small>Level <?php echo $row['level']; ?> <?php echo GetJobname($row['job']); ?></small>
					</p>
				</center>
			</div>

<?php
	}
?>
		</div>
	</div>
</div>
<?php
}
require_once __DIR__.'/../inc/footer.php';
?>