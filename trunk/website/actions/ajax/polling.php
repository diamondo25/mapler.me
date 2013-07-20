<?php
require_once __DIR__.'/../../inc/functions.php';
require_once __DIR__.'/../../inc/functions.ajax.php';
require_once __DIR__.'/../../inc/classes/statusses.php';
require_once __DIR__.'/../../inc/job_list.php';

CheckSupportedTypes('info');


if ($request_type == 'info') {
	$res = array();
	$res['time'] = (int)$__server_time;
	
	$_client_time = isset($_POST['client-time']) ? intval($_POST['client-time']) : time() - 10000;

	
	$res['loggedin'] = $_loggedin;
	$res['notifications'] = $_loggedin ? (int)GetNotification() : 0;

	if ($_loggedin) {
		$res['membername'] = $_loginaccount->GetUsername();
		$__database->query("UPDATE accounts SET last_login = NOW(), last_ip = '".$_SERVER['REMOTE_ADDR']."' WHERE id = ".$_loginaccount->GetID());
	}
	
	$status_info = array();
	if (isset($_POST['shown-statuses'])) {
		// Check status info
		$correctids = array();
		foreach ($_POST['shown-statuses'] as $oriid) {
			$id = intval($oriid);
			if ($id == 0) {
				continue;
			}
			$correctids[] = $id;
		}
		$correctids = array_unique($correctids);
		if (count($correctids) > 0) {
			$tmp = "
SELECT
	s.id,
	(SELECT COUNT(s2.id) FROM social_statuses s2 WHERE s2.reply_to = s.id) AS `reply_count`
FROM
	social_statuses s
WHERE
	s.id IN (".implode(',', $correctids).")";
			$q = $__database->query($tmp);
			if ($q->num_rows == 0) {
				// All deleted
				foreach ($_POST['shown-statuses'] as $oriid)
					$status_info['deleted'][] = $oriid;
			}
			else {
				while ($row = $q->fetch_row()) {
					$status_info['reply_count'][$row[0]] = (int)$row[1];
					if (($key = array_search($row[0], $correctids)) !== false) {
						unset($correctids[$key]);
					}
				}
				foreach ($correctids as $oriid)
					$status_info['deleted'][] = (int)$oriid;
			}
			$q->free();
		}
		
	}
	$res['status_info'] = $status_info;
	
	
	$url = isset($_POST['url']) ? $_POST['url'] : null;
	$parsed_url = $url == null ? null : parse_url($url);
	$is_ok_url = $url != null && strpos($parsed_url['host'], $domain) !== false;
	
	if ($is_ok_url && isset($_POST['has-statusses']) && $_POST['has-statusses'] != 0) {
		$subdomain = trim(substr($parsed_url['host'], 0, strpos($parsed_url['host'], $domain)), '.');
		
		$whereq = '> FROM_UNIXTIME('.$_client_time.')';
		if (isset($_POST['older-than'])) {
			$whereq = '< FROM_UNIXTIME('.$_client_time.')';
		}
		$whereq_1 = '`when` '.$whereq.($_client_time == 0 ? ' AND `when` > DATE_SUB(NOW(), INTERVAL 2 DAY)' : '');
		$whereq_2 = '`timestamp` '.$whereq.($_client_time == 0 ? ' AND `timestamp` > DATE_SUB(NOW(), INTERVAL 2 DAY)' : '');
		if (isset($_POST['older-than'])) {
			$whereq_1 .= ' AND `when` > DATE_SUB(FROM_UNIXTIME('.$_client_time.'), INTERVAL 2 DAY)';
			$whereq_2 .= ' AND `timestamp` > DATE_SUB(FROM_UNIXTIME('.$_client_time.'), INTERVAL 2 DAY)';
		}
		
		if (strpos($url, '/'.$domain.'/blog/') !== false) {
			// No time thingies, only blog posts
			$whereq_1 = ' 1 = 0 '; // heh
			$whereq_2 .= ' AND blog = 1';
		}
		
		$q = "
SELECT 
	UNIX_TIMESTAMP(`when`),
	'timeline_row' AS `type`,
	CONVERT(CONCAT(c.`name`, X'02', `type`) USING latin1) AS `col1`,
	CONVERT(`data` USING latin1) AS `col2`,
	a.username,
	a.nickname,
	a.account_rank
FROM
	`timeline`
LEFT JOIN
	characters c
	ON
		c.internal_id = character_id
LEFT JOIN
	accounts a
	ON
		a.id = `account_id`
WHERE
	(`type` = 'levelup' OR `type` = 'jobup')
	AND
	".$whereq_1."
";
		if ($_loggedin && ($subdomain == '' || $subdomain == 'www')) { // Main screen
			$q .= "
	AND
	`FriendStatus`(`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')";
		}
		if ($subdomain != '') {
			$q .= ' AND ';
			$q .= "a.username = '".$__database->real_escape_string($subdomain)."'";
		}

		$q .= "
ORDER BY
	`when` DESC
LIMIT
	15
";
		$q = $__database->query($q);
		
		$found_rows = array();
		
		while ($row = $q->fetch_row())
			$found_rows[] = $row;
		$q->free();
		
		
		// Get statusses
		
		$q = "
SELECT 
	UNIX_TIMESTAMP(`timestamp`),
	'status' AS `type`,
	CONVERT(CONCAT(ss.`id`, X'02', `account_id`, X'02', ss.`nickname`, X'02', `character`, X'02', `blog`, X'02', `override`, X'02', IF(`reply_to` IS NULL, '-', `reply_to`), X'02',(
	SELECT
		COUNT(s_inner.id)
	FROM
		social_statuses s_inner
	WHERE
		s_inner.reply_to = ss.id
	)) USING latin1) AS `col1`,
	CONVERT(`content` USING latin1) AS `col2`,
	a.username,
	a.nickname,
	a.account_rank
FROM
	`social_statuses` ss
LEFT JOIN
	accounts a
	ON
		a.id = `account_id`
WHERE
	".$whereq_2."
";
		if ($_loggedin && ($subdomain == '' || $subdomain == 'www')) { // Main screen
			$q .= "
	AND
	`FriendStatus`(`account_id`, ".$_loginaccount->GetID().") IN ('FRIENDS', 'FOREVER_ALONE')";
		}
		if ($subdomain != '') {
			$q .= ' AND ';
			$q .= "a.username = '".$__database->real_escape_string($subdomain)."'";
		}

		$q .= "
ORDER BY
	`timestamp` DESC
LIMIT
	15
";
		$q = $__database->query($q);
		
		while ($row = $q->fetch_row())
			$found_rows[] = $row;
		$q->free();

		$stream = array();
		$timestamp = $__server_time;
		$lowest_date = 0;
		foreach ($found_rows as $row) {
			$timestamp = $row[0];
			$type = $row[1];
			$content = explode(chr(0x02), $row[2]);
			$info = $row[3];
			$username = $row[4];
			$nickname = $row[5];
			$account_rank = $row[6];
			$seconds_since = $__server_time - $timestamp;
			if (count($stream) == 0)
				$lowest_date = $timestamp;
			ob_start();
			if ($type == 'timeline_row') {
				if ($content[1] == 'levelup') {
?>
			<div class="status">
				<p style="margin:0px;"><i class="icon-check-sign"></i>
					<a href="//<?php echo $username; ?>.mapler.me/">@<?php echo $username; ?></a>'s character 
					<a href="//mapler.me/player/<?php echo $content[0]; ?>"><?php echo $content[0]; ?></a> reached Level <span style="font-size: 13px"><?php echo $info; ?>!</span>
					<span status-post-time="<?php echo $timestamp; ?>" style="float:right;"><?php echo time_elapsed_string($seconds_since); ?> ago - Auto</span>
				</p>
			</div>
<?php
				}
				elseif ($content[1] == 'jobup') {
?>
			<div class="status">
				<p style="margin:0px;"><i class="icon-check-sign"></i>
					<a href="//<?php echo $username; ?>.mapler.me/">@<?php echo $username; ?></a>'s character 
					<a href="//mapler.me/player/<?php echo $content[0]; ?>"><?php echo $content[0]; ?></a> advanced to a '<span style="font-size: 13px"><?php echo GetJobname($info); ?>'!</span>
					<span status-post-time="<?php echo $timestamp; ?>" style="float:right;"><?php echo time_elapsed_string($seconds_since); ?> ago - Auto</span>
				</p>
			</div>
<?php
				}
				// hurr
			}
			elseif ($type == 'status') {
				$status = new Status(array(
					'id' => $content[0],
					'account_id' => $content[1],
					'nickname' => $content[2],
					'character' => $content[3],
					'blog' => $content[4],
					'override' => $content[5],
					'reply_to' => $content[6],
					'reply_count' => $content[7],
					'content' => $info,
					'timestamp' => $timestamp
				));
				
				$status->PrintAsHTML('');
				unset($status);
			}
			$level_info = ob_get_clean();
			$stream[] = array($timestamp, $level_info);
		}
		$highest_date = $timestamp;

		$res['statuses'] = $stream;
		$res['oldest_status'] = (int)$lowest_date;
		$res['newest_status'] = (int)$highest_date;

	}
	
	JSONAnswer($res);
}
?>