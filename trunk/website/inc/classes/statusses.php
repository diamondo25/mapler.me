<?php
require_once __DIR__.'/database.php';
require_once __DIR__.'/account.php';
require_once __DIR__.'/../functions.php'; // For a lot of functions


class Statusses {
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
	social_statuses.*,
	accounts.username,
	TIMESTAMPDIFF(SECOND, timestamp, NOW()) AS `secs_since`,
	(
	SELECT
		COUNT(*)
	FROM
		social_statuses
	WHERE
		reply_to = social_statuses.id
	) AS `reply_count`
FROM
	social_statuses
LEFT JOIN
	accounts
	ON
		social_statuses.account_id = accounts.id
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
	public $id, $account, $nickname, $character, $content, $blog, $timestamp, $override, $mention_list, $reply_to, $reply_count;
	
	public function __construct($row) {
		$this->id = (int)$row['id'];
		$this->account_id = (int)$row['account_id'];
		$this->account = Account::Load($this->account_id);
		$this->nickname = $row['nickname'];
		$this->character = $row['character'];
		$this->content = $row['content'];
		$this->blog = (int)$row['blog'];
		$this->timestamp = $row['timestamp'];
		$this->override = (int)$row['override'];
		$this->seconds_since = (int)$row['secs_since'];
		$this->reply_to = (int)$row['reply_to'];
		$this->reply_count = (int)$row['reply_count'];
		
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
	
	public function ParseContent() {
		global $domain;
		
		preg_match_all('/@([a-z0-9_]+)/i', $this->content, $matches);
		// $matches[1] Contains a list of found mentions
		// Remove dupes
		$matches[1] = array_unique($matches[1]);
		
		$this->mention_list = array_values($matches[1]); // Push all values to mention_list
		
		$this->content = preg_replace('/(http|https|ftp|mailto)\:\/\/([^\<\s\t]+)/i', '<a href="$1://$2" target="_blank">$1://$2</a>', $this->content);
		
		//@replies
		$this->content = preg_replace('/(^| )@([a-z0-9_]+)/i', '$1<a href="http://$2.'.$domain.'/">@$2</a>', $this->content);
		//#hashtags (no search for the moment)
		// $this->content = preg_replace('/(^| )#([a-z0-9_]+)/i', '$1<a href="//'.$domain.'/search/tag/$2">#$2</a>', $this->content);
		//^images (workaround for the moment)
		$this->content = preg_replace('/(^| )!([a-z0-9_]+)/i', '$1<a href="http://cdn.mapler.me/media/$2"><img src="http://cdn.mapler.me/media/$2" class="status-picture" onerror="this.src=\'http://mapler.me/inc/img/no-character.gif\'" /></a>', $this->content);
	}
	
	public function PrintAsHTML($style_addition = '') {
		global $parser, $_loggedin, $domain, $_loginaccount;
		$parser->parse($this->content);
		
		$username = $this->account->GetUsername();
		$own_post = $_loggedin && ($this->account_id == $_loginaccount->GetID() || $_loginaccount->IsRankOrHigher(RANK_MODERATOR));
		
		$reply_info = $this->reply_to == NULL ? NULL : $this->GetReplyInfo($this->reply_to);
		
		$object_id = GetUniqueID();

		$main_char = $this->character;
		if ($main_char == null)
			$main_char = 'inc/img/no-character.gif';
		else
			$main_char = 'avatar/'.$main_char;
		$account_rank = $this->account->GetAccountRank();
		
?>
			<div class="status<?php echo ($this->override == 1) ? ' notification' : ''; ?><?php echo $style_addition; ?>" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>">
				<div class="header">
					<div class="character" style="background: url('http://<?php echo $domain; ?>/<?php echo $main_char; ?>') no-repeat center -17px #FFF;"></div><br/>
				<p>
				<a href="//<?php echo $username; ?>.mapler.me/"><?php echo $this->nickname;?></a> <span class="faded">(@<?php echo $username; ?>)</span>
				
<?php if ($account_rank >= RANK_MODERATOR): ?>
					<span class="ct-label"><i class="icon-star"></i> <?php echo GetRankTitle($account_rank); ?></span>
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
<?php if ($this->account_id !== 2): ?>
					<a href="#" class="mention" status-id="<?php echo $this->id; ?>" poster="<?php echo $username; ?>" mentions="<?php echo implode(';', $this->mention_list); ?>"><i class="icon-share-alt"></i> Reply (<span class="status-reply-count"><?php echo $this->reply_count; ?></span>)</a>
<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $this->id; ?>" status-post-time="<?php echo time() - $this->seconds_since; ?>" class="status-time"><?php echo time_elapsed_string($this->seconds_since); ?> ago</a>
<?php
	if ($_loggedin) {
		if ($own_post) {
?>
						<a class="deletestatus" href="#" onclick="return false;"><i class="icon-remove"></i></a>
<?php
		}
		elseif (false) {
			// Report button
?>
						- <a href="#"></a>
<?php
		}
	}
?>
				</div>
			</div>
			<div class="reply-list span6" status-id="<?php echo $this->id; ?>" unique-id="<?php echo $object_id; ?>"></div>
<?php    
	}
}
?>