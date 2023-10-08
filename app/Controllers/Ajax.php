<?php

namespace App\Controllers;

class Ajax extends Manage
{
    public function load_add_record_view($table_name)
    {
        if (!has_access_h()) {
            echo 403;
        }
        $data['columns'] = $this->db_model->get_fields_in_table($table_name);
        $data['table_name'] = $table_name;
        $view = view('admin/form_elements/ajax_inline_added_field', $data);
        // we merged all the views in one return
        echo  $view;
    }

 
}
