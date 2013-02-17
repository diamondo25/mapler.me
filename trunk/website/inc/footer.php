		<div style="clear:both;"></div>	
		<footer style="">
			<p>
				<a href="//<?php echo $domain; ?>/intro/">About</a>
				<span style="color:#333;">&copy; 2012-2013 Mapler.me</span> <span style="display:none;">– In partnership with <a href="//nexon.net/" style="margin:0;">Nexon America</a></span> – 
				<?php echo $__database->QueriesRan(); ?> queries ran.
			</p>
		</footer>

	</div>

	<!-- Le javascript
	================================================== -->
	<!-- Placed at the end of the document so the pages load faster -->
	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.8.2/jquery.js" type="text/javascript"></script>
	<script src="//<?php echo $domain; ?>/inc/js/scripts.js" type="text/javascript"></script>
	<script src="//cdnjs.cloudflare.com/ajax/libs/modernizr/2.6.2/modernizr.min.js" type="text/javascript"></script>
	<script src="//isotope.metafizzy.co/jquery.isotope.min.js" type="text/javascript"></script>
	
	<script>
    $(document).ready(function() {
        var container = $('#character-wall');

        if (Modernizr.touch) {
            container.masonry({
                itemSelector : '.character-brick',
                gutterWidth: 20,
                
                    isFitWidth: true,
                
                isAnimated: false
            }).imagesLoaded(function() {
                container.masonry('reload');
            });
        }
		else {
            container.masonry({
                itemSelector : '.character',
                gutterWidth: 14,
                
                    isFitWidth: true,
                
                isAnimated: true
            }).imagesLoaded(function() {
                container.masonry('reload');
            });
        }
		
		container.isotope({
		  // options
		  itemSelector : '.item',
		  layoutMode : 'fitRows'
		});

    });
</script>

</body>
</html>

