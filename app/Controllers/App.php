<?php

namespace App\Controllers;

use App\Models\ClientModel;
use App\Models\DBModel;
use App\Models\MessagesModel;
use App\Models\CoinsModel;

class App extends BaseController
{
    public $client_model, $db_model, $msg_model,$coins_model;
    private $current_time;
    public function __construct()
    {
        helper(['User', 'date', 'form', 'Visitors', 'Cms', 'bc_array']);
        $this->client_model = new ClientModel();
        $this->db_model = new DBModel();
        $this->coins_model = new CoinsModel();
        $this->msg_model = model('MessagesModel');
        $this->current_time = date('Y-m-d H:i:s', now());
        require 'Sms.php';
    }

    /*  ------------------- public functions : index, login, register    */

    public function index()
    {
        echo "Forbidden";
    }

    private function _get_account_status($phone)
    {
        /** 'not_exist' , 'active' , 'inactive','unknown' */
        $client = $this->client_model->asObject()->where('phone', $phone)->first();
        if (!$client)
            return 'not_exist';
        if (!isset($client->status))
            return 'unknown';
        return $client->status; //active or inactive
    }

    public function register()
    {
        if (($this->request->getMethod() === 'post') && ($this->validate('client_register_rules'))) {
            $phone =  $this->request->getVar('phone', FILTER_SANITIZE_STRING);
            $status = $this->_get_account_status($phone);
            if ($status != 'not_exist') {
                $client = $this->client_model->asObject()->where('phone', $phone)->first();
                if ($client) {
                    $msg = "Welcome back to Bambo \n Your activation code: " .  $client->verify_token;
                    $recipients[] =  $client->phone;
                    $is_sent =  Sms::send_msg($msg, $recipients);
                    $result = [
                        'response' => 'success', 'msg' => 'Login code sent', 'verify_token' => $client->verify_token,
                        'msg_sent' => $is_sent, 'status_code' => 200
                    ];
                } else {
                    $result = ['response' => 'failed', 'msg' => 'Unable to handle this request', 'status_code' => 400];
                }

                echo json_encode($result);
                return;
            }
            $data['phone'] = $phone;
            $data['created_at'] = $this->current_time;
            $data['uniid'] = generate_uniq_id_h(); //to be used in activation
            $data['activation_date'] = $this->current_time;
            $data['token_expired'] = generate_date_after_number_of_days_h(2);
            $data['verify_token'] = $this->_generate_code();
            $res = $this->client_model->create_account($data);
            if ($res) {
                $msg = "Welcome to Bambo \n Your activation code: " .  $data['verify_token'];
                $recipients[] =  $data['phone'];
                $is_sent =  Sms::send_msg($msg, $recipients);
                $result = [
                    'response' => 'success', 'msg' => 'Account created successfully', 'verify_token' => $data['verify_token'],
                    'msg_sent' => $is_sent, 'status_code' => 200
                ];
            } else {
                $result = ['response' => 'failed', 'msg' => 'Unable to handle this request', 'status_code' => 400];
            }
        } else {
            $msg = 'Invalid data, please try again';
            if (!$this->validate('client_register_rules')) {
                if (get_field_validation_error_h('phone') != false)
                    $msg = trim(strip_tags(get_field_validation_error_h('phone')));
            }
            $result = ['response' => 'failed', 'msg' =>  $msg,   'status_code' => 400];
        }
        echo json_encode($result);
    }

    private function _add_datain_arr_if_valid($dataIn, $inputValue, $key_name)
    {
        /** Add inputValue to dataIn array in the given KeyName if it is set and valid */
        if (isset($inputValue)) {
            if ($key_name == 'height') {
                if (is_numeric($inputValue)) { // height should be a number
                    $dataIn[$key_name] = $inputValue;
                }
            } else {
                $dataIn[$key_name] = $inputValue;
            }
        }
        return $dataIn;
    }





    public function get_referral_number()
    {
        $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
        $client = $this->client_model->asObject()->where(['uniid' => $uniID])->first();
        // var_dump($client->id);die;
        $referal_number = $this->client_model->get_create_referal_number($client);
        $result = ['response' => 'success', 'msg' =>  $referal_number,   'status_code' => 200];
        echo json_encode($result);
    }

