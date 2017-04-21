<?php get_header(); ?>

    <div id="listing-filters">
        Sort By:
        <ul>
            <li>
                <a href="<?php echo add_query_arg(['sort_by' => 'price', 'sort_direction' => (get_query_var('sort_direction') == 'DESC' ? 'ASC' : 'DESC')]) ?>">
                    Price
                </a>
            </li>
            <li>
                <a href="<?php echo add_query_arg(['sort_by' => 'cap_rate', 'sort_direction' => (get_query_var('sort_direction') == 'DESC' ? 'ASC' : 'DESC')]) ?>">
                    Cap Rate
                </a>
            </li>
            <li>
                <a href="<?php echo add_query_arg(['sort_by' => 'listing_name', 'sort_direction' => (get_query_var('sort_direction') == 'ASC' ? 'DESC' : 'ASC')]) ?>">
                    Name
                </a>
            </li>
            <li>
                <a href="<?php echo add_query_arg(['sort_by' => 'state', 'sort_direction' => (get_query_var('sort_direction') == 'ASC' ? 'DESC' : 'ASC')]) ?>">
                    State
                </a>
            </li>
        </ul>
    </div>

    <div class="listings">
        <?php

        $query = new WP_Query([
            'post_type'      => 'rmb-listing',
            'posts_per_page' => -1,
            'meta_key'       => get_query_var('sort_by') ?: 'price',
            'orderby'        => (get_query_var('sort_by') == 'state' ? 'meta_value' : 'meta_value_num'),
            'order'          => (get_query_var('sort_direction') == 'ASC' ? 'ASC' : 'DESC'),
            'meta_query'     => get_query_var('filter_by') ? [
                [
                    'key'     => get_query_var('filter_by'),
                    'value'   => get_query_var('filter_value'),
                    'compare' => '=',
                ],
            ] : [],
        ]);

        if ($query->have_posts()):
            // Start the Loop.
            while ($query->have_posts()) : $query->the_post();
                global $post;
                $rmb_post_custom = json_decode(base64_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0]), true);

                $img = array_get($rmb_post_custom, 'property.primaryPhoto.sizes.120_sq', false) ?: '';

                $overview = '';
                foreach (array_get($rmb_post_custom, 'fields.data', []) as $k) {
                    if (isset($k->field->legacy_id) && $k->field->legacy_id == 'overview') {
                        $overview = $k->value;
                    }
                }
                ?>

                <div style="clear: both;" class="listing-item <?php if (array_get($rmb_post_custom, 'featured', false)): ?>listing-item-featured<?php endif; ?>"
                     data-latlon="<?php echo array_get($rmb_post_custom, 'property.address.geolookup.geo.latitude'); ?>_<?php echo array_get($rmb_post_custom,
                         'property.address.geolookup.geo.longitude'); ?>"
                     data-address="<?php echo array_get($rmb_post_custom, 'property.address.geolookup.formatted_address'); ?>"
                     data-link="<?php echo get_permalink(); ?>"
                >
                    <a href="<?php echo get_permalink(); ?>">
                        <img class="run-my-business-photo" src="<?php echo $img; ?>" alt="<?php the_title(); ?>" width="120" height="120"/>
                    </a>
                    <a href="<?php echo get_permalink(); ?>">
                        <h2 class="run-my-business-title"><?php the_title(); ?>,
                            <?php echo array_get($rmb_post_custom, 'property.address.geolookup.city'); ?>, <?php echo array_get($rmb_post_custom, 'property.address.geolookup.province.short_name'); ?>
                        </h2>
                    </a>
                    <ul class="run-my-business-details">
                        <li>
                            <?php echo get_post_meta($post->ID, 'property_type', true); ?> <?php echo get_post_meta($post->ID, 'transaction_type', true); ?>
                            <br>
                            <strong><span class="red">Price:</span> </strong>$<?php echo number_format(array_get($rmb_post_custom, 'price.min', 0)); ?>
                        </li>
                        <li>
                            <strong>Cap Rate: </strong> <?php echo array_get($rmb_post_custom, 'cap_rate'); ?>%
                            <br><strong>Building Size: </strong> <?php echo number_format(array_get($rmb_post_custom, 'property.building_size')); ?>
                        </li>
                        <li><a href="<?php echo get_permalink(); ?>" class="detail-button">View Details</a></li>
                    </ul>
                </div>

                <?php

                // End the loop.
            endwhile;

            echo custom_pagination();
        // If no content, include the "No posts found" template.
        else :
            get_template_part('content', 'none');
        endif;
        ?>
    </div>

<?php do_action('fusion_after_content'); ?>
<?php get_footer();

// Omit closing PHP tag to avoid "Headers already sent" issues.
