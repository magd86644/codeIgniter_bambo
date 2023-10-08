<?php

namespace App\Controllers;

use App\Models\MatchesModel;
use App\Models\MessagesModel;
# Swipe right (like) is not using coins
# Swipe right, left: 1 coint, see who likes you: 4 for each coins
# Regather: 4 coins
# Super love: 4 coins
# daily coin: 20 coin

class AppMatches extends App
{
    protected $matches_model;
    private $notPostError = [
        'response' => 'failed', 'msg' =>  'Invalid or missing data, please try again',
        'token' => '', 'status_code' => 400
    ];
    public function __construct()
    {
        parent::__construct();
        $this->matches_model = new MatchesModel();
    }


   

    public function unblure_liker(){
        /* When a user want to unblure his likers he will use this api to pay coins in order to see the liker image without blur */

        $result = $this->notPostError; // same validation rules as user_likers
        if (($this->request->getMethod() === 'post') && ($this->validate('token_req_rule'))) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $likerID = $this->request->getVar('liker_id', FILTER_SANITIZE_STRING);
            $client = $this->client_model->is_client_valid($uniID);
            if (!$client) {
                $result['msg'] = 'Not valid or inactive client';
            }elseif(!$likerID){
                $result['msg'] = 'Invalid liker id';
            } else {
                $response = $this->matches_model->unblure_liker_m($client->id,$likerID);
                if ($response != 200) {
                    $result['msg'] = $response;
                } else {
                    $result = ['response' => 'success', 'msg' =>  'Coins has been deducted successfully', 'status_code' => 200];
                }
            }
        }
        echo json_encode($result);

    }

    public function get_user_matches()
    {
        $result = $this->notPostError; // same validation rules as user_likers 
        if (($this->request->getMethod() === 'post') && ($this->validate('token_req_rule'))) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $client = $this->client_model->is_client_valid($uniID);
            if (!$client) {
                $result['msg'] = 'Not valid or inactive client';
            } else {
                $usersRecords = $this->matches_model->get_user_matches($client->id);
                if (empty($usersRecords)) {
                    $result = ['response' => 'success', 'msg' => 'No data', 'token' => '', 'status_code' => 200];
                } else {
                    $this->add_api_required_details($client, $usersRecords);
                    foreach ($usersRecords as  $obj) {
                        $obj->last_message = $this->msg_model->get_last_message($client->id, $obj->id);
                    }
                    $result = ['response' => 'success', 'msg' =>  $usersRecords, 'token' => '', 'status_code' => 200];
                }
            }
        }
        echo json_encode($result);
    }

 
 
}
