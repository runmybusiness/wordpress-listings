<?php
/**
 * Plugin Name: RunMyBusiness Import
 * Description: This plugin imports data from RunMyBusiness.
 * Version: 1.0.0
 */

// Add custom post types
add_action( 'init', 'create_post_types' );
function create_post_types() {
  register_post_type('listings',
    array(
      'labels' => array(
        'name' => __( 'Listings' ),
        'singular_name' => __( 'Listing' ),
		'add_new_item' => __( 'Add New Listing' ),
		'edit_item' => __( 'Edit Listing' ),
      ),
      'public' => true,
      'has_archive' => true,
	  'supports' => array('title', 'editor')
    )
  );
}

// New menu submenu for plugin tools in Tools menu and plugin options in Settings menu
add_action('admin_menu', 'runmybusiness_update_admin_menu');
function runmybusiness_update_admin_menu() {
	add_management_page('RunMyBusiness Import', 'RunMyBusiness Import', 'manage_options', 'runmybusiness_tools', 'runmybusiness_tools_display');
	add_options_page('RunMyBusiness', 'RunMyBusiness Settings', 'manage_options', 'runmybusiness_setting_admin', 'create_admin_page');
}

// Displays RunMyBusiness tools layout
function runmybusiness_tools_display() {
	if (array_key_exists('run_update', $_GET) && $_GET['run_update'] == 1) {
		runmybusiness_do_update_content();
	}
	echo '<h2>RunMyBusiness Tools</h2>';
	echo '<p>Run update manually!</p>';
	echo '<a class="button" href="'.admin_url('tools.php?page=runmybusiness_tools&run_update=1').'">Run update</a>';
}

