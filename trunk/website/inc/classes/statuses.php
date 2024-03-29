<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/account.php';
require_once __DIR__.'/../functions.php'; // For a lot of functions


class Statuses {
	public $data;
	
	public function FeedData($query) {
		$this->data = array();
		while ($row = $query->fetch_assoc()) {
			$this->data[] = new Status($row);
		}
	}
	
	public function Count() {
		return count($this->data);
	}
	
	public function Load($whereAddition = null, $limit = null) {
		global $__database;
		$q = $__database->query("
SELECT
	s.*,
	accounts.username,
	UNIX_TIMESTAMP(s.timestamp) AS `timestamp`,
	(
	SELECT
		COUNT(s_inner.id)
	FROM
		social_statuses s_inner
	WHERE
		s_inner.reply_to = s.id
	) AS `reply_count`
FROM
	social_statuses s
LEFT JOIN
	accounts
	ON
		s.account_id = accounts.id
".(
$whereAddition != null 
? "WHERE ".$whereAddition 
: ''
)."
ORDER BY
	id DESC
".(
$limit != null 
? "LIMIT ".$limit 
: ''
)."
");
	
		$this->FeedData($q);
		
		$q->free();
	}
}

class Status {
	public $id, $account_id, $account, $nickname, $character, $content, $blog, $timestamp, $override, $mention_list, $reply_to, $reply_count;
	public $using_face;
	
	public function __construct($row) {
		global $__server_time;
		$this->id = (int)$row['id'];
		$this->account_id = (int)$row['account_id'];
		$this->account = Account::Load((int)$this->account_id);
		$this->nickname = $row['nickname'];
		$this->character = $row['character'];
		$this->content = $row['content'];
		$this->blog = (int)$row['blog'];
		$this->timestamp = $row['timestamp'];
		$this->override = (int)$row['override'];
		$this->reply_to = (int)$row['reply_to'];
		$this->reply_count = (int)$row['reply_count'];
		$this->seconds_since = $__server_time - $this->timestamp;
		$this->using_face = $row['using_face'];
		
		$this->ParseContent();
	}
	
