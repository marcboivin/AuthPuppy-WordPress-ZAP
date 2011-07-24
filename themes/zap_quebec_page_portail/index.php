<?php get_header(); ?>

<section id="content">
	<h2>Bienvenu sur le réseau de ZAP Québec</h2>
</section>

<section id="nouvelles-zap">
	<?php 
	
			$rss = new WP_Widget_RSS();
			$args = array( 
						'title' => '>Dernières nouelles',
						'url' => 'http://zapquebec.org/feed/',
						'show_author' => 0, 
						'show_date' => 0, 
						'show_summary' => 1,
						'items' => 3,
						'before_widget' => '',
						'before_title' => '',
						'after_title' => '',
						'after_widget' => ''
						
					);
					
			$rss->widget($args, $args);
			
	?>
</section>



<?php get_footer(); ?>