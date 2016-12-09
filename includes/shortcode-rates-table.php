<?php

add_shortcode('rmb-rates-table', 'rmb_signup_form_handler');

function rmb_signup_form_handler($attr = [])
{
    rmb_signup_show_form($attr);
}

function rmb_signup_show_form($attr = [])
{
    $rates = @json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../rates/rates-cache.json'), true);

    $form = <<<HTMLPLUGIN
<div class="rmb-rate-index">
	<div class="rmb-rate-table">
		<div class="rmb-header">
			<div class="rmb-date">
			    <?php echo date('F j, Y'); ?>
			</div>
			<div class="rmb-updated">Last Updated: August 10, 2016 - 10:30 EDT</div>
		</div>
		<div class="rmb-rate-block">
		    <?php foreach($rates as $row): ?>
		    <?php $class = ($row['pct'][0] == '-') ? 'rmb-neg' : 'rmb-pos'; ?>
			<div class="rmb-rate">
				<div class="rmb-rate-name">
				    <?php echo $row['title']; ?>
				</div>
				<div class="rmb-rate-value <?php echo $class ?>">
				    <?php echo $row['value']; ?>
				</div>
				<div class="rmb-rate-change <?php echo $class ?>">
				    <?php echo $row['pct']; ?>
				</div>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
//	<div class="rmb-rate-graph">
//		<div class="rmb-graph">
//			<!--graph goes here-->
//		</div>
//	</div>
</div>
HTMLPLUGIN;

    echo $form;
}
