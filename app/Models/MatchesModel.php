<?php

namespace App\Models;

use CodeIgniter\Model;
use Config\Database;

class MatchesModel extends Model
{
    protected $table = 'likes';
    protected $db_model;
    protected $allowedFields = ['title', 'slug', 'body'];
    protected $clients_response_fields = "id,name,gender_get,year_of_birth,latitude,goals_get,
    longitude,bio,work,education,updated_at,verified,uniid";
    public function __construct()
    {
        parent::__construct();
        $this->db_model = new DBModel();
        $this->db = Database::connect();
    }
    public function like_client($clientId, $likedId, $type)
    {
        $requiredCoins = 1;
        if ($type == 'super_love') {
            $requiredCoins = 4;
        }
        if ($clientId == $likedId) {
            return 'CLient can not like his own profile';
        }
        $clientObj = $this->db_model->get_obj_by_id('clients', $clientId);
        if (!in_array($type, ['like', 'super_love', 'regather'])) {
            return "Unexpected like type";
        }
        if (($type == 'like') && ($clientObj->coins < $requiredCoins)) {
            return 'You don\'t have enough coins';
        } elseif (($type == 'regather') && ($clientObj->coins < $requiredCoins)) {
            return 'You don\'t have enough coins';
        } elseif (($type == 'super_love') && ($clientObj->coins < $requiredCoins)) {
            return 'You don\'t have enough coins';
        }
        if ($this->_already_liked($clientId, $likedId, $type) && ($type != 'regather')) {
            return 'You already liked this account';
        }

        if ((!$this->does_user_like_me($likedId, $clientId)) && ($type == 'regather')) {
            return 'You already regathered on this account';
        }

        $reduce_coins = $this->_use_coins($clientObj, $requiredCoins);
        if (!$reduce_coins) // use coins then add match
            return 'Unable to use coins,try again later';
        if ($type == 'regather') {
            $where = ['liker_id' => $clientObj->id, 'liked_id' => $likedId];
            $this->db_model->delete_where('likes', $where);
        } else {
            $data = ['liker_id' => $clientObj->id, 'liked_id' => $likedId, 'like_type' => $type];
            $this->db_model->save_data('likes', $data);
        }
        return 200;
    }


  

    private function _can_client_see_likers($clientId, $likerId)
    {
        // return true if client already pay coins to see this liker (visible = 1)
        $visible = false;
        $results =  $this->db_model->get_by_field_array('likes', ['liker_id' => $likerId, 'liked_id' => $clientId]);
        if (!empty($results)) {
            $visible = $results[0]->visible == 1;
        }

        return $visible;
    }

  

    private function _getMatchesIds($clientID)
    {
        /**
         *    Get the IDs of clients who have a match with the given client ID.
         *    @param int $clientID The client ID to check for matches.
         *    @return array An array containing the IDs of clients who have a match with the given client ID.
         */
        $matchesArr = $this->db->table('matches')
            ->select('client1_id, client2_id')
            ->where('client1_id', $clientID)
            ->orWhere('client2_id', $clientID)
            ->get()
            ->getResultArray();

        $matched_ids = [];
        foreach ($matchesArr as $row) {
            if ($row['client1_id'] == $clientID) {
                $matched_ids[] = $row['client2_id'];
            } else {
                $matched_ids[] = $row['client1_id'];
            }
        }
        return $matched_ids;
    }
   

    public function does_user_like_me($client1Id, $client2Id)
    {
        /** Check if client2 likes client 1 */
        $match = false;
        $results =  $this->db_model->get_by_field_array('likes', ['liker_id' => $client2Id, 'liked_id' => $client1Id]);
        if (!empty($results)) {
            $match = true;
        }

        return $match;
    }

    private function _already_liked($clientId, $likedId, $type)
    {
        $where = ['liker_id' => $clientId, 'liked_id' => $likedId, 'like_type' => $type];
        $result =  $this->db_model->get_by_field_array('likes', $where);
        if (!empty($result)) {
            return true;
        }
        return false;
    }

    private function _use_coins($clientObj, $useCoins)
    {
        $where = ['id' => $clientObj->id];
        $newCoinsCredit = $clientObj->coins - $useCoins;
        $dataIn = ['coins' => $newCoinsCredit];

        return $this->db_model->update_data('clients', $where, $dataIn);
    }

    public function addCoins($clientObj, $coinsNumber)
    {
        $where = ['id' => $clientObj->id];
        $newCoinsCredit = $clientObj->coins + $coinsNumber;
        $dataIn = ['coins' => $newCoinsCredit];

        return $this->db_model->update_data('clients', $where, $dataIn);
    }

    public function unmatch($client1Id, $client2Id)
    {
        /**
         * Unmatche between client1 and clint 2 
         * Remove the like from client 1 to client 2
         */
      
        $query  = "SELECT *
        FROM matches
        WHERE (client1_id = $client1Id AND client2_id = $client2Id)
           OR (client1_id = $client2Id AND client2_id = $client1Id)";
        $matchesArr = $this->db->query($query)->getResult();
        if (empty($matchesArr)){
            return 'This match was already removed by previous action';
        }
        $idsArray =  array_column($matchesArr, 'id');
        $this->db->table('matches')
            ->whereIn('id', $idsArray)
            ->delete();
        // delete from likes where liker_id = $client1Id and liked_id = $client2Id
        $where = ['liker_id' => $client1Id, 'liked_id' => $client2Id];
        $this->db_model->delete_where('likes', $where);
        return 200;
    }
}
