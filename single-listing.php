<?php

get_header(); ?>

	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
			$str = get_post_meta($post->ID, 'runmybusiness_datastring');
			$datastring = $str[0];
			$runmybusiness_data = json_decode($datastring);
			// var_dump($runmybusiness_data);die;
			$img = $runmybusiness_data->property->data->primaryPhoto->data->sizes->{250};
			$overview = '';
			foreach ($runmybusiness_data->fields->data as $k) {
				if (isset($k->field->legacy_id) && $k->field->legacy_id == 'overview') {
					$overview = $k->value;
				}
			}
		?>
			<h2 class="run-my-business-title"><?php the_title(); ?></h2>
			<img class="run-my-business-photo" src="<?php echo $img; ?>" alt="<?php the_title(); ?>" />
			<ul class="run-my-business-details">
				<li>Property type: <?php echo get_post_meta($post->ID, 'property_type', true);?></li>
				<li>Monthly Rent: <?php echo get_post_meta($post->ID, 'price', true);?></li>
				<li>Address: <?php echo get_post_meta($post->ID, 'location', true);?></li>
				<li>Overview: <?php echo $overview;?></li>
				<div>Square footage: <?php echo get_post_meta($post->ID, 'square_footage', true); ?></div>
				<div>Transaction type: <?php echo get_post_meta($post->ID, 'transaction_type', true); ?></div>
				<div>Status: <?php echo get_post_meta($post->ID, 'status', true); ?></div>
			</ul>
		<?php
			// Previous/next post navigation.
			the_post_navigation( array(
				'next_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Next', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Next post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
				'prev_text' => '<span class="meta-nav" aria-hidden="true">' . __( 'Previous', 'twentyfifteen' ) . '</span> ' .
					'<span class="screen-reader-text">' . __( 'Previous post:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">%title</span>',
			) );

		// End the loop.
		endwhile;
		?>

		</main><!-- .site-main -->
	</div><!-- .content-area -->

<?php get_footer(); ?>
