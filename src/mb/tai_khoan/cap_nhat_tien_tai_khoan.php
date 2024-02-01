
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
		
		$total_money = $_POST['total_money'];

		$type_price = $_POST['type_price'];

		$sql = "UPDATE tai_khoan 
		
		SET total_money = total_money - $total_money 
		WHERE ten_tai_khoan = '$ten_tai_khoan'";

		if($type_price =='up'){
			$sql = "UPDATE tai_khoan 
		
			SET total_money = total_money + $total_money 
			WHERE ten_tai_khoan = '$ten_tai_khoan'";
		}
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