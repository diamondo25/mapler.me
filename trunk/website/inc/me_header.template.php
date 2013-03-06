	<div id="profile" class="row">
		<div id="header" class="span12" style="background: url('//<?php echo $domain; ?>/inc/img/back_panel.png') repeat top center">
			<div id="meta-nav">
				<div class="row">
					<div class="span12">
						<ul id="nav-left">
							<li>
								<a id="posts" href="//<?php echo $subdomain.".".$domain; ?>/characters"><img src="//<?php echo $domain; ?>/inc/img/icons/user.png"/ style="position:relative;top:2px;">
									<span class="count"><?php echo count($cache); ?></span> <span class="item">Characters</span>
								</a>
							</li>
							<li><a id="likes" href="#"><img src="//<?php echo $domain; ?>/inc/img/icons/star.png"/ style="position:relative;top:2px;"> <span class="count"></span> <span class="item">Achievements</span></a></li>
						</ul>
						<ul id="nav-right">
						</ul>
				   </div>
				</div>
			</div>

			<div id="profile-user-details">
				<div class="row">
					<div class="span6 offset3" style="margin-bottom:70px;">
						<div id="user-about" class="center">
							<h2><?php echo $__url_useraccount->GetNickname(); ?></h2>
<?php if ($__url_useraccount->GetBio() != null): ?>
							<ul id="user-external">
								<li>
									<span style="color: rgb(255, 255, 255); text-shadow: rgb(102, 102, 102) 1px 0px 3px;">
										<img src="//<?php echo $domain; ?>/inc/img/icons/comment.png" style="position: relative; top: 4px;"/> <?php echo $__url_useraccount->GetBio(); ?>
									</span>
								</li>

							</ul>
<?php endif; ?>
						</div>
					</div>
				</div>

				<div class="row">
					<div class="span2 offset5 center" style="margin-bottom: -70px;">
						<a href="//<?php echo $domain; ?>/player/<?php echo $main_character_name; ?>"><img id="default_character" src="<?php echo $main_character_image; ?>" alt="<?php echo $main_character_name; ?>" style="display:inline-block;background: rgb(255, 255, 255);
border-radius: 150px;
margin-bottom: -30px;
box-sizing: border-box;
-webkit-box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
box-shadow: rgba(0, 0, 0, 0.298039) 0px 0px 4px 0px;
border: 8px solid rgb(255, 255, 255);
border-image: initial;
text-align: center;" /> </a>
					</div>
				</div>
			</div>
		</div>
	</div>