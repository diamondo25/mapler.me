<?php
require_once __DIR__.'/../inc/header.php';
$q = $__database->query("
SELECT
	(SELECT COUNT(*) FROM accounts),
	(SELECT COUNT(*) FROM ".DB_GMS.".characters) + (SELECT COUNT(*) FROM ".DB_EMS.".characters),
	(SELECT COUNT(*) FROM ".DB_GMS.".items) + (SELECT COUNT(*) FROM ".DB_EMS.".items),
	(SELECT COUNT(*) FROM ".DB_GMS.".strings) + (SELECT COUNT(*) FROM ".DB_EMS.".strings),
	(SELECT COUNT(*) FROM ".DB_GMS.".timeline WHERE type = 'levelup') + (SELECT COUNT(*) FROM ".DB_EMS.".timeline WHERE type = 'levelup'),
	(SELECT COUNT(*) FROM social_statuses),
	(SELECT COUNT(*) FROM friend_list WHERE accepted_on IS NOT NULL),
	(SELECT COUNT(*) FROM faq)
");
$tmp = $q->fetch_row(); 
$q->free();


// Build list of dates
$dates = array();
$starttime = time();
$secs_between_days = 60 * 60 * 24;
$datestr = 'Y-m-d'; // 1000-12-31
for ($i = 0; $i < 60; $i++) {
	$dates[] = date($datestr, $starttime - ($i * $secs_between_days));
}

$dates_month = array();
for ($i = 0; $i < 31; $i++) {
	$dates_month[] = date($datestr, $starttime - ($i * $secs_between_days));
}
?>
<link rel="stylesheet" href="http://cdn.oesmith.co.uk/morris-0.4.3.min.css">
<style>
.alert-number {
	color: inherit;
}

.a {
	width: 275px;
	float: left;
	margin-right: 20px;
}
</style>

<script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
<script src="http://cdn.oesmith.co.uk/morris-0.4.3.min.js"></script>

		<h2>Statisics</h2>
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[0]); ?></h1>
				<span class="faded">accounts registered on Mapler.me.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[1]); ?></h1>
				<span class="faded">characters added by maplers.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[2]); ?></h1>
				<span class="faded">items stored between all characters.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[3]); ?></h1>
				<span class="faded">different items exist in MapleStory.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[4]); ?></h1>
				<span class="faded">Mapler.me characters have leveled collaboratively.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[5]); ?></h1>
				<span class="faded">statuses posted between all members.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[6]); ?></h1>
				<span class="faded">friendships have been formed.</span>
			</div>
			
			<div class="lead alert alert-info a">
				<h1 class="alert-number"><?php AddCommas($tmp[7]); ?></h1>
				<span class="faded">questions in the FAQ.</span>
			</div>
			
<br />
<?php

$values = array_flip(array_reverse($dates));
foreach ($values as $date => $val)
	$values[$date] = 0;

$q = "
SELECT
	DATE(`registered_on`),
	COUNT(*)
FROM
	".DB_ACCOUNTS.".`accounts`
WHERE
	DATE(`registered_on`) IN ('".implode('\',\'', $dates)."')
GROUP BY
	YEAR(`registered_on`),
	MONTH(`registered_on`),
	DAY(`registered_on`)
";


$q = $__database->query($q);

while ($row = $q->fetch_row()) {
	$values[$row[0]] = $row[1];
}

?>
<div style="clear:both;">&nbsp;</div>

<h1>Chart of new Mapler.me accounts in 2 months</h1>
<div id="joinchart" class="span8" style="height: 250px;"></div>

<script>
new Morris.Bar({
  // ID of the element in which to draw the chart.
  element: 'joinchart',
  // Chart data records -- each entry in this array corresponds to a point on
  // the chart.
  data: [
<?php
foreach ($values as $date => $amount)
	echo '{ date: "'.$date.'", value: '.$amount.' },';
?>
  ],
  // The name of the data record attribute that contains x-values.
  xkey: 'date',
  // A list of names of data record attributes that contain y-values.
  ykeys: ['value'],
  // Labels for the ykeys -- will be displayed when you hover over the
  // chart.
  labels: ['Amount of accounts']
});
</script>


			<h1>Posts on the stream in 2 months</h1>
			<div id="statuschart" style="height: 250px;"></div>
<?php

$values = array_flip(array_reverse($dates));
foreach ($values as $date => $val)
	$values[$date] = 0;

$q = "
SELECT
	DATE(`timestamp`),
	COUNT(*),
	COUNT(DISTINCT account_id)