add_action('admin_init', 'register_runmybusiness_settings');
function register_runmybusiness_settings() {
	register_setting('runmybusiness_options', 'runmybusiness_options', 'runmybusiness_validation');
	add_settings_section('runmybusiness_settings', 'RunMyBusiness API Settings', 'runmybusiness_section_text', 'runmybusiness_setting_admin');
	add_settings_field('runmybusiness_api_username', 'Username', 'runmybusiness_api_username', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_api_password', 'Password', 'runmybusiness_api_password', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_api_recurrence', 'Cron Recurrence', 'runmybusiness_api_recurrence', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_api_url', 'API url', 'runmybusiness_api_url', 'runmybusiness_setting_admin', 'runmybusiness_settings');
}

function runmybusiness_section_text() {
}
function runmybusiness_api_username() {
	$options = get_option('runmybusiness_options');
	$username = isset($options['runmybusiness_username']) ? $options['runmybusiness_username'] : '';
?>
	<input type="text" id="runmybusiness_username" name="runmybusiness_options[runmybusiness_username]" value="<?php echo $username; ?>" style="width: 250px;" />
<?php
}
function runmybusiness_api_password() {
	$options = get_option('runmybusiness_options');
	$password = isset($options['runmybusiness_password']) ? $options['runmybusiness_password'] : '';
?>
	<input type="text" id="runmybusiness_password" name="runmybusiness_options[runmybusiness_password]" value="<?php echo $password; ?>" style="width: 250px;" />
<?php
}
function runmybusiness_api_recurrence() {
	$recurrence_options = array('hourly', 'twicedaily', 'daily');
	$options = get_option('runmybusiness_options');
	$recurrence = isset($options['runmybusiness_recurrence']) ? $options['runmybusiness_recurrence'] : '';
?>
	<select name="runmybusiness_options[runmybusiness_recurrence]">
		<option value="">None</option>
	<?php
	foreach ($recurrence_options as $key => $val) {
	?>
		<option value="<?php echo $val; ?>"<?php if ($recurrence == $val) echo ' SELECTED'; ?>><?php echo $val; ?></option>
	<?php
	}
	?>
	</select>
<?php
}
function runmybusiness_api_url() {
	$options = get_option('runmybusiness_options');
	$runmybusiness_url = isset($options['runmybusiness_url']) ? $options['runmybusiness_url'] : '';
?>
	<input type="text" id="runmybusiness_url" name="runmybusiness_options[runmybusiness_url]" value="<?php echo $runmybusiness_url; ?>" style="width: 500px;" />
<?php
}

// Register new action to run on cron execution
add_action('runmybusiness_update_content', 'runmybusiness_do_update_content');
function runmybusiness_do_update_content() {
	global $wpdb;
	global $user_ID;
	$runmybusiness_options = get_option('runmybusiness_options');
	$runmybusiness_url = isset($runmybusiness_options['runmybusiness_url']) ? $runmybusiness_options['runmybusiness_url'] : '';
	if ($runmybusiness_url) {
		$runmybusiness_datastring = file_get_contents($runmybusiness_url);
		$runmybusiness_data = json_decode($runmybusiness_datastring);
		if (!empty($runmybusiness_data->data)) {
			$posts_runmybusiness = array();
			$querystr = "SELECT `post_id`, `meta_value` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_id' AND `meta_value` > 0";
			$existing_posts = $wpdb->get_results($querystr, 'ARRAY_A');
			foreach ($existing_posts as $p) {
				$posts_runmybusiness[$p['post_id']] = $p['meta_value'];
			}
			$runmybusiness_ids = array();
			foreach ($runmybusiness_data->data as $item) {
				$runmybusiness_ids[] = $item->id;
				// Check if post already exists and update it
				$querystr = "SELECT `post_id` FROM $wpdb->postmeta WHERE `meta_key` = 'runmybusiness_id' AND `meta_value` = '$item->id'";
				$post = $wpdb->get_row($querystr);
				$address = '';
				foreach ($item->property->data->fields->data as $k) {
					if (isset($k->field->legacy_id) && $k->field->legacy_id == 'address') {
						$address = $k->value;
					}
				}
				$square_foot = 0;
				foreach ($item->property->data->fields->data as $k) {
					if (isset($k->field->legacy_id) && $k->field->legacy_id == 'square_foot') {
						$square_foot = (int)str_replace(',', '', $k->value);
					}
				}
				$price = '';
				foreach ($item->fields->data as $k) {
					if (isset($k->field->legacy_id) && $k->field->legacy_id == 'price') {
						$price = str_replace('$', '', $k->value);
						if ($price == "Best Offer") {
							// $price = '1';
						}
						else {
							// Edit the input to match database required values (its input is something like: 49,900,000, so we make it: 49900.000)
							$parts = explode(',', $price);
							$str = '';
							foreach ($parts as $key => $val) {
								if ($key < count($parts) - 1) {
									$str .= $val;
								}
								else {
									$str .= '.' . $val;
								}
							}
							$price = $str;
						}
					}
				}
				if ($post) {
					if (!empty($address))
						update_post_meta($post->post_id, 'location', $address);
					if (!empty($square_foot))
						update_post_meta($post->post_id, 'square_footage', $square_foot);
					if (!empty($price))
						update_post_meta($post->post_id, 'price', $price);
					if (!empty($item->property->data->type->name))
						update_post_meta($post->post_id, 'property_type', $item->property->data->type->name);
					if (!empty($item->transaction_type->name))
						update_post_meta($post->post_id, 'transaction_type', $item->transaction_type->name);
					if (!empty($item->status->friendly))
						update_post_meta($post->post_id, 'status', $item->status->friendly);
					update_post_meta($post->post_id, 'runmybusiness_datastring', str_replace(array('\"', '\/'), array('\\\\"', '\\\\/'), json_encode($item)));
				}
				else {
					// Else we create a new one
					$new_post = array(
						'post_title' => $item->property->data->name,
						'post_content' => '',
						'post_status' => 'publish',
						'post_author' => $user_ID,
						'post_type' => 'listing'
					);
					$post_id = wp_insert_post($new_post);
					add_post_meta($post_id, 'runmybusiness_id ', $item->id);
					if (!empty($address))
						add_post_meta($post_id, 'location', $address);
					if (!empty($square_foot))
						add_post_meta($post_id, 'square_footage', $square_foot);
					if (!empty($price))
						add_post_meta($post_id, 'price', $price);
					if (!empty($item->property->data->type->name))
						add_post_meta($post_id, 'property_type', $item->property->data->type->name);
					if (!empty($item->transaction_type->name))
						add_post_meta($post_id, 'transaction_type', $item->transaction_type->name);
					if (!empty($item->status->friendly))
						add_post_meta($post_id, 'status', $item->status->friendly);
					add_post_meta($post_id, 'runmybusiness_datastring', str_replace(array('\"', '\/'), array('\\\\"', '\\\\/'), json_encode($item)));
				}
			}
			// Delete the posts that are not present in runmybusiness anymore
			$post_to_delete = array_diff($posts_runmybusiness, $runmybusiness_ids);
			foreach ($post_to_delete as $post_id => $runmybusiness_id) {
				wp_delete_post($post_id);
			}
		}
	}
}

// Validating options
function runmybusiness_validation($input) {
	// Schedule runmybusiness cron
	// Schedule the event
	$options = get_option('runmybusiness_options');
	$recurrence = isset($options['runmybusiness_recurrence']) ? $options['runmybusiness_recurrence'] : '';
	if (!empty($recurrence)) {
		wp_unschedule_event(time(), 'runmybusiness_update_content');
		wp_schedule_event(time(), $recurrence, 'runmybusiness_update_content');
	}
	else {
		wp_unschedule_event(time(), 'runmybusiness_update_content');
	}
	return $input;
}

// Displays RunMyBusiness settings layout
function create_admin_page() {
	?>
	<div class="wrap">
		<?php screen_icon(); ?>
		<h2>RunMyBusiness API Settings</h2>
		<form method="post" action="options.php">
			<?php settings_fields('runmybusiness_options'); ?>
			<?php do_settings_sections('runmybusiness_setting_admin'); ?>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}

// Creating listing shortcode
function runmybusiness_shortcode() {
	global $wpdb;
	$querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'transaction_type'";
	$transaction_types = $wpdb->get_results($querystr, 'ARRAY_A');
	$querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'property_type'";
	$property_types = $wpdb->get_results($querystr, 'ARRAY_A');
	$querystr = "SELECT distinct `meta_value` FROM `wp_postmeta` WHERE `meta_key` = 'status'";
	$statuses = $wpdb->get_results($querystr, 'ARRAY_A');

	$meta_args = array();
	if (!empty($_POST)) {
		if (array_key_exists('runmybusiness_searchword', $_POST) && !empty($_POST['runmybusiness_searchword'])) {
			$_SESSION['runmybusiness_searchword'] = $_POST['runmybusiness_searchword'];
		}
		else {
			unset($_SESSION['runmybusiness_searchword']);
		}
		if (array_key_exists('runmybusiness_price_from', $_POST) && !empty($_POST['runmybusiness_price_from'])) {
			$_SESSION['runmybusiness_price_from'] = $_POST['runmybusiness_price_from'];
		}
		else {
			unset($_SESSION['runmybusiness_price_from']);
		}
		if (array_key_exists('runmybusiness_price_to', $_POST) && !empty($_POST['runmybusiness_price_to'])) {
			$_SESSION['runmybusiness_price_to'] = $_POST['runmybusiness_price_to'];
		}
		else {
			unset($_SESSION['runmybusiness_price_to']);
		}
		if (array_key_exists('runmybusiness_square_footage_from', $_POST) && !empty($_POST['runmybusiness_square_footage_from'])) {
			$_SESSION['runmybusiness_square_footage_from'] = $_POST['runmybusiness_square_footage_from'];
		}
		else {
			unset($_SESSION['runmybusiness_square_footage_from']);
		}
		if (array_key_exists('runmybusiness_square_footage_to', $_POST) && !empty($_POST['runmybusiness_square_footage_to'])) {
			$_SESSION['runmybusiness_square_footage_to'] = $_POST['runmybusiness_square_footage_to'];
		}
		else {
			unset($_SESSION['runmybusiness_square_footage_to']);
		}
		if (array_key_exists('runmybusiness_property_type', $_POST) && !empty($_POST['runmybusiness_property_type'])) {
			$_SESSION['runmybusiness_property_type'] = $_POST['runmybusiness_property_type'];
		}
		else {
			unset($_SESSION['runmybusiness_property_type']);
		}
		if (array_key_exists('runmybusiness_transaction_type', $_POST) && !empty($_POST['runmybusiness_transaction_type'])) {
			$_SESSION['runmybusiness_transaction_type'] = $_POST['runmybusiness_transaction_type'];
		}
		else {
			unset($_SESSION['runmybusiness_transaction_type']);
		}
		if (array_key_exists('runmybusiness_status', $_POST) && !empty($_POST['runmybusiness_status'])) {
			$_SESSION['runmybusiness_status'] = $_POST['runmybusiness_status'];
		}
		else {
			unset($_SESSION['runmybusiness_status']);
		}

		if (array_key_exists('runmybusiness_sort_by', $_POST)) {
			$_SESSION['runmybusiness_sort_by'] = $_POST['runmybusiness_sort_by'];
		}
		else {
			unset($_SESSION['runmybusiness_sort_by']);
		}
		if (array_key_exists('runmybusiness_sort_direction', $_POST)) {
			$_SESSION['runmybusiness_sort_direction'] = $_POST['runmybusiness_sort_direction'];
		}
		else {
			unset($_SESSION['runmybusiness_sort_direction']);
		}
		wp_redirect(get_permalink());
	}

	if (array_key_exists('runmybusiness_searchword', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'location',
							'value' => $_SESSION['runmybusiness_searchword'],
							'compare' => 'LIKE'
						);
	}
	if (array_key_exists('runmybusiness_price_from', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'price',
							'value' => (int)$_SESSION['runmybusiness_price_from'],
							'type' => 'numeric',
							'compare' => '>='
						);
	}
	if (array_key_exists('runmybusiness_price_to', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'price',
							'value' => (int)$_SESSION['runmybusiness_price_to'],
							'type' => 'numeric',
							'compare' => '<='
						);
	}
	if (array_key_exists('runmybusiness_square_footage_from', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'square_footage',
							'value' => (int)$_SESSION['runmybusiness_square_footage_from'],
							'type' => 'numeric',
							'compare' => '>='
						);
	}
	if (array_key_exists('runmybusiness_square_footage_to', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'square_footage',
							'value' => (int)$_SESSION['runmybusiness_square_footage_to'],
							'type' => 'numeric',
							'compare' => '<='
						);
	}
	if (array_key_exists('runmybusiness_property_type', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'property_type',
							'value' => $_SESSION['runmybusiness_property_type'],
							'compare' => 'LIKE'
						);
	}
	if (array_key_exists('runmybusiness_transaction_type', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'transaction_type',
							'value' => $_SESSION['runmybusiness_transaction_type'],
							'compare' => 'LIKE'
						);
	}
	if (array_key_exists('runmybusiness_status', $_SESSION)) {
		$meta_args[] = array(
							'key' => 'status',
							'value' => $_SESSION['runmybusiness_status'],
							'compare' => 'LIKE'
						);
	}

	$paged = (get_query_var('paged')) ? get_query_var('paged') : 1;
	$args = array(
		'post_type' => 'listing',
		'post_status' => 'publish',
		'orderby'   => 'meta_value_num',
		'meta_key'  => 'price',
		'order' => 'ASC',
		'posts_per_page' => 5,
		'paged' => $paged,
		'meta_query' => $meta_args
	);

	if (array_key_exists('runmybusiness_sort_by', $_SESSION)) {
		$args['meta_key'] = $_SESSION['runmybusiness_sort_by'];
	}
	if (array_key_exists('runmybusiness_sort_direction', $_SESSION)) {
		$args['order'] = $_SESSION['runmybusiness_sort_direction'];
	}
	$query = new WP_Query($args);
?>
	<script type="text/javascript">
		function reset_runmybusiness_form() {
			var oForm = document.getElementById("runmybusiness_form");
			var frm_elements = oForm.elements;
			for (i = 0; i < frm_elements.length; i++)
			{
				field_type = frm_elements[i].type.toLowerCase();
				switch (field_type)
				{
				case "text":
				case "password":
				case "textarea":
				case "hidden":
					frm_elements[i].value = "";
					break;
				case "radio":
				case "checkbox":
					if (frm_elements[i].checked)
					{
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
		<input type="text" name="runmybusiness_searchword" id="runmybusiness_searchword" value="<?php echo (array_key_exists('runmybusiness_searchword', $_SESSION)) ? $_SESSION['runmybusiness_searchword'] : ''; ?>" />
		<label for="runmybusiness_price_from">Price from</label>
		<input type="text" name="runmybusiness_price_from" id="runmybusiness_price_from" value="<?php echo (array_key_exists('runmybusiness_price_from', $_SESSION)) ? $_SESSION['runmybusiness_price_from'] : ''; ?>" />
		<label for="runmybusiness_price_to">Price to</label>
		<input type="text" name="runmybusiness_price_to" id="runmybusiness_price_to" value="<?php echo (array_key_exists('runmybusiness_price_to', $_SESSION)) ? $_SESSION['runmybusiness_price_to'] : ''; ?>" />
		<label for="runmybusiness_square_footage_from">Square footage from</label>
		<input type="text" name="runmybusiness_square_footage_from" id="runmybusiness_square_footage_from" value="<?php echo (array_key_exists('runmybusiness_square_footage_from', $_SESSION)) ? $_SESSION['runmybusiness_square_footage_from'] : ''; ?>" />
		<label for="runmybusiness_square_footage_to">Square footage to</label>
		<input type="text" name="runmybusiness_square_footage_to" id="runmybusiness_square_footage_to" value="<?php echo (array_key_exists('runmybusiness_square_footage_to', $_SESSION)) ? $_SESSION['runmybusiness_square_footage_to'] : ''; ?>" />
		<label for="runmybusiness_property_type">Property type</label>
		<select name="runmybusiness_property_type" id="runmybusiness_property_type">
			<option value=""></option>
		<?php
		foreach ($property_types as $type) {
		?>
			<option value="<?php echo $type["meta_value"]; ?>"<?php if (array_key_exists('runmybusiness_property_type', $_SESSION) && $_SESSION['runmybusiness_property_type'] == $type["meta_value"]) echo ' SELECTED'; ?>><?php echo $type["meta_value"]; ?></option>
		<?php
		}
		?>
		</select><br/>
		<label for="runmybusiness_status">Status</label>
		<select name="runmybusiness_status" id="runmybusiness_status">
			<option value=""></option>
		<?php
		foreach ($statuses as $status) {
		?>
			<option value="<?php echo $status["meta_value"]; ?>"<?php if (array_key_exists('runmybusiness_status', $_SESSION) && $_SESSION['runmybusiness_status'] == $status["meta_value"]) echo ' SELECTED'; ?>><?php echo $status["meta_value"]; ?></option>
		<?php
		}
		?>
		</select><br/><br/>
		<label for="runmybusiness_sort_by">Sort by</label>
		<select name="runmybusiness_sort_by" id="runmybusiness_sort_by">
			<option value="price"<?php if (array_key_exists('runmybusiness_sort_by', $_SESSION) && $_SESSION['runmybusiness_sort_by'] == 'price') echo ' SELECTED'; ?>>Price</option>
			<option value="square_footage"<?php if (array_key_exists('runmybusiness_sort_by', $_SESSION) && $_SESSION['runmybusiness_sort_by'] == 'square_footage') echo ' SELECTED'; ?>>Square footage</option>
		</select>
		<select name="runmybusiness_sort_direction" id="runmybusiness_sort_direction">
			<option value="asc"<?php if (array_key_exists('runmybusiness_sort_direction', $_SESSION) && $_SESSION['runmybusiness_sort_direction'] == 'asc') echo ' SELECTED'; ?>>Ascending</option>
			<option value="desc"<?php if (array_key_exists('runmybusiness_sort_direction', $_SESSION) && $_SESSION['runmybusiness_sort_direction'] == 'desc') echo ' SELECTED'; ?>>Descending</option>
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
			$runmybusiness_data = json_decode($datastring);
			$img = $runmybusiness_data->property->data->primaryPhoto->data->sizes->{120};
		?>
			<li>
				<img src="<?php echo $img; ?>" alt="<?php echo the_title(); ?>" />
				<div class="listing_title"><a href="<?php echo get_permalink($post_id); ?>"><?php echo the_title(); ?></a></div>
				<div>Price: <?php echo get_post_meta($post_id, 'price', true); if ((float)get_post_meta($post_id, 'price', true)  > 0) echo '$'; ?></div>
				<div>Square footage: <?php echo get_post_meta($post_id, 'square_footage', true); ?></div>
				<div>Property type: <?php echo get_post_meta($post_id, 'property_type', true); ?></div>
				<div>Transaction type: <?php echo get_post_meta($post_id, 'transaction_type', true); ?></div>
				<div>Status: <?php echo get_post_meta($post_id, 'status', true); ?></div>
			</li>
		<?php
		}
		?>
		</ul>
	<?php
	}
	custom_pagination($query->max_num_pages, "", $paged);
	?>

<?php
}
add_shortcode('runmybusiness', 'runmybusiness_shortcode');

add_filter('single_template', 'my_custom_template');
function my_custom_template($single) {
	global $wp_query, $post;
	/* Checks for single template by post type */
	if ($post->post_type == "listing"){
		if(file_exists(dirname( __FILE__ ) . '/single-listing.php'))
			return dirname( __FILE__ ) . '/single-listing.php';
	}
	return $single;
}

function register_session(){
	if( !session_id() )
		session_start();
}
add_action('init','register_session');

function custom_pagination($numpages = '', $pagerange = '', $paged='') {
	if (empty($pagerange)) {
		$pagerange = 2;
	}
	global $paged;
	if (empty($paged)) {
		$paged = 1;
	}
	if ($numpages == '') {
		global $wp_query;
		$numpages = $wp_query->max_num_pages;
		if(!$numpages) {
			$numpages = 1;
		}
	}

	$pagination_args = array(
		'base'            => get_pagenum_link(1) . '%_%',
		'format'          => 'page/%#%',
		'total'           => $numpages,
		'current'         => $paged,
		'show_all'        => False,
		'end_size'        => 1,
		'mid_size'        => $pagerange,
		'prev_next'       => True,
		'prev_text'       => __('&laquo;'),
		'next_text'       => __('&raquo;'),
		'type'            => 'plain',
		'add_args'        => false,
		'add_fragment'    => ''
	);

	$paginate_links = paginate_links($pagination_args);

	if ($paginate_links) {
		echo "<nav class='custom-pagination'>";
		echo $paginate_links;
		echo "</nav>";
	}
}
