<?php
// File class kết quả của đài. Dùng để chứa dữ liệu đọc được từ web 'https://www.minhngoc.com.vn/ket-qua-xo-so/mien-nam.html'

$dir_name = dirname(__FILE__);
require_once(dirname($dir_name) . '/app/class_sql_connector.php');
include_once(dirname($dir_name) . '/tin/class_tin.php');
include_once(dirname($dir_name) . '/cau_hinh/class_cau_hinh.php');
include_once(dirname($dir_name) . '/tin/class_ds_dai_kieu.php');




class ket_qua_ngay
{
    public $ngay_xo, $ket_qua_cac_dai;
    public function __construct()
    {
        $ket_qua_cac_dai = array();
    }

    
    //=============================================Lấy kết quả miền Bắc =========================================
    public static function LayKetQuaMienBac(): ket_qua_ngay
    {
        
        $ket_qua_ngay = new ket_qua_ngay();

        $sql_connector = new sql_connector();

        # lấy danh sách số chặn theo miền
        $sql_lay_ket_qua = "SELECT * FROM `ket_qua_trung` ORDER BY `id` DESC LIMIT 1;";

        $danh_sach_chan_diem ="";

        if ($limit_number = $sql_connector->get_query_result($sql_lay_ket_qua)) {

            while ($row = $limit_number->fetch_assoc()) {
              
                $danh_sach_chan_diem = $row['ket_qua'];
            }
        }

        //Lấy tên đài
        $ket_qua_dai = new ket_qua_dai();
        $ket_qua_dai->ten_dai = "Miền Bắc";
        //echo var_dump($ket_qua_dai);
        
        for ($i = 0; $i < 8; $i++) { //Chạy từ giải đb đến giải 7
            
            $cac_so_cua_giai = json_decode($danh_sach_chan_diem);
            
            $ket_qua_dai->cac_giai[$i] = $cac_so_cua_giai[$i];

        }
        $ket_qua_ngay->ket_qua_cac_dai[] = $ket_qua_dai;

        unset($dom);
        unset($x);

        //echo var_dump($ket_qua_ngay);
        return $ket_qua_ngay;
    }


    //================================
    //Trả về một mảng là các ket_qua_ngay sắp theo cấu hình
    function LayMangSapThuTuKetQuaDai(cau_hinh $cau_hinh): array
    {
        //Lấy thứ tự trong tuần của ngày xổ, từ 0 - 6
        $date = str_replace('/', '-', $this->ngay_xo);
        $ngay_trong_tuan = date('w', strtotime($date));
        //Tạo mảng lưu kết quả, 
        $kq_dai_theo_thu_tu = array();
        //$so_dai = (int)sizeof($this->ket_qua_cac_dai); //Lấy số đài xổ ngày hôm đó

        //Lấy số kq theo số đài xổ ngày hôm đó
        $ten_dai_theo_thu_tu = $cau_hinh->getTenDaiTheoNgay($ngay_trong_tuan);
        $arr_ten_dai = explode(";", $ten_dai_theo_thu_tu);
        for ($i = 0; $i < sizeof($arr_ten_dai); $i++) {

            foreach ($this->ket_qua_cac_dai as $ketqua) {
                if ($ketqua->ten_dai === $arr_ten_dai[$i])
                    $kq_dai_theo_thu_tu[] = $ketqua;
            }

        }
        //echo json_encode( $thu_tu_dai);
        return $kq_dai_theo_thu_tu;
    }
    /**
     * Hàm lấy kết quả đài theo tên đài
     * @param string $tendai tên đài ở dạng viết tắt.
     * 
     */
    public function layKetQuaDai(string $tendai): ket_qua_dai
    {
        return $this->ket_qua_cac_dai[0];
            
        // $ngay_xo = str_replace('/', '-', $this->ngay_xo); //Chuyển dạng dd/mm/yyyy sang dd-mm-yyyy để php nhận dạng đúng
        // $day_of_week = date('w', strtotime($ngay_xo));
        // $ds_dai = new DanhSachDai();
        // $ten_dai_day_du = $ds_dai->LayTenTheoVietTat($tendai, $day_of_week);
        // foreach ($this->ket_qua_cac_dai as $ketqua) {

        //     if ($ketqua->ten_dai === $ten_dai_day_du)
        //         return $ketqua;
        // }
        // return new ket_qua_dai();
    }

