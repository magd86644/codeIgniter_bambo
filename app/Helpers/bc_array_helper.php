<?php
if (!function_exists('get_arr_with_mapped_fields_h')) {
    /* Return a given array of objects after replacing each object param (field) with it is required value
    Mapped names are send in the second parameter as key=>value (old_name => new name) */
    function get_arr_with_mapped_fields_h($records, $mapped_names_arr)
    {
        if (!(is_array($records) && is_array($mapped_names_arr))) {
            return false;
        }
        $newArr = [];
        foreach ($records as $obj) {
            $newObj = new stdClass();
            foreach (get_object_vars($obj) as $key => $val) {
                if (array_key_exists($key, $mapped_names_arr)) {
                    $newKey = $mapped_names_arr[$key];
                    $newObj->$newKey = $val;
                } else {
                    $newObj->$key = $val; // key not in mapping array, keep old name
                }
            }
            $newArr[] = $newObj;
        }
        return $newArr;
    }
}