    public function update_location()
    {
        if (($this->request->getMethod() === 'post') && ($this->validate('update_location_rules'))) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $long =  $this->request->getVar('long', FILTER_SANITIZE_STRING);
            $lat =  $this->request->getVar('lat', FILTER_SANITIZE_STRING);
            $data['longitude'] = $long;
            $data['latitude'] = $lat;
            $response = $this->client_model->update_client_account($uniID, $data);
            if ($response == 200) {
                $client = $this->client_model->asObject()->where(['uniid' => $uniID])->first();
                $result = ['response' => 'success', 'msg' => 'Data saved successfully', 'token' => $client->uniid, 'status_code' => 200];
            } else {
                $result = ['response' => 'failed', 'msg' =>  $response,  'status_code' => 400];
            }
        } else {
            $msg = 'Missing required data, please try again';
            $validator = service('validation');
            $details = [];
            foreach (['token', 'long', 'lat'] as $field) {
                if ($validator->hasError($field)) {
                    $details[] =  $validator->getError($field);
                }
            }
            $result = ['response' => 'failed', 'msg' =>  $msg, 'error_details' => $details, 'status_code' => 400];
        }
        echo json_encode($result);
    }





  
 



    public function set_interest()
    {
        if (($this->request->getMethod() === 'post')) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $interestID =  $this->request->getVar('interest_id', FILTER_SANITIZE_STRING);
            $action = $this->request->getVar('action', FILTER_SANITIZE_STRING);
            if ($action == 'remove') {
                $response = $this->client_model->delete_client_interest($uniID, $interestID);
            } else {
                $response = $this->client_model->add_client_interest($uniID, $interestID);
            }
            if ($response == 200) {
                $client = $this->client_model->asObject()->where(['uniid' => $uniID])->first();
                $result = ['response' => 'success', 'msg' => 'Data saved successfully', 'token' => $client->uniid, 'status_code' => 200];
            } else {
                $result = ['response' => 'failed', 'msg' =>  $response,   'status_code' => 400];
            }
        } else {
            $msg = 'Missing required data, please try again';
            $validator = service('validation');
            $details = [];
            foreach (['token', 'interest_id'] as $field) {
                if ($validator->hasError($field)) {
                    $details[] =  $validator->getError($field);
                }
            }
            $result = ['response' => 'failed', 'msg' =>  $msg,   'error_details' => $details, 'status_code' => 400];
        }
        echo json_encode($result);
    }

    public function set_language()
    {
        if (($this->request->getMethod() === 'post')) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $language =  $this->request->getVar('language', FILTER_SANITIZE_STRING);
            $action = $this->request->getVar('action', FILTER_SANITIZE_STRING);
            if ($action == 'remove') {
                $response = $this->client_model->delete_client_language($uniID, $language);
            } else {
                $response = $this->client_model->add_client_language($uniID, $language);
            }

            if ($response == 200) {
                $result = ['response' => 'success', 'msg' => 'Data saved successfully', 'status_code' => 200];
            } else {
                $result = ['response' => 'failed', 'msg' =>  $response,   'status_code' => 400];
            }
        } else {
            $msg = 'Missing required data, please try again';
            $result = ['response' => 'failed', 'msg' =>  $msg,  'status_code' => 400];
        }
        echo json_encode($result);
    }

    public function set_prompt()
    {
        if (($this->request->getMethod() === 'post')) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $promptQuestionId =  $this->request->getVar('prompt_question_id', FILTER_SANITIZE_STRING);
            $answer =  $this->request->getVar('answer', FILTER_SANITIZE_STRING);
            $action = $this->request->getVar('action', FILTER_SANITIZE_STRING);
            if ($action == 'remove') {
                $response = $this->client_model->delete_client_prompt_answer($uniID, $promptQuestionId);
            } else {
                $response = $this->client_model->add_client_prompt_answer($uniID, $promptQuestionId, $answer);
            }

            if ($response == 200) {
                $result = ['response' => 'success', 'msg' => 'Data saved successfully', 'status_code' => 200];
            } else {
                $result = ['response' => 'failed', 'msg' =>  $response,   'status_code' => 400];
            }
        } else {
            $msg = 'Missing required data, please try again';
            $result = ['response' => 'failed', 'msg' =>  $msg,  'status_code' => 400];
        }
        echo json_encode($result);
    }

    public function set_star_sign()
    {
        if (($this->request->getMethod() === 'post')) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $starSign =  $this->request->getVar('star_sign', FILTER_SANITIZE_STRING);
            $data = [];
            $data = $this->_add_datain_arr_if_valid($data, $starSign, 'star_sign');
            $response = $this->client_model->update_client_account($uniID, $data);
            if ($response == 200) {
                $result = ['response' => 'success', 'msg' => 'Data saved successfully', 'status_code' => 200];
            } else {
                $result = ['response' => 'failed', 'msg' =>  $response,   'status_code' => 400];
            }
        } else {
            $msg = 'Missing required data, please try again';
            $result = ['response' => 'failed', 'msg' =>  $msg, 'status_code' => 400];
        }
        echo json_encode($result);
    }



    // Messages
    public function messages($action)
    {
        switch ($action) {
            case 'send':
                $this->_message_send();
                break;
            case 'open':
                $this->_message_open();
                break;
            default:
                $result = ['response' => 'failed', 'msg' =>  'Invalid request/data', 'status_code' => 400];
                echo json_encode($result);
                break;
        }
    }
 

    // submit steps
    public function submit_client_steps(){
        if (($this->request->getMethod() === 'post')) {
            $uniID = $this->request->getVar('token', FILTER_SANITIZE_STRING);
            $num_steps = $this->request->getVar('num_steps', FILTER_SANITIZE_STRING);
            $response = $this->coins_model->add_coins_by_steps($uniID,$num_steps);
            if ($response != 200) {
                $result = ['response' => 'failed', 'msg' =>  $response, 'status_code' => 400];
            } else {
                $client = $this->client_model->is_client_valid($uniID);
                $result = ['response' => 'success', 'msg' => 'Steps submitted successfully','coins'=>$client->coins, 'status_code' => 200];
            }
        } else {
            $msg = 'Invalid or missing data, please try again';
            $result = ['response' => 'failed', 'msg' =>  $msg, 'status_code' => 400];
        }
        echo json_encode($result);
    }
}
