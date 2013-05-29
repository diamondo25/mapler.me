<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = $__server_time;
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;

	if ($_loggedin) {
		$res['membername'] = $_loginaccount->GetUsername();
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
	}
	
	$status_info = array();
	if (isset($_POST['shown-statuses'])) {
		// Check status info

		foreach ($_POST['shown-statuses'] as $oriid) {
			$id = intval($oriid);
			if ($id == 0) {
				$status_info['deleted'][] = $oriid;
				continue;
			}
			$q = $__database->query("
SELECT
	COUNT(*) AS `reply_count`
FROM
	social_statuses
WHERE
	reply_to = ".$id);
			if ($q->num_rows == 0) {
				$status_info['deleted'][] = $oriid;
			}
			else {
				$row = $q->fetch_row();
				$status_info['reply_count'][$oriid] = $row[0];
			}
			$q->free();
		}
	}
	
	if ($_loggedin) {
		if (isset($_POST['last-level'])) {
			$lastlevel = intval($_POST['last-level']);
			if ($lastlevel == 0)
				$lastlevel = -1;

			$q = $__database->query("
SELECT
	tl.id,
	TIMESTAMPDIFF(SECOND, tl.when, NOW()),
	c.name,
	tl.data,
	a.username
FROM
	timeline tl
LEFT JOIN
	characters c
	ON
		c.internal_id = tl.objectid
LEFT JOIN
	users u
	ON
		u.id = c.userid
LEFT JOIN
	accounts a
	ON
		a.id = u.account_id
WHERE
	tl.type = 'levelup'
	AND
	(
		`FriendStatus`(a.id, ".$_loginaccount->GetID().") = 'FRIENDS'
		OR
		`FriendStatus`(a.id, ".$_loginaccount->GetID().") = 'FOREVER_ALONE'
	)
".($lastlevel != -1 ? "
	AND
		tl.when > FROM_UNIXTIME(".$lastlevel.")" : '')."
ORDER BY
	tl.id DESC
LIMIT
	20");
			$levels = array();
			
			while ($row = $q->fetch_row()) {
				$username = $row[4];
				$seconds_since = $row[1];
				$servertime = $__server_time - $seconds_since;
				if (count($levels) == 0)
					$lastlevel = $servertime;
				ob_start();
?>
			<div class="status" tl-id="<?php echo $row[0]; ?>">
				<div class="header">
					<div class="character" style="background: url('http://<?php echo $domain; ?>/avatar/<?php echo $row[2]; ?>') no-repeat center -17px #FFF;"></div><br/>
				<p>
				<a href="//mapler.me/player/<?php echo $row[2]; ?>"><?php echo $row[2]; ?></a> just leveled up to level <span style="font-size: 13px"><?php echo $row[3]; ?></span>!
				<span status-post-time="<?php echo $servertime; ?>" class="status-time" style="float: right;"><?php echo time_elapsed_string($seconds_since); ?> ago</a>
			</div>
<?php
				$level_info = ob_get_clean();
				$levels[] = array($servertime, $level_info);
			}
			
			$res['levels'] = $levels;
			$res['last_level'] = $lastlevel;
		}
	}
	$res['status_info'] = $status_info;
	
	JSONAnswer($res);
}
?>