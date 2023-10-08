<?php

namespace App\Models;

use CodeIgniter\Model;
/*
 *  Used Models: (db_model)
 */

class EventsModel extends Model
{
    protected $db_model;
    public function __construct()
    {
        $this->db_model = new DBModel();
    }

    public function after_insert($tableName, $data)
    {   
        if ($tableName == 'likes') {
            $this->_check_for_matches($data['liker_id'], $data['liked_id']);
        }
    }

    /** Called when on recordInsert event on likes table, check if both matches, add them to 'both match' table so 
     * they can see each others profile
     */
    private function _check_for_matches($user1Id, $user2Id)
    {
       
        $match1 =   $this->db_model->get_by_field_array('likes', ['liker_id' => $user1Id, 'liked_id' => $user2Id]);
       
        $match2 =   $this->db_model->get_by_field_array('likes', ['liker_id' => $user2Id, 'liked_id' => $user1Id]);
        $string = sizeof($match1) . ', '. sizeof($match2);
        log_message('error', "Event: check for matches $user1Id , $user2Id , results: $string");
        if ((!empty($match1)) && (!empty($match2))) {
            $data = ['client1_id' => $user1Id, 'client2_id' => $user2Id];
            $already_added =   $this->db_model->get_by_field_array('matches', $data);
            if(empty($already_added)){ // avoid adding same match twice
                $this->db_model->save_data('matches', $data);
            }
          
        }
    }
}
