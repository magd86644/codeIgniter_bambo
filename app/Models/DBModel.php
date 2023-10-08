<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;
use CodeIgniter\Events\Events;

class DBModel extends Model
{

    public function __construct()
    {
        parent::__construct();
        $this->db = Database::connect();
    }

    public function save_data($tableName, $data)
    {
        $builder = $this->db->table($tableName);
        $builder->insert($data);
        if ($this->db->affectedRows() == 1) {
            Events::trigger('afterInsert', $tableName, $data);
            return $this->db->insertID();
        }
        return false;
    }

    public function save_batch_data($tableName, $data_arr)
    {
        /* Save multiple records at once, data is array of arrays e.g:
         [['title' => 'My title','name'  => 'My Name'],['title' => 'Another title','name'  => 'Another Name'] ] */

        $builder = $this->db->table($tableName);
        $builder->insertBatch($data_arr);
        if ($this->db->affectedRows() > 1) {
            return $this->db->insertID();
        }
        return false;
    }

    public function update_data($tableName, $where_arr, $data)
    {
        $builder = $this->db->table($tableName);
        $builder->where($where_arr);
        $builder->update($data);
        if ($this->db->affectedRows() > 0) {
            return TRUE;
        }
        return false;
    }

    public function delete_where($tableName, $where_arr)
    {
        $builder = $this->db->table($tableName);
        $builder->delete($where_arr);
        if ($this->db->affectedRows() > 0) {
            return TRUE;
        }
        return false;
    }

    public function getCount($tableName)
    {
        $builder = $this->db->table($tableName);
        return $builder->countAll();
    }

    public function get_by_field_array(
        $tableName,
        $where_arr = [],
        $limit = 0,
        $offset = 0,
        $order = 'table_order asc, id asc',
        $groupby = '',
        $select = "*",
        $resultType = '' // By default the result is array of objects, unless we sent other required type
    ) {
        $builder = $this->db->table($tableName);
        $builder->select($select); //text comma seperated fields;
        $builder->where($where_arr);
        $builder->orderBy($order); //text: ex: 'title DESC, name ASC'
        $builder->groupBy($groupby); //text or array, ie.g "title", ["title", "date"]
        $builder->limit($limit, $offset);
        $result = [];
        if ($resultType != '') {
            $result = $builder->get()->getResult($resultType);
        } else {
            $result = $builder->get()->getResult();
        }
        $this->update_fkey_by_related_value($tableName, $result);
        return $result;
    }

    public function get_by_field_array_whereIn($tableName, $needle, $array, $limit = 0, $offset = 0, $order = 'id asc', $groupby = '', $select = "*")
    {
        if (!is_array($array))
            return [];

        $builder = $this->db->table($tableName);
        $builder->select($select); //text comma seperated fields;
        $builder->whereIn($needle, $array);
        $builder->orderBy($order); //text: ex: 'title DESC, name ASC'
        $builder->groupBy($groupby); //text or array, ie.g "title", ["title", "date"]
        $builder->limit($limit, $offset);
        $result = $builder->get()->getResult();
        $this->update_fkey_by_related_value($tableName, $result);
        return $result;
    }

    public function countSearchLike($tableName, $where)
    {
        // Return number of records that match a search array after searching
        $builder = $this->db->table($tableName);
        $builder->like($where);
        return $builder->countAllResults();
    }

    public function search_like($tableName, $where, $limit = 0, $offset = 0, $order = '', $groupby = '', $select = "*")
    {
        $builder = $this->db->table($tableName);
        $builder->select($select); //text comma seperated fields;
        $builder->like($where);
        $builder->orderBy($order); //text: ex: 'title DESC, name ASC'
        $builder->groupBy($groupby); //text or array, ie.g "title", ["title", "date"]
        $builder->limit($limit, $offset);
        return $builder->get()->getResult();
    }



}
