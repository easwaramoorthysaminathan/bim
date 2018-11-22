<?php
ob_start();
include "common_functions.php";
class goods_receipt extends common_functions{
    public function create_gr($po_id,$site_name,$date_received){
        $pdo = new PDODB();
        $db = $pdo -> getDbConnection();
        $gr = array();
        $gr['poNumber'] = $po_id;
		$gr['site_name'] = $site_name;
		$gr['date_received'] = $date_received;
        $gr['goods_receipt_number'] = $this -> generateSequence("goods_receipt","gr");
        $db->beginTransaction();
        $res = $this->send_curl_req('http://'.$this->getServerHost().'/crud/api.php/records/goods_receipt',json_encode($gr));
        $qry =  "insert into gr_items(goods_receipt_id,sku_name,ltl_code,unit_price,supplier,category,part_number,quantity,skuNumber)
        (select '".$gr['goods_receipt_number']."',sku_name,ltl_code,unit_price,supplier,category,part_number,quantity-received_qty,skuNumber 
        from po_items where po_id = ?)";
        $stmt =  $db->prepare($qry);
        $stmt->execute(array($po_id));
        $db->commit();
        }
    }
$po_id = $_POST['po'];
$site_name = $_POST['site_name'];
$date_received = $_POST['date_received'];
$gr = new goods_receipt;
// var_dump($gr->create_gr($po_id,$site_name,$date_received));
// header("Location:http://".$gr->getServerHost()."/crud/api.php/records/goods_receipt?join=gr_items&filter=poNumber,eq,$po_id");
ob_flush();