<?php

add_shortcode('rmb-tombstones-list', 'rmb_tombstones_list_handler');

/**
 * @param array $attr
 */
function rmb_tombstones_list_handler($attr = [])
{
    return rmb_tombstones_show_list($attr);
}

/**
 * @param array $attr
 */
function rmb_tombstones_show_list($attr = [])
{
    $table = <<<HTMLBLOCK
<div class="rmb-tombstones">
HTMLBLOCK;

    $query = new WP_Query([
        'post_type'      => 'rmb-comparable',
        'posts_per_page' => array_get($attr, 'limit', -1),
        'meta_key'       => 'recorded_date',
        'orderby'        => 'meta_value',
        'order'          => 'DESC',
    ]);

    if ($query->have_posts()) {
        // Start the Loop.
        while ($query->have_posts()) : $query->the_post();
        global $post;
        $rmb_post_custom = json_decode(base64_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0]), true);

        $id = array_get($rmb_post_custom, 'id');
        $title = array_get($rmb_post_custom, 'property.name');
        $img = array_get($rmb_post_custom, 'property.primaryPhoto.sizes.600_sq');
        $img_is_placeholder = array_get($rmb_post_custom, 'property.primaryPhoto.placeholder', false)  ? ' placeholder' : '';
        $location = implode(', ', [
                array_get($rmb_post_custom, 'property.address.geolookup.city'),
                array_get($rmb_post_custom, 'property.address.geolookup.province.short_name'),
            ]);
        $loan_closing_date = date('M j, Y', strtotime(array_get($rmb_post_custom, 'loan.closing_date.date', '')));
        $loan_amount = array_get($rmb_post_custom, 'loan.amount.formatted');
        $loan_type = array_get($rmb_post_custom, 'loan.type.name');
        $loan_term = array_get($rmb_post_custom, 'loan.term');
        $loan_term = $loan_term ? $loan_term . ' Months' : '';
        $loan_to_value = array_get($rmb_post_custom, 'loan.loan_to_value');

        $table .= <<<HTMLBLOCK
<div class="rmb-tombstone-block" data-rmb-id="tombstone-{$id}">
    <div class="rmb-tombstone-image{$img_is_placeholder}">
        <img src="{$img}">
    </div>
    <div class="rmb-tombstone-title">
        {$title}
    </div>
    <div class="rmb-tombstone-type">
        {$loan_type}
    </div>
    <div class="rmb-tombstone-location">
        {$location}
    </div>
    <div class="rmb-tombstone-date">
        {$loan_closing_date}
    </div>
    <div class="rmb-tombstone-price">
        {$loan_amount}
    </div>
    <div class="rmb-tombstone-term">
        {$loan_term}
    </div>
</div>
HTMLBLOCK;
        endwhile;
    }

    $table .= <<<HTMLBLOCK
</div>
HTMLBLOCK;

    return $table;
}