FROM
	`social_statuses`
WHERE
	DATE(`timestamp`) IN ('".implode('\',\'', $dates)."')
GROUP BY
	YEAR(`timestamp`),
	MONTH(`timestamp`),
	DAY(`timestamp`)
";


$q = $__database->query($q);

while ($row = $q->fetch_row()) {
	$values[$row[0]] = array($row[1], $row[2]);
}


?>
<script>
new Morris.Bar({
  element: 'statuschart',
  data: [
<?php
foreach ($values as $date => $amount)
	echo '{ date: "'.$date.'", value: '.$amount[0].', unique_accounts: '.$amount[1].' },';
?>
  ],
  xkey: 'date',
  ykeys: ['value', 'unique_accounts'],
  labels: ['Amount of status updates', 'Unique accounts']
});
</script>

			<h1>Chart of MapleStory account creation dates</h1>
			<div id="creationchart" style="height: 250px;"></div>
<?php

$q = "
SELECT
	YEAR(`creation_date`),
	COUNT(*),
	COUNT(DISTINCT account_id),
	COUNT(CASE WHEN account_id = 2 THEN NULL ELSE account_id END),
	COUNT(CASE WHEN account_id <> 2 THEN NULL ELSE 2 END)
FROM
	".DB_GMS.".`users`
WHERE
	`creation_date` <> '0000-00-00'
GROUP BY
	YEAR(`creation_date`)
";


$q = $__database->query($q);
$values = array();
while ($row = $q->fetch_row()) {
	$values[$row[0]] = array($row[1], $row[2], $row[3], $row[4]);
}


?>
<script>
new Morris.Line({
  element: 'creationchart',
  data: [
<?php
foreach ($values as $date => $amount)
	echo '{ date: "'.$date.'", value: '.$amount[0].', value_unique_accounts: '.$amount[1].', value_non_dummies: '.$amount[2].', value_dummies: '.$amount[3].' },';
?>
  ],
  xkey: 'date',
  ykeys: ['value', 'value_unique_accounts', 'value_dummies', 'value_non_dummies'],
  labels: ['Amount of accounts created', 'Unique Mapler.me accounts', 'Unassigned accounts', 'Bound accounts']
});
</script>



			<h1>Graph of Character Levels</h1>
			<div id="levelchart" style="height: 250px;"></div>
<?php

$q = "
SELECT
	`level`,
	COUNT(*)
FROM
	".DB_GMS.".`characters`
GROUP BY
	`level`
ORDER BY
	`level` DESC
";


$q = $__database->query($q);
$values = array();
for ($i = 1; $i <= 250; $i++) {
	$values[$i] = 0;
}

while ($row = $q->fetch_row()) {
	$values[(int)$row[0]] = $row[1];
}


?>
<script>
new Morris.Bar({
  element: 'levelchart',
  data: [
<?php
foreach ($values as $level => $amount)
	echo '{ level: '.$level.', value: '.$amount.' },';
?>
  ],
  xkey: 'level',
  ykeys: ['value'],
  labels: ['Amount of characters'],
  parseTime: false
});
</script>

			<h1>Graph of Levels per Day</h1>
			<div id="level2chart" style="height: 250px;"></div>
<?php

$values = array_flip(array_reverse($dates));
foreach ($values as $date => $val)
	$values[$date] = array(0,0,0);

$q = "
SELECT
	DATE(`when`),
	COUNT(CASE WHEN `type` = 'levelup' THEN 1 ELSE NULL END),
	COUNT(CASE WHEN `type` = 'skillup' THEN 1 ELSE NULL END),
	COUNT(CASE WHEN `type` = 'jobup' THEN 1 ELSE NULL END)
FROM
	".DB_GMS.".`timeline`
WHERE
	DATE(`when`) IN ('".implode('\',\'', $dates)."')
GROUP BY
	YEAR(`when`),
	MONTH(`when`),
	DAY(`when`)
";


$q = $__database->query($q);

while ($row = $q->fetch_row()) {
	$values[$row[0]] = array($row[1], $row[2], $row[3]);
}


?>
<script>
new Morris.Bar({
  element: 'level2chart',
  data: [
<?php
foreach ($values as $level => $amount)
	echo '{ level: "'.$level.'", value1: '.$amount[0].', value2: '.$amount[1].', value3: '.$amount[2].' },';
?>
  ],
  xkey: 'level',
  ykeys: ['value1', 'value2', 'value3'],
  labels: ['Levelups', 'Skillups', 'Jobups']
});
</script>

