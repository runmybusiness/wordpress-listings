<?php

// Creating listing shortcode
function runmybusiness_shortcode()
{
    global $wpdb;
    $querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'transaction_type'";
    $transaction_types = $wpdb->get_results($querystr, 'ARRAY_A');
    $querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'property_type'";
    $property_types = $wpdb->get_results($querystr, 'ARRAY_A');
    $querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'status'";
    $statuses = $wpdb->get_results($querystr, 'ARRAY_A');

    $meta_args = [];
    if (! empty($_POST)) {
        if (array_key_exists('runmybusiness_searchword', $_POST) && ! empty($_POST['runmybusiness_searchword'])) {
            $_SESSION['runmybusiness_searchword'] = $_POST['runmybusiness_searchword'];
        } else {
            unset($_SESSION['runmybusiness_searchword']);
        }
        if (array_key_exists('runmybusiness_price_from', $_POST) && ! empty($_POST['runmybusiness_price_from'])) {
            $_SESSION['runmybusiness_price_from'] = $_POST['runmybusiness_price_from'];
        } else {
            unset($_SESSION['runmybusiness_price_from']);
        }
        if (array_key_exists('runmybusiness_price_to', $_POST) && ! empty($_POST['runmybusiness_price_to'])) {
            $_SESSION['runmybusiness_price_to'] = $_POST['runmybusiness_price_to'];
        } else {
            unset($_SESSION['runmybusiness_price_to']);
        }
        if (array_key_exists('runmybusiness_square_footage_from', $_POST) && ! empty($_POST['runmybusiness_square_footage_from'])) {
            $_SESSION['runmybusiness_square_footage_from'] = $_POST['runmybusiness_square_footage_from'];
        } else {
            unset($_SESSION['runmybusiness_square_footage_from']);
        }
        if (array_key_exists('runmybusiness_square_footage_to', $_POST) && ! empty($_POST['runmybusiness_square_footage_to'])) {
            $_SESSION['runmybusiness_square_footage_to'] = $_POST['runmybusiness_square_footage_to'];
        } else {
            unset($_SESSION['runmybusiness_square_footage_to']);
        }
        if (array_key_exists('runmybusiness_property_type', $_POST) && ! empty($_POST['runmybusiness_property_type'])) {
            $_SESSION['runmybusiness_property_type'] = $_POST['runmybusiness_property_type'];
        } else {
            unset($_SESSION['runmybusiness_property_type']);
        }
        if (array_key_exists('runmybusiness_transaction_type', $_POST) && ! empty($_POST['runmybusiness_transaction_type'])) {
            $_SESSION['runmybusiness_transaction_type'] = $_POST['runmybusiness_transaction_type'];
        } else {
            unset($_SESSION['runmybusiness_transaction_type']);
        }
        if (array_key_exists('runmybusiness_status', $_POST) && ! empty($_POST['runmybusiness_status'])) {
            $_SESSION['runmybusiness_status'] = $_POST['runmybusiness_status'];
        } else {
            unset($_SESSION['runmybusiness_status']);
        }

        if (array_key_exists('runmybusiness_sort_by', $_POST)) {
            $_SESSION['runmybusiness_sort_by'] = $_POST['runmybusiness_sort_by'];
        } else {
            unset($_SESSION['runmybusiness_sort_by']);
        }
        if (array_key_exists('runmybusiness_sort_direction', $_POST)) {
            $_SESSION['runmybusiness_sort_direction'] = $_POST['runmybusiness_sort_direction'];
        } else {
            unset($_SESSION['runmybusiness_sort_direction']);
        }
        wp_redirect(get_permalink());
    }

    if (array_key_exists('runmybusiness_searchword', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'location',
            'value'   => $_SESSION['runmybusiness_searchword'],
            'compare' => 'LIKE',
        ];
    }
    if (array_key_exists('runmybusiness_price_from', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'price',
            'value'   => (int)$_SESSION['runmybusiness_price_from'],
            'type'    => 'numeric',
            'compare' => '>=',
        ];
    }
    if (array_key_exists('runmybusiness_price_to', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'price',
            'value'   => (int)$_SESSION['runmybusiness_price_to'],
            'type'    => 'numeric',
            'compare' => '<=',
        ];
    }
    if (array_key_exists('runmybusiness_square_footage_from', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'square_footage',
            'value'   => (int)$_SESSION['runmybusiness_square_footage_from'],
            'type'    => 'numeric',
            'compare' => '>=',
        ];
    }
    if (array_key_exists('runmybusiness_square_footage_to', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'square_footage',
            'value'   => (int)$_SESSION['runmybusiness_square_footage_to'],
            'type'    => 'numeric',
            'compare' => '<=',
        ];
    }
    if (array_key_exists('runmybusiness_property_type', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'property_type',
            'value'   => $_SESSION['runmybusiness_property_type'],
            'compare' => 'LIKE',
        ];
    }
    if (array_key_exists('runmybusiness_transaction_type', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'transaction_type',
            'value'   => $_SESSION['runmybusiness_transaction_type'],
            'compare' => 'LIKE',
        ];
    }
    if (array_key_exists('runmybusiness_status', $_SESSION)) {
        $meta_args[] = [
            'key'     => 'status',
            'value'   => $_SESSION['runmybusiness_status'],
            'compare' => 'LIKE',
        ];
    }

    $paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
    $args = [
        'post_type'      => 'rmb-listing',
        'post_status'    => 'publish',
        'orderby'        => 'meta_value_num',
        'meta_key'       => 'price',
        'order'          => 'ASC',
        'posts_per_page' => 5,
        'paged'          => $paged,
        'meta_query'     => $meta_args,
    ];

    if (array_key_exists('runmybusiness_sort_by', $_SESSION)) {
        $args['meta_key'] = $_SESSION['runmybusiness_sort_by'];
    }
    if (array_key_exists('runmybusiness_sort_direction', $_SESSION)) {
        $args['order'] = $_SESSION['runmybusiness_sort_direction'];
    }
    $query = new WP_Query($args); ?>
    <script type="text/javascript">
        function reset_runmybusiness_form() {
            var oForm = document.getElementById("runmybusiness_form");
            var frm_elements = oForm.elements;
            for (i = 0; i < frm_elements.length; i++) {
                field_type = frm_elements[i].type.toLowerCase();
                switch (field_type) {
                    case "text":
                    case "password":
                    case "textarea":
                    case "hidden":
                        frm_elements[i].value = "";
                        break;
                    case "radio":
                    case "checkbox":
                        if (frm_elements[i].checked) {
                            frm_elements[i].checked = false;
                        }
                        break;
                    case "select-one":
                    case "select-multi":
                        frm_elements[i].selectedIndex = -1;
                        break;
                    default:
                        break;
                }
            }
            oForm.submit();
        }
    </script>
    <form method="post" id="runmybusiness_form" action="<?php echo get_permalink(); ?>">
        <label for="runmybusiness_searchword">Search</label>
        <input type="text" name="runmybusiness_searchword" id="runmybusiness_searchword" value="<?php echo (array_key_exists('runmybusiness_searchword',
            $_SESSION)) ? $_SESSION['runmybusiness_searchword'] : ''; ?>"/>
        <label for="runmybusiness_price_from">Price from</label>
        <input type="text" name="runmybusiness_price_from" id="runmybusiness_price_from" value="<?php echo (array_key_exists('runmybusiness_price_from',
            $_SESSION)) ? $_SESSION['runmybusiness_price_from'] : ''; ?>"/>
        <label for="runmybusiness_price_to">Price to</label>
        <input type="text" name="runmybusiness_price_to" id="runmybusiness_price_to" value="<?php echo (array_key_exists('runmybusiness_price_to',
            $_SESSION)) ? $_SESSION['runmybusiness_price_to'] : ''; ?>"/>
        <label for="runmybusiness_square_footage_from">Square footage from</label>
        <input type="text" name="runmybusiness_square_footage_from" id="runmybusiness_square_footage_from" value="<?php echo (array_key_exists('runmybusiness_square_footage_from',
            $_SESSION)) ? $_SESSION['runmybusiness_square_footage_from'] : ''; ?>"/>
        <label for="runmybusiness_square_footage_to">Square footage to</label>
        <input type="text" name="runmybusiness_square_footage_to" id="runmybusiness_square_footage_to" value="<?php echo (array_key_exists('runmybusiness_square_footage_to',
            $_SESSION)) ? $_SESSION['runmybusiness_square_footage_to'] : ''; ?>"/>
        <label for="runmybusiness_property_type">Property type</label>
        <select name="runmybusiness_property_type" id="runmybusiness_property_type">
            <option value=""></option>
            <?php
            foreach ($property_types as $type) {
                ?>
                <option value="<?php echo $type['meta_value']; ?>"<?php if (array_key_exists('runmybusiness_property_type', $_SESSION) && $_SESSION['runmybusiness_property_type'] == $type['meta_value']) {
                    echo ' SELECTED';
                } ?>

                <?php echo $type['meta_value']; ?></option>
                <?php

            } ?>
        </select><br/>
        <label for="runmybusiness_status">Status</label>
        <select name="runmybusiness_status" id="runmybusiness_status">
            <option value=""></option>
            <?php
            foreach ($statuses as $status) {
                ?>
                <option value="<?php echo $status['meta_value']; ?>"<?php if (array_key_exists('runmybusiness_status', $_SESSION) && $_SESSION['runmybusiness_status'] == $status['meta_value']) {
                    echo ' SELECTED';
                } ?>

                <?php echo $status['meta_value']; ?></option>
                <?php

            } ?>
        </select><br/><br/>
        <label for="runmybusiness_sort_by">Sort by</label>
        <select name="runmybusiness_sort_by" id="runmybusiness_sort_by">
            <option value="price"<?php if (array_key_exists('runmybusiness_sort_by', $_SESSION) && $_SESSION['runmybusiness_sort_by'] == 'price') {
                echo ' SELECTED';
            } ?>
                    Price
            </option>
            <option value="square_footage"<?php if (array_key_exists('runmybusiness_sort_by', $_SESSION) && $_SESSION['runmybusiness_sort_by'] == 'square_footage') {
                echo ' SELECTED';
            } ?>
                    Square footage
            </option>
        </select>
        <select name="runmybusiness_sort_direction" id="runmybusiness_sort_direction">
            <option value="asc"<?php if (array_key_exists('runmybusiness_sort_direction', $_SESSION) && $_SESSION['runmybusiness_sort_direction'] == 'asc') {
                echo ' SELECTED';
            } ?>
                    Ascending
            </option>
            <option value="desc"<?php if (array_key_exists('runmybusiness_sort_direction', $_SESSION) && $_SESSION['runmybusiness_sort_direction'] == 'desc') {
                echo ' SELECTED';
            } ?>
                    Descending
            </option>
        </select><br/>
        <button type="submit">Search</button>
        <button onClick="javascrit: reset_runmybusiness_form();">Reset</button>
    </form>
    <br/>
    <?php
    if ($query->have_posts()) {
        ?>
        <ul class="listing_listing">
            <?php
            while ($query->have_posts()) {
                $query->the_post();
                $post_id = get_the_ID();
                $str = get_post_meta($post_id, 'runmybusiness_datastring');
                $datastring = $str[0];
                $runmybusiness_data = json_decode(base64_decode($datastring));
                $img = $runmybusiness_data->property->data->primaryPhoto->data->sizes->{120}; ?>
                <li>
                    <img src="<?php echo $img; ?>" alt="<?php echo the_title(); ?>"/>
                    <div class="listing_title"><a href="<?php echo get_permalink($post_id); ?>"><?php echo the_title(); ?></a></div>
                    <div>Square footage: <?php echo get_post_meta($post_id, 'square_footage', true); ?></div>
                    <div>Property type: <?php echo get_post_meta($post_id, 'property_type', true); ?></div>
                    <div>Transaction type: <?php echo get_post_meta($post_id, 'transaction_type', true); ?></div>
                    <div>Status: <?php echo get_post_meta($post_id, 'status', true); ?></div>
                </li>
                <?php

            } ?>
        </ul>
        <?php

    }
    custom_pagination($query->max_num_pages, '', $paged); ?>

    <?php

}

add_shortcode('runmybusiness', 'runmybusiness_shortcode');
