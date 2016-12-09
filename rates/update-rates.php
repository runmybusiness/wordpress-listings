<?php
include('simple_dom.php');

$dom = file_get_html($runmybusiness_rates_url);
$rates = [];

foreach ($dom->find('.tile') as $tile) {
    $title = (string) $tile->find('.symbolName')[0]->innertext;
    $value = (string) $tile->find('.symbolValue')[0]->innertext;
    $change = (string) $tile->find('.symbolChg')[0]->innertext;
    $pct = (string) $tile->find('.symbolChgPCT')[0]->innertext;
    $link = $tile->find('.preview')[0]->href;

    $rates[] = compact('title', 'value', 'change', 'pct', 'link');
}

file_put_contents(plugin_dir_path(__FILE__) . 'rates-cache.json', json_encode([
    'rates'     => $rates,
    'timestamp' => time(),
], JSON_PRETTY_PRINT));