    function DaXien(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so); //Tách dãy các số thành mảng

        $mang_cac_dai = explode(',', $chi_tiet->dai);

        $ket_qua1 = $this->layKetQuaDai($mang_cac_dai[0]);
        $ket_qua2 = $this->layKetQuaDai($mang_cac_dai[1]);
        foreach ($cac_so as $so) { //Với mỗi số
            $so1 = substr($so, 0, 2);
            $so2 = substr($so, -2);
            $so_lan_so1_xuat_hien = $so_lan_so2_xuat_hien = 0;
            $so_lan_so1_xuat_hien += $ket_qua1->SoLanXuatHienBaoLo($so1);
            $so_lan_so1_xuat_hien += $ket_qua2->SoLanXuatHienBaoLo($so1);
            $so_lan_so2_xuat_hien += $ket_qua1->SoLanXuatHienBaoLo($so2);
            $so_lan_so2_xuat_hien += $ket_qua2->SoLanXuatHienBaoLo($so2);
            $so_lan_xuat_hien_cua_ca_2_so = min($so_lan_so1_xuat_hien, $so_lan_so2_xuat_hien);
            if ($so_lan_xuat_hien_cua_ca_2_so > 0) { //Nếu cả hai số đều xuất hiện ít nhất 1 lần
                //Cập nhật kết quả
                $chi_tiet->tien_trung = ($chi_tiet->tien_trung == -1) ?
                    $chi_tiet->diem * $trung * $so_lan_xuat_hien_cua_ca_2_so :
                    $chi_tiet->tien_trung + ($chi_tiet->diem * $trung * $so_lan_xuat_hien_cua_ca_2_so);
                $chi_tiet->so_trung .= $so . '-';
            }
        }
        return $chi_tiet;
    }

}

//======================================Class Ket Qua Dai ==========================
class ket_qua_dai
{
    public $ten_dai, $cac_giai = array();
    public function __construct()
    {
        $ten_dai = '';
        $cac_giai = array();
    }

    function HaiConDau(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $giai_can_lay = 7; //Nam lấy 8, bắc lấy 7
        $cac_so = explode(' ', $chi_tiet->so);
        $size_of_cac_so = sizeof($cac_so);
        foreach ($cac_so as $so) {
            $giai = $this->cac_giai[$giai_can_lay];
            if (strpos($giai, $so) !== false) {
                $chi_tiet->tien_trung += ($chi_tiet->xac / $size_of_cac_so) * $trung *10;
                $chi_tiet->so_trung .= $so . "-";
            }
        }

        return $chi_tiet;
    }

    //Hàm so sánh 2 con đuôi, 2 số cuối giải ĐB
    function HaiConDuoi(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so);
        $size_of_cac_so = sizeof($cac_so);

        $hai_so_cuoi_db = substr($this->cac_giai[0], -2); //lay 2 so cuoi giai dac biet


        foreach ($cac_so as $so) {

            if ($so == $hai_so_cuoi_db) { //So mỗi số với 2 số cuối giải đặc biệt
                $chi_tiet->tien_trung += ($chi_tiet->xac / $size_of_cac_so) * $trung *10;
                $chi_tiet->so_trung .= $so . "-";
            }
        }

