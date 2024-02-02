<?php
$dir_name = dirname(__FILE__);
require_once(dirname($dir_name) . '/app/class_sql_connector.php');
include_once(dirname($dir_name) . '/tin/class_tin.php');
include_once(dirname($dir_name) . '/tin/class_noi_dung_tin.php');
include_once(dirname($dir_name) . '/tin/kiem_tra_so_chan.php');
include_once(dirname($dir_name) . '/tin/kiem_tra_tang_diem.php');
include_once(dirname($dir_name) . '/tin/kiem_tra_diem.php');

$response = array();
$response['log'] = "";

date_default_timezone_set('Asia/Ho_Chi_Minh');

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //Nếu không phải POST thì thoát
    $response['log'] = "không phải post;";
    $response['success'] = 0;
    echo json_encode($response);
    exit();
}


if ($_POST["action"] === "luu_kq") {

    if (!isset($_POST["ket_qua"]) ) {
        //Nếu chưa có thông tin thì thoát
        $response['log'] .= "không có message_id";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $ma_tin = $_POST["ma_tin"];
    $ket_qua = $_POST['ket_qua'];
    $openTime = $_POST['openTime'];
    $ma_phien_toi = $_POST['ma_phien_toi'];

    $ket_qua = str_replace("'",'"', $ket_qua);
    
    $sql_connector = new sql_connector();

    if(!$sql_connector->conn){ //Nếu có lỗi kết nối csdl
        $response['log'] .= "Loi ket noi csdl; ";
        $response['error'] = $sql_connector->get_connect_error();
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $sql_select = "INSERT INTO `ket_qua_trung` (`ma_phien`,`ket_qua`,`openTime`, `ma_phien_toi`) VALUES ('$ma_tin','$ket_qua','$openTime', '$ma_phien_toi')";

   
    if ($sql_connector->get_query_result($sql_select)){

        $response['success'] = 1;

        //Cập nhật kết quả các tin trước khi đọc
        tin::CapNhatKetQuaCacTin("admin", "admin");

    }else{

        $response['success'] = 0;
    }

    echo json_encode($response);
}

if ($_POST["action"] === "lay_kq") {


    $sql_connector = new sql_connector();

    if(!$sql_connector->conn){ //Nếu có lỗi kết nối csdl
        $response['log'] .= "Loi ket noi csdl; ";
        $response['error'] = $sql_connector->get_connect_error();
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $sql_lay_ket_qua = "SELECT * FROM `ket_qua_trung` ORDER BY `id` DESC LIMIT 1;";

    $ket_qua_trung = [];

    if ($limit_number = $sql_connector->get_query_result($sql_lay_ket_qua)) {

        while ($row = $limit_number->fetch_assoc()) {
            
            $ket_qua_trung= $row;
            
        }

        
    }
    else{

        $response['success'] = 0;
    }

    $response['ket_qua_trung'] = $ket_qua_trung;

    echo json_encode($response);
}

if ($_POST["action"] === "lay_kq_of_message") {


    $sql_connector = new sql_connector();

    if(!$sql_connector->conn){ //Nếu có lỗi kết nối csdl
        $response['log'] .= "Loi ket noi csdl; ";
        $response['error'] = $sql_connector->get_connect_error();
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $message_id = $_POST["message_id"];

    $sql_lay_ket_qua = "SELECT `ket_qua` FROM `ket_qua_trung` WHERE `ma_tin`=$message_id ORDER BY `id` DESC LIMIT 1;";

    $ket_qua_trung = [];

    if ($limit_number = $sql_connector->get_query_result($sql_lay_ket_qua)) {

        while ($row = $limit_number->fetch_assoc()) {
            
            $ket_qua_trung= $row;
            
        }

        
    }
    else{

        $response['success'] = 0;
    }

    $response['ket_qua_trung'] = $ket_qua_trung;

    echo json_encode($response);
}

?>