<?php

namespace dev\suvera\exms\utils;

class UiUtils {

    public static function selectOptions(array $objects, mixed $value = null): string {
        $html = '';
        foreach ($objects as $obj) {
            $selected = '';
            if (is_array($value)) {
                if (in_array($obj->id, $value)) {
                    $selected = 'selected';
                }
            } else if ($value !== null && $value == $obj->id) {
                $selected = 'selected';
            }
            $html .= '<option value="' . $obj->id . '" ' . $selected . '>' . $obj->name . '</option>' . PHP_EOL;
        }
        return $html;
    }

    public static function selectOptions2(array $keyValPair, mixed $value = null): string {
        $html = '';
        foreach ($keyValPair as $key => $val) {
            $selected = '';
            if (is_array($value)) {
                if (in_array($key, $value)) {
                    $selected = 'selected';
                }
            } else if ($value !== null && $value == $key) {
                $selected = 'selected';
            }
            $html .= '<option value="' . $key . '" ' . $selected . '>' . $val . '</option>' . PHP_EOL;
        }
        return $html;
    }
}
