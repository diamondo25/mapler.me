<?php
require_once __DIR__.'/../inc/header.php';
?>
		<center><h1>Mapler.me Statistics</h1></center>
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM accounts"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">accounts registered on Mapler.me.</span></h1>
			
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM characters"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">characters added by maplers.</span></h1>
			
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM items"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">items stored between all characters.</span></h1>
			
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM strings"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded"> different items exist in MapleStory.</span></h1>
			
			<hr/>
			
			<h1><span class="faded">Mapler.me characters have leveled</span> <?php $q = $__database->query("SELECT COUNT(*) FROM timeline WHERE type = 'levelup'"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">times collaboratively.</span></h1>
			
			<hr/>
			
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM social_statuses"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">statuses posted between all members.</span></h1>
			
			<h1><?php $q = $__database->query("SELECT COUNT(*) FROM friend_list WHERE accepted_on IS NOT NULL"); $tmp = $q->fetch_row(); $q->free(); echo $tmp[0]; ?> <span class="faded">friendships have been formed.</span></h1>
<?php
require_once __DIR__.'/../inc/footer.php';
?>
