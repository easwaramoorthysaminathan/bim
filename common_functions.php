<?php
include "PDODB.php";
class common_functions{
    public function getServerHost(){
       return $_SERVER['HTTP_HOST']; 
    }
    public function generateSequence($entity,$prefix){
        $pdo = new PDODB();
        $db = $pdo -> getDbConnection();
        $qry =  "SELECT `AUTO_INCREMENT`
        FROM  INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = ?
        AND   TABLE_NAME   = ?";
        $stmt =  $db->prepare($qry);
        $stmt->execute(array('sprocurement',$entity));
        $res = $stmt->fetch(PDO::FETCH_ASSOC);
        return $prefix."-".$res['AUTO_INCREMENT'];
    }

    public function send_curl_req($url,$data){
		//die($url);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        $output = curl_exec($ch);
		var_dump(curl_error);
        //$info = curl_getinfo($ch);
        curl_close($ch);
        return $output;
    }

    public function get_approval_user($cost){
        if($cost >= 1000){
            $user = 'Director';
        }elseif($cost < 1000 && $cost >=500){
            $user = 'Manager';
        }else{
            $user = 'Supervisor';
        }
        return $user;
    }
}