<?php 
$dir_name = dirname(__FILE__);
require_once(dirname($dir_name) . '/app/class_sql_connector.php');
include_once(dirname($dir_name) . '/thong_ke/class_thong_ke.php');
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

//-------------------------Nếu là đọc thống kê ---------------------------------
if ($_POST["action"] === "doc") { 
    $response['log'] .= "action=doc;";
    if (!isset($_POST["ten_tai_khoan"]) || !isset($_POST["loai_tai_khoan"]) ) {
        //Nếu chưa có thông tin thì thoát
        $response['log'] .= "không biết ai xin đọc; chức vụ?";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    $ten_tai_khoan = $_POST['ten_tai_khoan'];
    $loai_tai_khoan = $_POST['loai_tai_khoan'];
    
    if ($loai_tai_khoan === 'god') {
        //Nếu god thì thoát, tạm thời chưa làm với god
        $response['log'] .= " chưa hoạt động với god";
        $response['success'] = 0;
        echo json_encode($response);
        exit();
    }

    //Cập nhật kết quả các tin trước khi đọc
    tin::CapNhatKetQuaCacTin($ten_tai_khoan, $loai_tai_khoan);

    
    $response ["result_thong_ke"] = 1;


    echo json_encode($response);

}

function CapNhatThongKeTheoDanhSachTin(array $tin_list, thong_ke $thong_ke): array
{
    // Initialize an associative array to store grouped elements
    $grouped_tin_list = array();

    foreach ($tin_list as $tin) {
        if($tin->vung_mien =='mb'){

            $thong_ke->so_tin++;
            $thong_ke->hai_c += $tin->hai_c;
            $thong_ke->ba_c += $tin->ba_c;
            $thong_ke->bon_c += $tin->bon_c;
            $thong_ke->da_daxien += $tin->da_daxien;
            $thong_ke->xac += $tin->xac;
            $thong_ke->thuc_thu += $tin->thuc_thu;
            $thong_ke->tien_trung += ($tin->tien_trung != -1) ? $tin->tien_trung : 0;

            // Group by 'vung_mien'
            $vung_mien = $tin->vung_mien;

            if (!isset($grouped_tin_list[$vung_mien])) {
                $grouped_tin_list[$vung_mien] = new thong_ke(); // Initialize a new thong_ke object for each group
            }

            // Update statistics for each group
            $grouped_tin_list[$vung_mien]->so_tin++;
            $grouped_tin_list[$vung_mien]->hai_c += $tin->hai_c;
            $grouped_tin_list[$vung_mien]->ba_c += $tin->ba_c;
            $grouped_tin_list[$vung_mien]->bon_c += $tin->bon_c;
            $grouped_tin_list[$vung_mien]->da_daxien += $tin->da_daxien;
            $grouped_tin_list[$vung_mien]->xac += $tin->xac;
            $grouped_tin_list[$vung_mien]->thuc_thu += $tin->thuc_thu;
            $grouped_tin_list[$vung_mien]->tien_trung += ($tin->tien_trung != -1) ? $tin->tien_trung : 0;

        }
        
    }

    // Calculate 'thang_thua' for each group
    foreach ($grouped_tin_list as $vung_mien => $grouped_thong_ke) {
        $grouped_tin_list[$vung_mien]->thang_thua = $grouped_thong_ke->tien_trung - $grouped_thong_ke->thuc_thu;
    }

    // Now, $grouped_tin_list is an associative array where keys are 'vung_mien' values
    // and values are thong_ke objects containing statistics for each group.
    return $grouped_tin_list;
}
?>