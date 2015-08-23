<?php

include 'functions.php';

get_header(); ?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main" role="main">

		<?php
		// Start the loop.
		while ( have_posts() ) : the_post();
			$runmybusiness_data = json_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0], true);
			$img = array_get($runmybusiness_data, 'property.data.primaryPhoto.data.sizes.250_sq');
			$secondaryImages = [];

			foreach(array_get($runmybusiness_data, 'property.data.photos.data', []) as $photo)
			{
				if( ! $photo['primary'])
				{
					$secondaryImages[$photo['id']] = $photo;
				}
			}

			$overview = '';
			foreach (array_get($runmybusiness_data, 'fields.data', []) as $k)
			{
				if (isset($k['field']['legacy_id']) && $k['field']['legacy_id'] == 'overview')
				{
					$overview = $k['value'];
				}
			}
		?>
			<h2 class="run-my-business-title"><?php the_title(); ?></h2>
			<div style="float: left; width: 22%; margin-right: 3%;">
				<?php if(!empty($img)): ?>
				<a href="<?php echo array_get($runmybusiness_data, 'property.data.primaryPhoto.data.sizes.1200'); ?>" rel="lightbox">
					<img class="run-my-business-photo" src="<?php echo array_get($runmybusiness_data, 'property.data.primaryPhoto.data.sizes.250_sq'); ?>" alt="<?php the_title(); ?>" class="aligncenter"/>
				</a>
				<?php else: ?>
					Insert Missing Image Placeholder Here
				<?php endif; ?>
				<p style="padding-top: 20px">
					<strong>Overview: </strong><br><?php echo $overview;?>
				</p>
			</div>
			<div style="float: right; width: 75%">

			<?php if(!empty(array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo'))): ?>
			<div class="single-map" id="single-map"></div>
			<?php endif; ?>

			<table class="run-my-business-details">
				<tr><td><strong><span class="blue">Address:</span> </strong></td><td><?php echo getField($runmybusiness_data['property']['data'], 'address'); ?></td></tr>
				<tr><td style="width: 30%;"><strong>Property Type: </strong></td><td><?php echo get_post_meta($post->ID, 'property_type', true);?></td></tr>
				<tr><td><strong>Monthly Rent: </strong></td><td><?php echo getField($runmybusiness_data, 'price'); ?></td></tr>
				<tr><td><strong>Square footage: </strong></td><td><?php echo getField($runmybusiness_data['property']['data'], 'square_foot'); ?></td></tr>
				<tr><td><strong>Bedrooms: </strong></td><td><?php echo getField($runmybusiness_data['property']['data'], 'bedrooms'); ?></td></tr>
				<tr><td><strong>Bathrooms: </strong></td><td><?php echo getField($runmybusiness_data['property']['data'], 'bathrooms'); ?></td></tr>
				<tr><td><strong>Transaction type: </strong></td><td><?php echo get_post_meta($post->ID, 'transaction_type', true); ?></td></tr>
				<tr><td><strong>Status: </strong></td><td><?php echo get_post_meta($post->ID, 'status', true); ?></td></tr>
				<tr><td><strong>Salient Points: </strong></td><td><?php echo getField($runmybusiness_data, 'salient_points'); ?></td></tr>
			</table>

			<?php if(!empty($secondaryImages)): ?>
			<ul class="secondary-images">
				<?php foreach($secondaryImages as $image): ?>
				<li>
					<a href="<?php echo $image['sizes']['1200']; ?>" rel="lightbox">
						<img src="<?php echo $image['sizes']['120_sq']; ?>" title="<?php echo $image['description']; ?>">
					</a>
				</li>
				<?php endforeach; ?>
			</ul>
			<?php endif; ?>

			<?php echo do_shortcode('[shareaholic app="share_buttons" id="19233840”]'); ?>

			<?php if(!empty(array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo'))): ?>
			<script>

				var map;
				function initMap() {
				  map = new google.maps.Map(document.getElementById('single-map'), {
				    center: {lat: <?php echo array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo.lat') ?>, lng: <?php echo array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo.lng') ?>},
				    zoom: 13
				  });

				  marker = new google.maps.Marker({
				    map: map,
				    draggable: false,
				    animation: google.maps.Animation.DROP,
				    position: {lat: <?php echo array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo.lat') ?>, lng: <?php echo array_get(getFieldArray($runmybusiness_data['property']['data'], 'address'), 'payload.geo.lng') ?>}
				  });
				  marker.addListener('click', toggleBounce);
				}

				function toggleBounce() {
				  if (marker.getAnimation() !== null) {
				    marker.setAnimation(null);
				  } else {
				    marker.setAnimation(google.maps.Animation.BOUNCE);
				  }
				}

    		</script>
    		<script src="https://maps.googleapis.com/maps/api/js?callback=initMap" async defer></script>
			<?php endif; ?>

		<?php
			// Previous/next post navigation.
			the_post_navigation( array(
				'prev_text' => '<span class="meta-nav-prev" aria-hidden="true">' . __( '', 'twentyfifteen' ) . '</span> ' .
					'<span style="float: left; padding: 20px;"><span class="screen-reader-text" style="float: left">' . __( 'Previous Listing:', 'twentyfifteen' ) . '</span></span> ' .
					'<span class="post-title">Previous Listing: %title</span>',

				'next_text' => '<span class="meta-nav-next" aria-hidden="true">' . __( '', 'twentyfifteen' ) . '</span> ' .
					'<span style="float: right; padding: 20px;"><span class="screen-reader-text">' . __( 'Next Listing:', 'twentyfifteen' ) . '</span> ' .
					'<span class="post-title">Next Listing: %title</span></span>',
			) );

		// End the loop.
		endwhile;
		?>

		</main><!-- .site-main -->
	</div></div><!-- .content-area -->

<?php get_footer(); ?>
