<?php
ob_start();
include "common_functions.php";
class purchase_order extends common_functions{
    public function create_po($boq_id){
        $pdo = new PDODB();
        $db = $pdo -> getDbConnection();
        $qry =  "select * from boq where boq_number = ?";
        $stmt =  $db->prepare($qry);
        $stmt->execute(array($boq_id));
        $boq = $stmt->fetch(PDO::FETCH_ASSOC);
        $project_id = $boq['project_id'];

        $qry =  "select sku_name,ltl_code,unit_price,supplier,category,part_number,quantity,skuNumber,total_price from boq_items where boq_id = ? and match_status = ?";
        $stmt =  $db->prepare($qry);
        $stmt->execute(array($boq_id,'MATCHED'));
        $boq_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $vendor_array = [];
        $total_prices = [];
        foreach($boq_items as $boq_item){
            extract($boq_item);
            if(!array_key_exists($supplier,$vendor_array)){
                $vendor_array[$supplier]=[];
                $total_prices[$supplier] = 0;
            }
            $vendor_array[$supplier][$skuNumber] = $boq_item;
            $total_prices[$supplier] = $total_prices[$supplier]+$total_price;
        }
        foreach($vendor_array as $supplier => $items){
            $po = array();
            $po['poNumber'] = $this -> generateSequence("purchase_order","po");
            $po['boq_id'] = $boq_id;
            $po['project_id'] = $project_id;
            $po['supplier'] = $supplier;
            $po['po_date'] = gmdate('Y-m-d H:i:s');
            $po['status'] = 'pending_acceptance';
            $po['total_price'] = $total_prices[$supplier];
            $this->send_curl_req('http://'.$this->getServerHost().'/crud/api.php/records/purchase_order',json_encode($po));
            
            $po_items = array();
            foreach($items as $item){
                $item['po_id']=$po['poNumber'];
                $item['received_qty']=0;
                array_push($po_items,$item);
            }
            
            $this->send_curl_req('http://'.$this->getServerHost().'/crud/api.php/records/po_items',json_encode($po_items));
           

        }
    }
    
}
$boq_id = $_POST['boq'];
$po = new purchase_order();
$po->create_po($boq_id);
ob_flush();