<?php 
require_once __DIR__.'/inc/header.php';
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['guide']) && $_loggedin) {
?>
	
<style>
h1 small {
    color: #333;
}
</style>	
	
<div class="status">
	<h1>#1 <small>To get you started, <span class="muted">what does Mapler.me offer?</span></small></h1>
	<p><img src="http://cdn.mapler.me/etc/resources/11IPp.png" class="pull-right"/> Mapler.me offers some of the greatest additions to MapleStory such as the ability to view your own characters offline, 
	socialize with other maplers, and stays up to date on the latest updates from MapleStory.</p>
	<p>While exploring Mapler.me, you'll be able to participate with the hundreds of other maplers that are part of our network. Our goal is to make Mapler.me as MapleStory-like
	as the actual game, which is why we've worked with maplers in mind.</p>
	<p>Some of the amazing features all maplers can try out are:
		<ul>
			<li><b>Real-time</b> updated character profiles, avatars, and statistics</li>
			<li>Viewing all of your items, equipment, and inventories as well as their <b>complete</b> stats</li>
			<li>Connecting with other maplers by adding friends, and following other mapler's streams</li>
			<li>An <b>extensive API</b> offered to Mapler+ members to include our site onto their sites or applications</li>
			<li>Friendly, veteran maplers as our staff; here to help <b>you</b></li>
			<li>An application for Android and plugin for Wordpress</li>
			<li><i>And much, much more..</i></li>
		</ul>
	</p>
</div>

<div class="status">

<?php
$q = $__database->query("
(
	SELECT
		name
	FROM
		".DB_GMS.".characters 
	WHERE
		level > '30'
)
UNION
(
	SELECT
		name
	FROM
		".DB_EMS.".characters 
	WHERE
		level > '30'
)
ORDER BY
	rand()
	LIMIT 1
");
$cache = array();

while ($row = $q->fetch_assoc()) {
	$cache[] = $row;
}
$q->free();
?>
	<h1>#2 <small>Now onward to the next step, <span class="muted">making the most of Mapler.me</span></small></h1>
	<p>
	
	<?php
	foreach ($cache as $row) {
	?>
		<img src="//mapler.me/avatar/<?php echo $row['name']; ?>" class="pull-right"/>
	<?php
		}
	?>
	
	To be able to include your characters on Mapler.me, you must download our client, a lightweight application which monitors your MapleStory gameplay effortlessly. Our client allows real-time updates to be sent to Mapler.me keeping your characters updated.</p>
	
	<p>You can <a href="//<?php echo $domain; ?>/downloads/" target="blank">download our client and get started now.</a></p>
	
	<p>When first installing our client, you will go through a simple installer. During installation you will be asked to install WinPCap, a library required for relaying information from our client and servers securely. Once everything has finished, simply launch the client and login with your Mapler.me account!</p>
</div>

<div class="status">
	<h1>#3 <small>Seems you're all set, <span class="muted">what's next?</span></small></h1>
	<p><img src="http://cdn.mapler.me/etc/resources/bing.png" class="pull-right"/> Congrats! Once you've logged in with your Mapler.me account, you can now start adding your characters and show off that new Zakum Helmet you scrolled! If not, that's perfectly fine; you can continue to use Mapler.me without playing MapleStory. Some of these unrelated features will be discussed later in the guide.</p>
	<br/>
</div>

<div class="status">
	<h1>#4 <small>How to use Mapler.me, <span class="muted">step-by-step!</span></small></h1>
	<p><img src="//<?php echo $domain; ?>/inc/img/logo.new.png" class="pull-right" /> When you first launch the client, it will greet you with a notice requesting to launch MapleStory.</p>
	<p>With Mapler.me, we don't want to change the way you normally play MapleStory, so with the client running, you can login to MapleStory as you normally do.</p>
	<h2>Terms and their meanings:</h2>
	<ul style="list-style-image: url(http://cdn.mapler.me/etc/resources/medal.png);margin-left:40px;">
		<li><b>Last update:</b></li> Displays the current (or last) character and the last time it's been updated on Mapler.me. As Mapler.me updates in real-time, this will usually
		change every second.
		<li><b>"Awaiting account check, Happy Mapling!"</b></li>
		As soon as Mapler.me has received a response that MapleStory has successfully loaded, it will pause. Mapler.me will resume as soon as you've selected a channel.
		<li><b>"Successfully connected to servers or Cash Shop"</b></li>
		This is shown when you've finally selected a character. This is also shown when your character is in the Cash
		Shop. As soon as your character's mapler data has been encrypted and sent to Mapler.me, it will display a different message.
		<li><b>"Mapler101 has been added or updated!"</b></li>
		If everything has gone perfectly, you will be presented with this notification (except with your character's name)! If you refresh your <a href="//<?php echo $_loginaccount->GetUsername(); ?>.<?php echo $domain; ?>/">profile</a> or <a href="//<?php echo $domain; ?>/settings/characters/">characters settings page</a> your character(s) will now be added to Mapler.me
</div>

<div class="status">
	<h1>#5 <small>Exploring Mapler.me, <span class="muted">and you're on your way.</span></small></h1>
	<p><span class="pull-right" style="border-left:1px solid rgba(0,0,0,0.3);padding-left:10px;margin-left:10px;">You:<br/><img src="//mapler.me/avatar/<?php echo $main_char; ?>"></span> You've now gotten the basics of Mapler.me! There are many other features to the site such as social statuses, profiles, and friends, but you'll have to discover those yourself. If you've successfully added a character, you will see your character to the right!</p>
	<p>Good luck, and happy mapling!<br/>
	- Mapler.me Staff</p>
</div>

<div class="status">
Having trouble with the guide? Here's an alternative guide by Nexon Volunteer Maryse: <a href="http://imgur.com/a/zIqtV#AioUUDm">Mapler.me New Member Journey</a>
</div>

<?php
}
else {
?>

	<div class="featurette">
        <h2 class="featurette-heading">Effortless mapling. <span class="muted">Now it's possible.</span></h2>
        <p class="lead">Keep track of your <b>entire</b> life on MapleStory; from the equipment you wear & your friends.<br/>With Mapler.me, you can look back at your progress on any character and interact with other maplers seamlessly.
        </p>
    </div>

    <div class="featurette">
        <h2 class="featurette-heading">Never leave your buddies.. <span class="muted">fighting alone.</span></h2>
        <p class="lead">With <b>upcoming</b> applications for iOS and Android, you can continue a conversation with your buddies in-game, or interact with an expedition. The possibilities are <b>endless</b>.</p>
      </div>
      
      <div class="featurette">
        <h2 class="featurette-heading">We ♥ Security. <span class="muted">and so should you.</span></h2>
        <p class="lead">Mapler.me is developed from the ground up with a <b>secure</b> system to keep you and your characters safe.<br/>Any involvement with the black wings <b>isn't</b> tolerated on this site.</p>
      </div>
 
<?php 
}
require_once __DIR__.'/inc/footer.php';
?>