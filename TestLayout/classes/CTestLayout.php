<?php namespace EC\TestLayout;
defined('_ESPADA') or die(NO_ACCESS);

use E, EC;


class CTestLayout {

    static public function _(E\ILayout $layout, $holder_name, $layout_path,
            $fields_path = null)
    {
        $fields = null;

        if ($fields_path === null)
            $fields = new E\Fields();
        else {
            $fields_file_path =
                    E\Package::Path_FromPath($fields_path, 'fields', '.json');
            if ($fields_file_path === null) {
                \E\Notice::Add("Fields path `{$fields_path}` does not exist.");
                $fields = new E\Fields();
            } else {
                $fields_array = json_decode(file_get_contents($fields_file_path),
                        true);

                if ($fields_array === null) {
                    \E\Notice::Add("Cannot parse `{$fields_file_path}`: " .
                            json_last_error_msg());
                    $fields = new E\Fields();
                } else {
                    $fields = new E\Fields($fields_array);
                }
            }
        }

        return $layout->addL($holder_name, E\Layout::_($layout_path, $fields));
    }

}
