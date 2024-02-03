
<?php
	/*
        Cập nhật tên hiển thị của tài khoản
     */
	$dir_name = dirname(__FILE__);
	require_once(dirname($dir_name) . '/app/class_sql_connector.php');
	/** Array for JSON response*/
	$response = array();

	$lich_su = array();

	if($_SERVER['REQUEST_METHOD']=='POST'){
		$ten_tai_khoan = $_POST['ten_tai_khoan'];
		
		$sql = "SELECT `id`,`type_price`,`money`,`tai_khoan_tao`, `date_create` FROM `payment_history`  WHERE tai_khoan_tao = '$ten_tai_khoan' LIMIT 0,1000";

        //Kiểm tra tài khoản đã tồn tại 
		
		$sql_connector = new sql_connector();

		if ($result = $sql_connector->get_query_result($sql)) {
			while ($row = $result -> fetch_assoc()) {
				$lich_su[] = $row;
					
			}
			$response["success"] = 1; //Thành công
			$response["lich_su"] = $lich_su;
		}      
		else{
			$response["success"] = 0;
        }
    
		echo json_encode($response);
	} 
?>