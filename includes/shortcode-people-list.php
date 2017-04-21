<?php

add_shortcode('rmb-people-list', 'rmb_people_list_handler');

/**
 * @param array $attr
 */
function rmb_people_list_handler($attr = [])
{
    return rmb_people_show_list($attr);
}

/**
 * @param array $attr
 */
function rmb_people_show_list($attr = [])
{
    $table = <<<HTMLBLOCK
<div class="rmb-people">
HTMLBLOCK;

    $query = new WP_Query([
        'post_type'      => 'rmb-person',
        'posts_per_page' => array_get($attr, 'limit', -1),
        'orderby'        => 'title',
        'order'          => array_get($attr, 'order', 'ASC'),
    ]);

    if ($query->have_posts()) {
        // Start the Loop.
        while ($query->have_posts()) : $query->the_post();
        global $post;
        $rmb_post_custom = json_decode(base64_decode(get_post_meta($post->ID, 'runmybusiness_datastring')[0]), true);

        $id = array_get($rmb_post_custom, 'id');
        $name = array_get($rmb_post_custom, 'name.full');
        $title = array_get($rmb_post_custom, 'title');
        $email = array_get($rmb_post_custom, 'email');
        $img = array_get($rmb_post_custom, 'photo.sizes.600_sq');
        $img_is_placeholder = array_get($rmb_post_custom, 'photo.placeholder', false) ? ' placeholder' : '';
        $bio = nl2br(array_get($rmb_post_custom, 'bio'));
        $phone = array_get($rmb_post_custom, 'phone_numbers.0');

        $table .= <<<HTMLBLOCK
<div class="rmb-person-block" data-rmb-id="person-{$id}">
    <div class="rmb-person-image{$img_is_placeholder}">
        <img src="{$img}">
    </div>
    <div class="rmb-person-name">
        <a href="mailto:{$email}">
            {$name}
        </a>
    </div>
    <div class="rmb-person-title">
        {$title}
    </div>
    <div class="rmb-person-phone">
        <a href="tel:{$phone}">
            {$phone}
        </a>
    </div>
    <div class="rmb-person-bio">
        {$bio}
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