	public static function GetReplyInfo($id) {
		global $__database;
		
		$q = $__database->query("
SELECT
	id,
	nickname
FROM
	social_statuses ss
WHERE
	ss.id = ".$id);
		if ($q->num_rows == 0) {
			$q->free();
			return NULL;
		}
		$row = $q->fetch_assoc();
		$q->free();
		return $row;
	}
	
	private static $_status_search_for = array(
		'/(http|https|ftp)\:\/\/([^\<\s\t]+)/i', // URLs
		'/(^| |\r\n)@([a-z0-9_]+)/i', // Replies
		'/(^| |\r\n)~([a-z0-9_]+)~/i', // Player
		// '/(^| )#([a-z0-9_]+)/i', // Hashtags
		'/(^| )!([a-z0-9_]+)/i', // Images
	);
	
	private static $_status_replace_with = array(
		'<a href="$1://$2" target="_blank" class="status-link">$1://$2</a>', // URLs
		'$1<a href="http://$2.mapler.me/">@$2</a>', // Replies
		'$1<a href="http://mapler.me/player/$2">$2</a>', // Player
		// '$1<a href="//'.$domain.'/search/tag/$2">#$2</a>', // Hashtags
		'$1<a href="http://cdn.mapler.me/media/$2"><img src="http://cdn.mapler.me/media/$2" class="status-picture" onerror="this.src=\'http://mapler.me/inc/img/no-character.gif\'" /></a>', // Images
	);
	
	public static $blocklist = array('fuck', 'fucking', 'motherfucker', 'shit', 'shitting', 'fucker', 'bitch', 'bitching',
	'asshole', 'fag', 'faggot', 'nigger', 'vagina', 'pussy', 'dick', 'cock', 'wonderking',
	'cunt', 'penis', 'whore', 'gamersoul', 'w8baby', 'gamekiller', 'anal', 'masterbate', 'slut', 'whore',
	'pute', 'gay', 'dick', 'boobs');
	
	public static $censor = '<img src="//mapler.me/inc/img/ui/Item/Equip/lock.png" title="Censored!" />';
	
	public function ParseContent() {
		global $domain;
		
		preg_match_all('/@([a-z0-9_]+)/i', $this->content, $matches);
		// $matches[1] Contains a list of found mentions
		// Remove dupes
		$matches[1] = array_unique($matches[1]);
		
		$this->mention_list = array_values($matches[1]); // Push all values to mention_list
		
		$this->content = preg_replace(self::$_status_search_for, self::$_status_replace_with, $this->content);
		
		$this->content = preg_replace_callback('/(^| |\r\n)([a-zA-Z0-9]+)/', function ($matches) {
			if (in_array(strtolower($matches[2]), Status::$blocklist))
				return $matches[1].Status::$censor;
			return $matches[1].$matches[2];
		}, $this->content);
		// $this->content = str_ireplace(self::$blocklist, self::$censor, $this->content);
	}
	
	public function PrintAsHTML($style_addition = '') {
		global $parser, $_loggedin, $domain, $_loginaccount, $__server_time;
		$parser->parse($this->content);
		
		$username = $this->account->GetUsername();
		$own_post = $_loggedin && ($this->account_id == $_loginaccount->GetID() || $_loginaccount->IsRankOrHigher(RANK_MODERATOR));
		
		$reply_info = $this->reply_to == NULL ? NULL : $this->GetReplyInfo($this->reply_to);
		
		$object_id = GetUniqueID();
		
		if (strpos($this->character, ':') === false)
			$this->character = 'gms:'.$this->character;

		$tmp = explode(':', $this->character);
		$main_char = $tmp[1];
		$locale = $tmp[0];
		$account_rank = $this->account->GetAccountRank();
		
?>
			<div class="status<?php echo ($this->override == 1) ? ' notification' : ''; ?><?php echo $style_addition; ?>" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>">
				<div class="header">
					<?php MakePlayerAvatar($main_char, $locale, array('face' => $this->using_face)); ?><br />
					<p>
						<a href="//<?php echo $username; ?>.mapler.me/"><?php echo $this->nickname;?></a> <span class="faded">(@<?php echo $username; ?>)</span>
				
<?php if ($account_rank >= RANK_MODERATOR): ?>
						<span class="ct-label"><i class="icon-star"></i> <?php echo GetRankTitle($account_rank); ?></span> 
<?php endif; ?>
<?php if ($this->blog !== 0): ?>
						<span class="ct-label"><i class="icon-bullhorn"></i> Blog Post</span>
<?php endif; ?>
				
					</p>
				</div>
				<br />
				<div class="status-contents">
<?php echo $parser->getAsHtml(); ?>
				</div>
				<div class="status-extra" style="clear:both;">
<?php if ($reply_info != NULL): ?>
					<a href="//mapler.me/stream/status/<?php echo $reply_info['id']; ?>" style="float: left;" class="reply-to">Replied to <?php echo $reply_info['nickname']; ?> <i class="icon-chevron-right"></i></a>
<?php endif; ?>
<?php if ($_loggedin): ?>
					<a href="#post" role="button" data-toggle="modal" class="mention" status-id="<?php echo $this->id; ?>" poster="<?php echo $username; ?>" mentions="<?php echo implode(';', $this->mention_list); ?>"><i class="icon-share-alt"></i> Reply (<span class="status-reply-count"><?php echo $this->reply_count; ?></span>)</a>
<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $this->id; ?>" status-post-time="<?php echo $__server_time - $this->seconds_since; ?>" class="status-time"><?php echo time_elapsed_string($this->seconds_since); ?> ago</a>
<?php
	if ($_loggedin) {
		if ($own_post) {
?>
					<a href="#" class="deletestatus" onclick="return false;"><i class="icon-remove"></i></a>
<?php
		}
?>
					<a href="//<?php echo $domain; ?>/report/status/<?php echo $this->id; ?>" class="reportstatus"><i class="icon-flag"></i></a>
<?php
	}
?>
				</div>
				<div class="reply-list" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>"></div>
			</div>
<?php    
	}
}
?>