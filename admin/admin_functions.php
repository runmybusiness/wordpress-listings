<?php

// New menu submenu for plugin tools in Tools menu and plugin options in Settings menu
add_action('admin_menu', 'runmybusiness_update_admin_menu');
function runmybusiness_update_admin_menu() {
	add_management_page('RunMyBusiness Sync', 'RunMyBusiness Sync', 'manage_options', 'runmybusiness_tools', 'runmybusiness_tools_display');
	add_options_page('RunMyBusiness', 'RunMyBusiness Settings', 'manage_options', 'runmybusiness_setting_admin', 'create_admin_page');
}

// Displays RunMyBusiness tools layout
function runmybusiness_tools_display()
{
	if (array_key_exists('run_update', $_GET) && $_GET['run_update'] == 1)
	{
		runmybusiness_do_update_content();
	}
	echo '<h2>RunMyBusiness Tools</h2>';
	echo '<p>Run sync manually!</p>';
	echo '<a class="button" href="'.admin_url('tools.php?page=runmybusiness_tools&run_update=1').'">Run update</a>';
}

add_action('admin_init', 'register_runmybusiness_settings');
function register_runmybusiness_settings() {
	register_setting('runmybusiness_options', 'runmybusiness_options', 'runmybusiness_validation');
	add_settings_section('runmybusiness_settings', 'RunMyBusiness API Settings', 'runmybusiness_section_text', 'runmybusiness_setting_admin');
	add_settings_field('runmybusiness_api_username', 'Username', 'runmybusiness_api_username', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_api_password', 'Password', 'runmybusiness_api_password', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_api_recurrence', 'Cron Recurrence', 'runmybusiness_api_recurrence', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_listing_api_url', 'Listing API URL', 'runmybusiness_listing_api_url', 'runmybusiness_setting_admin', 'runmybusiness_settings');
	add_settings_field('runmybusiness_person_api_url', 'People API URL', 'runmybusiness_person_api_url', 'runmybusiness_setting_admin', 'runmybusiness_settings');
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

function runmybusiness_listing_api_url() {
	$options = get_option('runmybusiness_options');
	$runmybusiness_url = isset($options['runmybusiness_listing_url']) ? $options['runmybusiness_listing_url'] : '';
?>
	<input type="text" id="runmybusiness_listing_url" name="runmybusiness_options[runmybusiness_listing_url]" value="<?php echo $runmybusiness_url; ?>" style="width: 500px;" />
<?php
}

function runmybusiness_person_api_url() {
	$options = get_option('runmybusiness_options');
	$runmybusiness_url = isset($options['runmybusiness_people_url']) ? $options['runmybusiness_people_url'] : '';
?>
	<input type="text" id="runmybusiness_people_url" name="runmybusiness_options[runmybusiness_people_url]" value="<?php echo $runmybusiness_url; ?>" style="width: 500px;" />
<?php
}
