<?php

include 'functions.php';

/*
 * The template for displaying archive pages
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * If you'd like to further customize these archive views, you may create a
 * new template file for each one. For example, tag.php (Tag archives),
 * category.php (Category archives), author.php (Author archives), etc.
 *
 * @link https://codex.wordpress.org/Template_Hierarchy
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

get_header(); ?>


	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

			<div class="search-box">
				<form><ul>
						<li>
					<h4>Refine Your Search</h4>
					<div style="width: 100%;"><div style="width: 30%; float: left; margin-right: 20px;"><input class="span10" placeholder="Search by Street Name, City, or Zipcode" name="property_search[keywords]" value="" type="text" id="form_property_search[keywords]" style="width: 70%; float: left;"/>	<input class="btn success" name="property_search[submit]" value="Search" type="submit" id="form_property_search[submit]" style="float: right;" /></div><div style="width: 65%; float: right;"></li>
					</ul>
					<ul>
						<li>
							<span>Monthly Rent: </span>
							<input style="width: 50px;" name="property_search[details][rent][min]" value="" type="text" id="form_property_search[details][rent][min]" />							to
							<input style="width: 50px;" name="property_search[details][rent][max]" value="" type="text" id="form_property_search[details][rent][max]" />
							<span>Bedrooms: </span>
							<input style="width: 50px;" name="property_search[details][bedrooms][min]" value="" type="text" id="form_property_search[details][bedrooms][min]" />							to
							<input style="width: 50px;" name="property_search[details][bedrooms][max]" value="" type="text" id="form_property_search[details][bedrooms][max]" />
							<span>Bathrooms: </span>
							<input style="width: 50px;" name="property_search[details][bathrooms][min]" value="" type="text" id="form_property_search[details][bathrooms][min]" />							to
							<input style="width: 50px;" name="property_search[details][bathrooms][max]" value="" type="text" id="form_property_search[details][bathrooms][max]" />						</li>

						<li></div></div>


							<div style="width: 100%; padding-left; 30px; margin-top: 20px;">
							<select name="property_search[order_by][field]" id="form_property_search[order_by][field]" style="float: left; width: 20%; margin-right: 2%;">
	<option value="sort">Sort by</option>
	<option value="city">Location</option>
	<option value="rent">Monthly Rent</option>
	<option value="id">Date Added</option>
	<option value="bedrooms">Bedrooms</option>
	<option value="bathrooms">Bathrooms</option>
	<option value="name" selected="selected">Property Name</option>
</select>
<select name="property_search[order_by][field]" id="form_property_search[order_by][field]" style="float: left; width: 20%; margin-right: 2%;">
	<option value="office">Office</option>
	<option value="vacaville">Vacaville Office</option>
	<option value="fairfield">Fairfield Office</option>
	<option value="benicia">Benicia Office</option>
</select>

							<select name="property_search[order_by][direction]" id="form_property_search[order_by][direction]" style="float: left; width: 20%; margin-right: 2%;">
	<option value="asc" selected="selected">Ascending</option>
	<option value="desc">Descending</option>
</select>

							<span style="float: left; width: 30%"><input name="property_search[details][pets]" value="yes" type="checkbox" id="form_property_search[details][pets]"/>							<span class="">Pets Negotiable</span></span>
						</li>
					</ul>

				</form>			</div></div>

			<div class="map">Insert Map Here</div>

			<div class="listings">

		<?php if (have_posts()) : ?>

			<?php
            // Start the Loop.
            while (have_posts()) : the_post();
                global $post;
                $rmb_post_custom = json_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0], true);

                $img = array_get($rmb_post_custom, 'property.data.primaryPhoto.data.sizes.250');

                $overview = '';
                foreach (array_get($rmb_post_custom, 'fields.data', []) as $k) {
                    if (isset($k->field->legacy_id) && $k->field->legacy_id == 'overview') {
                        $overview = $k->value;
                    }
                }

            ?>

			<div style="margin: 0 auto 0; width: 100%; padding: 20px; clear: both;" class="listing-item <?php if (array_get($rmb_post_custom, 'featured', false)): ?>listing-item-featured<?php endif; ?>">

				<a href="<?php echo get_permalink(); ?>">
					<img class="run-my-business-photo" src="<?php echo $img; ?>" alt="<?php the_title(); ?>" />
				</a>
<a href="<?php echo get_permalink(); ?>">
					<h2 class="run-my-business-title"><?php the_title(); ?>, 
						<?php echo array_get_from_field_array($rmb_post_custom['property']['data'], 'address', 'payload.address.normalized.city.long'); ?>, <?php echo array_get_from_field_array($rmb_post_custom['property']['data'], 'address', 'payload.address.normalized.state.short'); ?>
					</h2>
				</a>
				<ul class="run-my-business-details">
					<li><?php echo get_post_meta($post->ID, 'property_type', true);?> <?php echo get_post_meta($post->ID, 'transaction_type', true); ?></li>
					<li>
						<strong><span class="red">Monthly Rent:</span> </strong><?php echo getField($rmb_post_custom, 'price'); ?> - 
						<strong>Bedrooms: </strong></td><td><?php echo getField($rmb_post_custom['property']['data'], 'bedrooms'); ?> - 
						<strong>Bathrooms: </strong></td><td><?php echo getField($rmb_post_custom['property']['data'], 'bathrooms'); ?>
					</li>
					<li><a href="<?php echo get_permalink(); ?>" class="detail-button">View Details</a></li>
				</ul>
			</div>


			<?php

            // End the loop.
            endwhile;

        // If no content, include the "No posts found" template.
        else :
            get_template_part('content', 'none');

        endif;
        ?>

		</main><!-- .site-main -->
	</section><!-- .content-area -->

<?php get_footer(); ?>