        return $chi_tiet;
    }

    //So sánh 2 con bao, tất cả giải
    function Bao(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so);
        $con = strlen($cac_so[0]);
        $giai_bat_dau_soi = (sizeof($this->cac_giai) + 1) - $con; //Lấy vị trí giải bắt đầu soi trở về 0
        foreach ($cac_so as $so) {
            for ($i = $giai_bat_dau_soi; $i >= 0; $i--) { //Soi từng giải
                $giai = $this->cac_giai[$i];
                $mang_cac_so_cua_giai = explode(';', $giai); //Phân tách các số của giải
                foreach ($mang_cac_so_cua_giai as $so_cua_giai) { //Với mỗi số của giải, lấy só cuối, có thể là 2, 3, 4 số cuối
                    $so_cuoi = substr($so_cua_giai, (-1 * $con));
                    if ($so == $so_cuoi) { //So sánh số cuối, nếu bằng thì cập nhật 

                        $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                        $chi_tiet->so_trung .= $con . '-';
                    }
                }
            }
        }
        return $chi_tiet;
    }
    //So sánh 2 con bao, tất cả giải
    function BayLo2con(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so);
        foreach ($cac_so as $so) {
            for ($i = 8; $i >= 5; $i--) { //Soi từ giải 8 đến giải 5
                $giai = $this->cac_giai[$i];
                $mang_cac_so_cua_giai = explode(';', $giai); //Phân tách các số của giải
                foreach ($mang_cac_so_cua_giai as $so_cua_giai) { //Với mỗi số của giải, lấy 2 số cuối
                    $so_cuoi = substr($so_cua_giai, -2); 
                    if ($so == $so_cuoi) { //So sánh hai số cuối, nếu bằng thì cập nhật 

                        $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                        $chi_tiet->so_trung .=$so . '-';
                    }
                }
            }
            //Giải đặc biệt
            $so_cuoi = substr($this->cac_giai[0], -2); //Lấy 2 số cuối db
            if ($so == $so_cuoi) { //So sánh hai số cuối, nếu bằng thì cập nhật 

                $chi_tiet->tien_trung += $chi_tiet->diem * $trung;
                $chi_tiet->so_trung .= $so . '-';
            } 
        }
        return $chi_tiet;
    }

    function BayLo3con(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so);
        foreach ($cac_so as $so) {
            for ($i = 7; $i >= 5; $i--) { //Soi từ giải 7 đến giải 5
                $giai = $this->cac_giai[$i];
                $mang_cac_so_cua_giai = explode(';', $giai); //Phân tách các số của giải
                foreach ($mang_cac_so_cua_giai as $so_cua_giai) { //Với mỗi số của giải, lấy 3 số cuối
                    $so_cuoi = substr($so_cua_giai, -3); 
                    if ($so == $so_cuoi) { //So sánh, nếu bằng thì cập nhật 

                        $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                        $chi_tiet->so_trung .= $so . '-';
                    }
                }
            }
            //Số đầu tiên giải 4
             $giai_tu  = explode(';', $this->cac_giai[4]);
             $so_dau_giai_tu = $giai_tu[0];
             $so_cuoi = substr($so_dau_giai_tu, -3); //Lấy 3 chữ số cuối
             if ($so == $so_cuoi) { //So sánh, nếu bằng thì cập nhật 
 
                 $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                 $chi_tiet->so_trung .= $so . '-';
             } 
            //Giải đặc biệt
            $so_cuoi = substr($this->cac_giai[0], -3); //Lấy 3 số cuối db
            if ($so == $so_cuoi) { //So sánh, nếu bằng thì cập nhật 

                $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                $chi_tiet->so_trung .= $so . '-';
            } 
        }
        return $chi_tiet;
    }
    //Hàm soi đánh 3 con đầu. Miền Nam giải 7, miền bắc Giải 6
    function XiuDau(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $vi_tri_giai_can_lay =  6; //Nam lấy 7, bắc lấy 6
        $giai_can_lay = $this->cac_giai[$vi_tri_giai_can_lay];
        $mang_cac_so_cua_giai = explode(';', $giai_can_lay); //Tách các số của giải thành mảng

        $cac_so = explode(' ', $chi_tiet->so); //Tách dãy các cần soi  thành mảng
        //$size_of_cac_so = sizeof($cac_so); //Số lượng số cần soi

        foreach ($cac_so as $so) {
            foreach ($mang_cac_so_cua_giai as $so_cua_giai) { //Với mỗi số, lấy 3 só cuối
                $ba_so_cuoi = substr($so_cua_giai, -3);
                if ($so === $ba_so_cuoi) { //So sánh ba số cuối, nếu bằng thì cập nhật 

                    //$xac_cua_so = $chi_tiet->xac / $size_of_cac_so;
                    $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                    $chi_tiet->so_trung .= $so . '-';
                }
            }
        }
        return $chi_tiet;
    }

    //Hàm soi đánh 3 con đuôi, 3 số cuối giải ĐB
    function XiuDuoi(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {


        $cac_so = explode(' ', $chi_tiet->so); //Tách dãy các cần soi  thành mảng
        $size_of_cac_so = sizeof($cac_so); //Số lượng số cần soi

        $giai_db = $this->cac_giai[0];
        $ba_so_cuoi = substr($giai_db, -3);

        foreach ($cac_so as $so) {
            if ($so === $ba_so_cuoi) { //So sánh ba số cuối, nếu bằng thì cập nhật 

                //$xac_cua_so = $chi_tiet->xac / $size_of_cac_so;
                $chi_tiet->tien_trung += $chi_tiet->diem * $trung *10;
                $chi_tiet->so_trung .= $so . '-';
            }

        }
        return $chi_tiet;
    }



    /**
     * Hàm soi kiểu Đá Thẳng
     */
    function Da(chi_tiet_tin $chi_tiet, float $trung): chi_tiet_tin
    {
        $cac_so = explode(' ', $chi_tiet->so); //Tách dãy các số thành mảng
        foreach ($cac_so as $so) { //Với mỗi số
            $so1 = substr($so, 0, 2);
            $so2 = substr($so, -2);
            $so_lan_so1_xuat_hien = $so_lan_so2_xuat_hien = 0;
            $so_lan_so1_xuat_hien += $this->SoLanXuatHienBaoLo($so1);
            $so_lan_so2_xuat_hien += $this->SoLanXuatHienBaoLo($so2);
            $so_lan_xuat_hien_cua_ca_2_so = min($so_lan_so1_xuat_hien, $so_lan_so2_xuat_hien);
            if ($so_lan_xuat_hien_cua_ca_2_so > 0) { //Nếu cả hai số đều xuất hiện ít nhất 1 lần
                //Cập nhật kết quả
                $chi_tiet->tien_trung += $chi_tiet->diem * $trung * $so_lan_xuat_hien_cua_ca_2_so *10;
                $chi_tiet->so_trung .= $so . '-';
            }
        }
        return $chi_tiet;
    }

    /**
     * Hàm đếm số lần xuất hiện của số trong kết quả (Soi đuôi)
     */
    function SoLanXuatHienBaoLo(string $so): int
    {
        $so_lan_xuat_hien = 0;
        $con = strlen($so);
        $giai_bat_dau_soi = (count($this->cac_giai) + 1) - $con; //Lấy vị trí giải bắt đầu soi trở về 0
        for ($i = $giai_bat_dau_soi; $i >= 0; $i--) { //Soi từng giải
            $giai = $this->cac_giai[$i];
            $mang_cac_so_cua_giai = explode(';', $giai); //Phân tách các số của giải
            foreach ($mang_cac_so_cua_giai as $so_cua_giai) { //Với mỗi số của giải, lấy só cuối, có thể là 2, 3, 4 số cuối
                $so_cuoi = substr($so_cua_giai, (-1 * $con));
                if ($so == $so_cuoi) //So sánh số cuối, nếu bằng thì cập nhật 
                    $so_lan_xuat_hien++;
            }
        }
        return $so_lan_xuat_hien;
    }

}

?>