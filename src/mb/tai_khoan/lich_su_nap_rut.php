
<?php
	/*
        Cập nhật tên hiển thị của tài khoản
     */
	$dir_name = dirname(__FILE__);
	require_once(dirname($dir_name) . '/app/class_sql_connector.php');
	/** Array for JSON response*/
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$ten_tai_khoan = $_POST['ten_tai_khoan'];
		
		$sql = "SELECT `id`,`type_price`,`money`,`tai_khoan_tao` FROM `payment_history`  WHERE ten_tai_khoan = '$ten_tai_khoan' LIMIT 0,1000";

        //Kiểm tra tài khoản đã tồn tại 
		
		$sql_connector = new sql_connector();

		if ($result = $sql_connector->get_query_result($sql)){ 
            $response["success"] = 1; 
        }        
		else{
			$response["success"] = 0;
        }
    
		echo json_encode($response);
	} 
?>