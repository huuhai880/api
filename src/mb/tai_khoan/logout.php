<?php
	/*
	Trang đăng nhập
	input: POST: ten_tai_khoan, mat_khau
	output: JSON: 	succes: 1 nếu thành công, 0 nếu không thành công
					loai_tai_khoan: quyền của người dùng
					trang_thai: trạng thái của tài khoản
	 */
	$dir_name = dirname(__FILE__);
    require_once(dirname($dir_name) . '/app/class_sql_connector.php');

	/** Array for JSON response*/
	$response = array();
	if($_SERVER['REQUEST_METHOD']=='POST'){
		$username = $_POST['ten_tai_khoan'];
		
		
		$sql="UPDATE `tai_khoan` SET `is_login` = 0 WHERE `ten_tai_khoan` = '$username'";

		$sql_connector = new sql_connector();
		$result = $sql_connector->get_query_result($sql);
		if(mysqli_num_rows($result) > 0){
				
				$response["success"] = 1;

		}
		else{
			$response["success"] = 0;
		}


		echo json_encode($response);
		
	} 
	//echo "some thing";
?>