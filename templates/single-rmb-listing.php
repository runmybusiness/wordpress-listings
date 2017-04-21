<?php get_header();
// Start the loop.
while (have_posts()) :
    the_post();
    $runmybusiness_data = json_decode(base64_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0]), true);
    $img = array_get($runmybusiness_data, 'property.primaryPhoto.sizes.250_sq');
    $secondaryImages = [];

    foreach (array_get($runmybusiness_data, 'property.photos.data', []) as $photo) {
        if (! $photo['primary']) {
            $secondaryImages[$photo['id']] = $photo;
        }
    }
    ?>

    <div class="listing-sidebar">
        <?php if (! empty($img)): ?>
            <a href="<?php echo array_get($runmybusiness_data, 'property.primaryPhoto.sizes.1200'); ?>" rel="lightbox">
                <img class="run-my-business-photo" src="<?php echo $img; ?>" alt="<?php the_title(); ?>" class="aligncenter"/>
            </a>
        <?php else: ?>
            <!-- Place Alternate Image Here If No Image Exists -->
        <?php endif; ?>

        <br><br>

        <?php if (! empty($secondaryImages)): ?>
            <ul class="secondary-images">
                <?php foreach ($secondaryImages as $image): ?>
                    <li>
                        <a href="<?php echo $image['sizes']['1200']; ?>" rel="lightbox">
                            <img src="<?php echo $image['sizes']['64_sq']; ?>" title="<?php echo $image['description']; ?>" width="48" height="48">
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>
    </div>
    <div class="listing-main">

        <div class="single-map" id="single-map"></div>

        <table class="run-my-business-details">
            <?php if (array_get($runmybusiness_data, 'property.address.geolookup.formatted_address')): ?>
                <tr>
                    <td><strong><span class="blue">Address:</span> </strong></td>
                    <td><?php echo array_get($runmybusiness_data, 'property.address.geolookup.formatted_address'); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (array_get($runmybusiness_data, 'overview')): ?>
                <tr>
                    <td><strong><span class="blue">Overview:</span> </strong></td>
                    <td><?php echo array_get($runmybusiness_data, 'overview'); ?></td>
                </tr>
            <?php endif; ?>
            <tr>
                <td style="width: 35%; padding-right: 10px;"><strong>Property Type: </strong></td>
                <td><?php echo get_post_meta($post->ID, 'property_type', true); ?></td>
            </tr>
            <?php if (array_get($runmybusiness_data, 'price.min', 0)): ?>
                <tr>
                    <td><strong>Price: </strong></td>
                    <td>$<?php echo number_format(array_get($runmybusiness_data, 'price.min', 0)); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (array_get($runmybusiness_data, 'property.building_size')): ?>
                <tr>
                    <td><strong>Building Size: </strong></td>
                    <td><?php echo number_format(array_get($runmybusiness_data, 'property.building_size')); ?></td>
                </tr>
            <?php endif; ?>
            <?php if (array_get($runmybusiness_data, 'salient_points')): ?>
                <tr>
                    <td><strong>Salient Points: </strong></td>
                    <td><?php echo array_get($runmybusiness_data, 'salient_points'); ?></td>
                </tr>
            <?php endif; ?>
        </table>

        <?php if (array_get($runmybusiness_data, 'property.address.geolookup.geo')): ?>
            <script>

                var map;
                function initMap() {
                    map = new google.maps.Map(document.getElementById('single-map'), {
                        center: {
                            lat: <?php echo array_get($runmybusiness_data, 'property.address.geolookup.geo.latitude') ?>,
                            lng: <?php echo array_get($runmybusiness_data, 'property.address.geolookup.geo.longitude') ?>
                        },
                        zoom: 13
                    });

                    marker = new google.maps.Marker({
                        map: map,
                        draggable: false,
                        animation: google.maps.Animation.DROP,
                        position: {
                            lat: <?php echo array_get($runmybusiness_data, 'property.address.geolookup.geo.latitude') ?>,
                            lng: <?php echo array_get($runmybusiness_data, 'property.address.geolookup.geo.longitude') ?>
                        }
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
    </div>

    <?php
    // End the loop.
endwhile;

do_action('fusion_after_content');
get_footer();

// Omit closing PHP tag to avoid "Headers already sent" issues.
