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

function getFieldArray(array $dataArray, $field_name, $fallback = null)
{
    foreach ($dataArray['fields']['data'] as $row) {
        if ($row['field']['legacy_id'] == $field_name) {
            return $row;
        }
    }

    return $fallback;
}
