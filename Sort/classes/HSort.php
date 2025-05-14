<?php namespace EC\Sort;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;

class HSort {

    static public function UA_Stable($arr, $fn)
    {
        $t_arr = [];
        $i = 0;
        foreach ($arr as $key => $item) {
            $t_arr[] = [ $i, $key, $item ];
            $i++;
        }

        uasort($t_arr, function($a, $b) use ($fn) {
            $result = $fn($a[2], $b[2]);

            if ($result !== 0)
                return $result;

            return $a[0] > $b[0];
        });

        $r_arr = array();
        foreach ($t_arr as $t_item) {
            $r_arr[$t_item[1]] = $t_item[2];
        }
    }

}
