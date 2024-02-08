<?php
$dir_name = dirname(__FILE__);
require_once(dirname($dir_name) . '/app/class_sql_connector.php');
include_once(dirname($dir_name) . '/tin/class_tin.php');

$response = array();
$response['log'] = "";
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //Nếu không phải POST thì thoát
    $response['log'] = "không phải post;";
    $response['success'] = 0;
    echo json_encode($response);
    exit();
}

//-------------------------Nếu là lấy cấu hình ---------------------------------
if ($_POST["action"] === "doc") {

    $response['log'] .= "action=doc;";
    if (!isset($_POST["ten_tai_khoan"]) ) {
        //Nếu chưa có thông tin thì thoát
        $response['log'] .= "không biết ai xin đọc";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $ten_tai_khoan = $_POST['ten_tai_khoan'];


    $sql_connector = new sql_connector();


    $config_price = array(); 

    $sql ="CALL getConfigPriceRef('$ten_tai_khoan')";

    // Execute the update query
    if ($result = $sql_connector->get_query_result($sql)) {
        while ($row = $result -> fetch_assoc()) {
            $config_price[] = $row;
        }
        $response["success"] = 1; //Thành công
        $response["lich_su"] = $config_price;
    }      
    else{
        $response["success"] = 0;
    }
}



//-------------------------Nếu là ghi  ---------------------------------
if ($_POST["action"] === "ghi") {
    $response['log'] .= "action=ghi;";
  
}


//-------------------------Nếu là xoa  ---------------------------------
if ($_POST["action"] === "xoa") {
    $response['log'] .= "action=xoa;";
  
}
//-------------------------Nếu là Cập Nhật chi tiết kiểu đánh  ---------------------------------
if ($_POST["action"] === "cap_nhat_chi_tiet") {
    $response['log'] .= "action = cap nhat chi tiet;";
    if (!isset($_POST["id"]) || !isset($_POST["co"]) || !isset($_POST["trung"])) {
        //Nếu chưa có thông tin thì thoát
        $response['log'] .= "không rõ chi tiết gửi xuống";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $id = $_POST['id'];
    $co = $_POST['co'];
    $trung = $_POST['trung'];

    $chi_tiet_cau_hinh = new chi_tiet_cau_hinh();
    $chi_tiet_cau_hinh->id = $id;
    $chi_tiet_cau_hinh->co = $co;
    $chi_tiet_cau_hinh->trung = $trung;

    if($chi_tiet_cau_hinh->cap_nhat_xuong_db())
        $response['success'] = 1;
    else
        $response['success'] = 0;
    //Xuất ra
    $response['chi_tiet_cau_hinh'] = json_encode($chi_tiet_cau_hinh);
}


if ($_POST["action"] === "cap_nhat_config_price") {

    $response['log'] .= "action = cap nhat chi tiet;";
    if (!isset($_POST["ten_tai_khoan"]) || !isset($_POST["list_config"]) || !isset($_POST["id_update"])) {
        //Nếu chưa có thông tin thì thoát
        $response['log'] .= "không rõ chi tiết gửi xuống";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $sql_connector = new sql_connector();

    $ten_tai_khoan = $_POST['ten_tai_khoan'];

    $updateData =  $_POST['list_config'];

    $id_update = $_POST['id_update'];
    
    $updateData = json_decode($updateData);
    

    # kiểm tra xem có tài khoản ref không nếu có thì update nếu không thì thêm mới

    $sql_check_exists = "SELECT COUNT(*) AS countRef
    FROM chi_tiet_cau_hinh_ref AS ref_count WHERE tai_khoan_tao = '$ten_tai_khoan'";


    $isExists = 0;

    if ($result = $sql_connector->get_query_result($sql_check_exists)) {
        while ($row = $result -> fetch_assoc()) {
            $isExists = $row['countRef'];
                
        }
        
    }   
    
    if($isExists >= 1){
        
        // Construct the SQL update query
        $sql = "UPDATE chi_tiet_cau_hinh_ref SET co = CASE id ";

        // Add CASE statements for each item in the list
        foreach ($updateData as $item) {
            $id = $item->id;
            $co = $item->co;
            $sql .= "WHEN $id THEN '$co' ";
        }

        // Close the CASE statement and specify the WHERE condition
        $sql .= "END,  trung = CASE id ";

        foreach ($updateData as $item) {
            $id = $item->id;
            $trung = $item->trung;
            $sql .= "WHEN $id THEN '$trung' ";
        }

        $sql .= "END  WHERE id IN ($id_update)";

        var_dump($sql);

        // Execute the update query
        if ($sql_connector->get_query_result($sql)) {
            $response['success'] = 1;
        } else {
            $response['success'] = 0;
        }


    }
    else{


        try {


            $sql_chi_tiet = "INSERT INTO chi_tiet_cau_hinh_ref (kieu_danh, co, trung, vung_mien, tai_khoan_tao) VALUES ";
            foreach ($updateData  as $ct) {
                $sql_chi_tiet .= "('" . $ct->kieu_danh . "'," . $ct->co . "," . $ct->trung . ",'" . $ct->vung_mien . "','". $ten_tai_khoan."'),";
            }

            

            $sql_chi_tiet = rtrim($sql_chi_tiet, ',');

            if ($sql_connector->get_query_result($sql_chi_tiet)) {
                $response['success'] = 1;
            } else {
                $response['success'] = 0;
            }
            
        } catch (Exception $e) {
            // Code to handle the exception
            echo "An exception occurred: " . $e->getMessage();
        } 

        


    }

}

echo json_encode($response);
?>