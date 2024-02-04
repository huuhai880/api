
<?php
$dir_name = dirname(__FILE__);
require_once(dirname($dir_name) . '/app/class_sql_connector.php');
include_once(dirname($dir_name) . '/tin/class_tin.php');
include_once(dirname($dir_name) . '/cau_hinh/class_cau_hinh.php');


/** Array for JSON response*/
$response = array();
/**
 * Trang Xoá tài khoản, khi một tài khoản bị xoá thì tất cả những gì liên quan đều bị xoá theo  
 * */
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    //Nếu không phải POST thì thoát
    $response['log'] = "không phải post;";
    $response['success'] = 0;
    echo json_encode($response);
    exit();
}
//Lấy các giá trị từ post
$ten_tai_khoan = $_POST['ten_tai_khoan'];
$loai_tai_khoan = $_POST["loai_tai_khoan"];
$trang_thai = $_POST["trang_thai"];
//Tạo câu lệnh truy vấn
$sql = "UPDATE tai_khoan SET trang_thai = '$trang_thai' WHERE ten_tai_khoan = '$ten_tai_khoan' OR tai_khoan_quan_ly = '$ten_tai_khoan'";
$sql_connector = new sql_connector();
if ($result = $sql_connector->get_query_result($sql)) 
{ //Xoá tài khoản
    
    $response["success"] = 1; //Thành công
}
else
    $response["success"] = 0;
/**Return json*/
//echo timetos;
$response['sql'] = $sql;
echo json_encode($response);
?>