<?php

add_shortcode('rmb-rates-table', 'rmb_rates_table_handler');

/**
 * @param array $attr
 */
function rmb_rates_table_handler($attr = [])
{
    return rmb_rates_show_table($attr);
}

/**
 * @param array $attr
 */
function rmb_rates_show_table($attr = [])
{
    $rates = @json_decode(file_get_contents(plugin_dir_path(__FILE__) . '../rates/rates-cache.json'), true);

    $table = '
        <div class="rmb-rate-index">
            <div class="rmb-rate-table">
                <div class="rmb-header">
                    <div class="rmb-date">
                        ' . date('F j, Y') . '
                    </div>
                    <div class="rmb-updated">Last Updated: ' . date('F j, Y - H:m', $rates['timestamp']) . ' UTC</div>
                </div>
                <div class="rmb-rate-block">
                ';

    foreach ($rates['rates'] as $row):
        $class = ($row['pct'][0] == '-') ? 'rmb-neg' : 'rmb-pos';

    $table = $table . '
                    <div class="rmb-rate">
                        <div class="rmb-rate-name">
                            ' . $row['title'] . '
                        </div>
                        <div class="rmb-rate-value ' . $class . '">
                            ' . $row["value"] . '
                        </div>
                        <div class="rmb-rate-change ' . $class . '">
                            ' . $row["pct"] . '
                        </div>
                    </div>';

    endforeach;

    $table = $table . '</div>
            </div>
<!--        	<div class="rmb-rate-graph">-->
<!--        		<div class="rmb-graph">-->
<!--        		</div>-->
<!--        	</div>-->
        </div>';

    return $table;
}
