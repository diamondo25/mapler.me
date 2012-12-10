<?php
include_once('../../inc/header.php');
?>

<style>
.thumbnail {
	margin-bottom: 30px;
}
</style>

	<div class="row">
	<div class="span3">
	    <ul class="nav nav-tabs nav-stacked">
              <li><a href="#" data-toggle="collapse" data-target="#general">General</a></li>
              <li><a href="#" data-toggle="collapse" data-target="#notifications">Notifications</a></li>
              <li><a href="#" data-toggle="collapse" data-target="#memberships">Memberships</a></li>
              <li><a href="#" data-toggle="collapse" data-target="#privacy">Privacy</a></li>
            </ul>
      </div>
	<div class="span7">
	<div id="general" class="collapse in">
		<p class="lead">General <sup>(General settings and Options)</sup></p>
		<form class="form-horizontal">
  <div class="control-group">
    <label class="control-label" for="inputEmail">Name (Full Name)</label>
    <div class="controls">
      <input type="text" id="inputEmail" placeholder="<?php echo $_logindata['full_name']; ?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">Nickname</label>
    <div class="controls">
      <input type="password" id="inputPassword" placeholder="<?php echo $_logindata['nickname']; ?>">
    </div>
  </div>
  <div class="control-group">
    <label class="control-label" for="inputPassword">Email</label>
    <div class="controls">
      <input type="password" id="inputPassword" placeholder="<?php echo $_logindata['email']; ?>">
    </div>
  </div>
</form>
	</div>
	
	<div id="notifications" class="collapse">
		<p class="lead">Notifications <sup>(Notification Settings)</sup></p>
	</div>
	
	<div id="memberships" class="collapse">
		<p class="lead">Memberships <sup>(Badges and Memberships)</sup></p>
		
			<a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            
            <a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            
            <a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            
            <a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            <a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            <a href="#" class="thumbnail span2">
            	<img data-src="http://placehold.it/140" src="http://placehold.it/140">
            </a>
            
	</div>
	
	<div id="privacy" class="collapse">
		<p class="lead">Privacy <sup>(Privacy settings and security)</sup></p>
	</div>
	
	</div>
	
	</div>

<?php include_once('../../inc/footer.php'); ?>