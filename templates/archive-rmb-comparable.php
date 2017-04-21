<?php get_header(); ?>
    <div class="comparables">
        <?php

        $query = new WP_Query([
            'post_type'      => 'rmb-comparable',
            'posts_per_page' => -1,
        ]);

        if ($query->have_posts()):
            // Start the Loop.
            while ($query->have_posts()) : $query->the_post();
                global $post;
                $rmb_post_custom = json_decode(base64_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0]), true);
                ?>

                <div style="clear: both;" class="comparable-item" data-rmb-id="<?php echo array_get($rmb_post_custom, 'id'); ?>">
                    <img class="run-my-business-photo" src="<?php echo array_get($rmb_post_custom, 'property.primaryPhoto.sizes.120_sq'); ?>" alt="<?php the_title(); ?>" width="120" height="120"/>
                    <h2 class="run-my-business-title"><?php the_title(); ?>,
                        <?php echo array_get($rmb_post_custom, 'property.address.geolookup.city'); ?>, <?php echo array_get($rmb_post_custom, 'property.address.geolookup.province.short_name'); ?>
                    </h2>
                    <ul class="run-my-business-details">
                        <li>
                            <strong>Transaction Type: </strong> <?php echo array_get($rmb_post_custom, 'type.name'); ?>
                        </li>
                        <li>
                            <strong>Price: </strong> <?php echo array_get($rmb_post_custom, 'recorded_price.formatted'); ?>
                        </li>
                        <li>
                            <strong>Cap Rate: </strong> <?php echo array_get($rmb_post_custom, 'cap_rate'); ?>%
                        </li>
                        <li>
                            <strong>Date: </strong> <?php echo array_get($rmb_post_custom, 'recorded_date'); ?>
                        </li>
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
