<?php

/**
 * Get the type from a Halpdesk\Perform\Model field
 * @see Halpdesk\Perform\Model
 *
 * http://php.net/manual/en/function.gettype.php
 */
if (!function_exists('get_orm_value_type')) {
    function get_orm_value_type($value) {

        $type = gettype($value);

        // for historical reasons "double" is returned in case of a float, and not simply "float"
        if ($type == 'double') {
            $type = 'float';
        }

        // if the key is a Carbon object, the type should be 'datetime'
        if ($type == 'string' && preg_match('/([0-9]+[-T_\ :\.]{0,1})+/', $value) === 1) {
            $type = 'datetime';
        }

        return $type;
    }
}
