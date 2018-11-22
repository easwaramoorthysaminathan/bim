<?php
class PDODB {

    public function getDbConnection() {
        try {
            
                $driver = 'mysql';
                $host = '127.0.0.1';
                $port = '3336';
                $database = 'sprocurement';
                $username = 'sprocurement';
                $password = 'sprocurement';
            $options = array(\PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8");
            switch (\strtolower($driver)) {
                case 'mysql':
                    $dsn = 'mysql:host=' . $host . ';port=' . $port . ';dbname=' . $database;
                    break;
            }
            $dbConnection = new \PDO($dsn, $username, $password, $options);
        } catch (\Exception $ex) {
            throw $ex;
        }

        return $dbConnection;
    }

}
$pdo = new PDODB();
        $db = $pdo -> getDbConnection();
		$boq_id = 'Boq-19';
        $qry =  "select * from boq where boq_number = ?";
        $stmt =  $db->prepare($qry);
        $stmt->execute(array($boq_id));
        $boq = $stmt->fetch(PDO::FETCH_ASSOC);
        var_dump($boq);
// $json = '{"id":1,"poNumber":"po-1","project_id":"pr-1","boq_id":"Boq-1","supplier":"supplier-1","po_date":"2018-10-24 09:27:15","torice":200,"status":"pending_acceptance","delivery_status":null,"created_by":1,"updated_by":1,"created_at":"2018-10-24 09:27:15","updated_at":"2018-10-24 09:27:15"}';
// print "<pre>";
// print_r(json_decode($json,true));