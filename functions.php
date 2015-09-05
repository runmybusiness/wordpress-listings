<?php

ini_set('xdebug.var_display_max_depth', 50);

function array_get($array, $key, $default = null)
{
    if (is_null($key)) {
        return $array;
    }
    if (isset($array[$key])) {
        return $array[$key];
    }
    foreach (explode('.', $key) as $segment) {
        if (!is_array($array) || !array_key_exists($segment, $array)) {
            return $default;
        }
        $array = $array[$segment];
    }

    return $array;
}

function getField(array $dataArray, $field_name, $fallback = null)
{
    foreach ($dataArray['fields']['data'] as $row) {
        if ($row['field']['legacy_id'] == $field_name) {
            return $row['markup'];
        }
    }

    return $fallback;
}

function gearray $dataArray, $field_name, $fallback = null)
{
    foreach ($dataArray['fields']['data'] as $row) {
        if ($row['field']['legacy_id'] == $field_name) {
            return $row;
        }
    }

    return $fallback;
}

function array_get_from_field_array(array $dataArray, $field_name, $key, $default)
{
    $array = ge$dataArray, $field_name);
    return array_get($array, $key, $default);
}
