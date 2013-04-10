<?php
require_once __DIR__.'/../database.php';
require_once __DIR__.'/account.php';


class Statusses {
	public $data;
	
	public function FeedData($query) {
		while ($row = $query->fetch_assoc()) {
			$this->data[] = new Status($row);
		}
	}
	
	public function Count() {
		return count($this->data);
	}
}

class Status {
	public $id, $account, $nickname, $character, $content, $comments_disabled, $timestamp, $override;
	
	public function __construct($row) {
		$this->id = $row['id'];
		$this->account_id = $row['account_id'];
		$this->account = Account::Load($this->account_id);
		$this->nickname = $row['nickname'];
		$this->character = $row['character'];
		$this->content = $row['content'];
		$this->comments_disabled = $row['comments_disabled'];
		$this->timestamp = $row['timestamp'];
		$this->override = $row['override'];
		$this->seconds_since = $row['secs_since'];
		
		$this->ParseContent();
	}
	
	public function ParseContent() {
		//@replies
		$this->content = preg_replace('/(^|[^a-z0-9_])@([a-z0-9_]+)/i', '$1<a href="http://$2.mapler.me/">@$2</a>', $this->content);
		//#hashtags (no search for the moment)
		$this->content = preg_replace('/(^|[^a-z0-9_])#([a-z0-9_]+)/i', '$1<a href="#">#$2</a>', $this->content);
	}
	
	public function PrintAsHTML($style_addition = '') {
		global $parser, $_loggedin, $domain, $_loginaccount;
		$parser->parse($this->content);
		
		$username = $this->account->GetUsername();
?>
			<div class="status<?php echo ($this->override == 1) ? ' notification' : ''; ?><?php echo ($_loggedin && $this->account_id == $_loginaccount->GetID()) ? ' postplox' : ''; ?><?php echo $style_addition; ?>">
				<div class="header" style="background: url('http://mapler.me/avatar/<?php echo $this->character; ?>') no-repeat right -30px #FFF;">
					<a href="//<?php echo $username; ?>.<?php echo $domain; ?>/"><?php echo $this->nickname;?></a> said:
				</div>
				<br />
				<?php echo $parser->getAsHtml(); ?>
				<div class="status-extra">
<?php if ($this->account_id !== 2): ?>
					<a href="#" class="mention-<?php echo $this->id; ?>" mentioned="<?php echo $username; ?>"><i class="icon-share-alt"></i></a>
					<script type="text/javascript">
						$('.mention-<?php echo $this->id; ?>').click(function() {
							var value = $(".mention-<?php echo $this->id; ?>").attr('mentioned');
							var input = $('#post-status');
							input.val(input.val() + '@' + value + ' ');
							$(".poster").addClass("in");
							$('.poster').css("height","auto");
							return false;
						});
					</script>
<?php endif; ?>
					<a href="//<?php echo $domain; ?>/stream/status/<?php echo $this->id; ?>"><?php echo time_elapsed_string($this->seconds_since); ?> ago</a>
<?php
	if ($_loggedin) {
		if (IsOwnAccount()) {
?>
						- <a href="#" onclick="RemoveStatus(<?php echo $this->id; ?>);">delete?</a>
<?php
		}
		else {
			// Report button
?>
						- <a href="#"></a>
<?php
		}
	}
?>
				</div>
			</div>
<?php    
	}
}
?>