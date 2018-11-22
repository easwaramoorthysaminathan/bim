<?php
ob_start();
include('common_functions.php');
error_reporting(E_ALL);
ini_set("memory_limit","2048M");

require_once __DIR__ . '/src/Bootstrap.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

// require __DIR__ . '/Header.php';
$fun = new common_functions();
if($_POST){
    $target_dir = "uploads/";
    $inputFileName = $target_dir . basename($_FILES["fileToUpload"]["name"]);
    if (move_uploaded_file($_FILES["fileToUpload"]["tmp_name"], $inputFileName)) {
        echo "The file ". basename( $_FILES["fileToUpload"]["name"]). " has been uploaded.";
    } else {
        $inputFileName = __DIR__ . '/BOQ.xlsx';
    }    

    $boq = array();
    $boq['boq_number'] = $fun -> generateSequence("boq","Boq");
    $boq['entity_type_id'] = 'BOQ';
    $boq['project_id'] = $_POST['project_id'];
    $boq['customer_id'] = $_POST['customer_id'];
    $boq['status'] = 'pending_approval';
	$pdo = new PDODB();
    $db = $pdo -> getDbConnection();
    $db->beginTransaction();
    $fun->send_curl_req('http://'.$fun->getServerHost().'/crud/api.php/records/boq',json_encode($boq));
    $db->commit();


    $spreadsheet = IOFactory::load($inputFileName);
    $spreadsheet->setActiveSheetIndexByName('Groups Pivot Table(TH)');

    $worksheet = $spreadsheet->getActiveSheet();
    $highestRow = $worksheet->getHighestDataRow(); // e.g. 10
    $highestColumn = $worksheet->getHighestDataColumn(); // e.g 'F'
    $highestColumnIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestColumn); // e.g. 5
    $rowArray = [];
    $header = array(
        1=>'item_name',2=>'category',$highestColumnIndex => 'quantity'
    );



    for ($row = 2; $row <= $highestRow; ++$row) {
        $colArray = [];
        $colArray['row_num'] = $row;
        $colArray['match_status'] = 'New';
        $colArray['boq_id'] = $boq['boq_number'];
        for ($col = 1; $col <= $highestColumnIndex; ++$col) {
            $value = $worksheet->getCellByColumnAndRow($col, $row)->getValue();
            if(array_key_exists($col,$header)){
                $key = $header[$col];
                $colArray[$key] = $value;
            }
            if ($col == $highestColumnIndex && !empty($value)){
                array_push($rowArray,$colArray);
            }
        }
        
    }
    $db->beginTransaction();
    $fun->send_curl_req('http://'.$fun->getServerHost().'/crud/api.php/records/boq_items',json_encode($rowArray));
    $db->commit();
    header("Location:http://".$fun->getServerHost()."/crud/api.php/records/boq/".$boq['boq_number'].'?join=boq_items');

}
?>
<!DOCTYPE html>
<html>
<body>

<form action="boqreader.php" method="post" enctype="multipart/form-data">
    Upload BOQ
    <br />
    Customer : <input type="text" name="customer_id" id="customer_id">
    <br />
    Project : <input type="text" name="project_id" id="project_id">
    <br />
    File : <input type="file" name="fileToUpload" id="fileToUpload">
    <br />
    <input type="submit" value="Upload" name="submit">
</form>

</body>
</html>

<?php ob_flush(); ?>