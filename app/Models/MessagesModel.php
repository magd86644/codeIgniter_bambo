<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;
use App\Models\ClientModel;

class MessagesModel extends Model
{
    protected $table = 'messages';
    protected $db_model, $client_model;
    protected $allowedFields = ['title', 'slug', 'body'];
    public function __construct()
    {
        $this->db_model = new DBModel();
        $this->client_model = new ClientModel();
        $this->db = Database::connect();
    }

   



    private function mark_messages_as_seen_by_recipient($recipient_id,$sender_id){
        // when client open a conversation, the messages sent to him should be marked as seen
        $where = ['sender_id'=>$sender_id,'recipient_id'=>$recipient_id];
        $this->where($where)->set(['is_read' => 1])
                ->protect(false)->update();
    }

    private function is_seen($messageID, $userID)
    {
        //if the user is the sender, the message is seen, if the user is recipients: return message status
        $msg = $this->asObject()->find($messageID);
        if ($userID == $msg->recipient_id) {
            return $msg->is_read;
        } else {
            return 1;
        }
    }
}
