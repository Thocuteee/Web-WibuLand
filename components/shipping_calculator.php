<?php
/**
 * File: components/shipping_calculator.php
 * Tính phí vận chuyển dựa trên khoảng cách từ cửa hàng
 * Cửa hàng: 02 Võ Oanh, Phường 25, Bình Thạnh, Hồ Chí Minh
 */

// Địa chỉ cửa hàng
define('STORE_CITY', 'Hồ Chí Minh');
define('STORE_DISTRICT', 'Bình Thạnh');
define('STORE_WARD', 'Phường 25');

/**
 * Tính phí vận chuyển dựa trên địa chỉ giao hàng
 * 
 * @param string $city Tỉnh/Thành phố
 * @param string $district Quận/Huyện
 * @param string $ward Phường/Xã (tùy chọn)
 * @return int Phí vận chuyển (VND)
 */
function calculate_shipping_fee($city, $district, $ward = '') {
    // Chuẩn hóa dữ liệu đầu vào
    $city = mb_strtolower(trim($city), 'UTF-8');
    $district = mb_strtolower(trim($district), 'UTF-8');
    $ward = mb_strtolower(trim($ward), 'UTF-8');
    
    $store_city = mb_strtolower(STORE_CITY, 'UTF-8');
    $store_district = mb_strtolower(STORE_DISTRICT, 'UTF-8');
    
    // Các biến thể tên Hồ Chí Minh
    $hcm_variants = ['hồ chí minh', 'ho chi minh', 'hcm', 'tp.hcm', 'tp hcm', 'thành phố hồ chí minh'];
    $is_hcm = in_array($city, $hcm_variants) || strpos($city, 'hồ chí minh') !== false || strpos($city, 'ho chi minh') !== false;
    
    // Các biến thể tên Bình Thạnh
    $binh_thanh_variants = ['bình thạnh', 'binh thanh'];
    $is_binh_thanh = in_array($district, $binh_thanh_variants) || strpos($district, 'bình thạnh') !== false || strpos($district, 'binh thanh') !== false;
    
    // 1. Cùng quận Bình Thạnh: 20,000₫
    if ($is_hcm && $is_binh_thanh) {
        return 20000;
    }
    
    // 2. Khác quận nhưng cùng HCM (nội thành): 30,000₫
    if ($is_hcm && !$is_binh_thanh) {
        // Các quận nội thành HCM
        $inner_districts = [
            'quận 1', 'quận 2', 'quận 3', 'quận 4', 'quận 5', 'quận 6', 'quận 7', 'quận 8', 
            'quận 9', 'quận 10', 'quận 11', 'quận 12', 'tân bình', 'tân phú', 'phú nhuận',
            'gò vấp', 'bình tân', 'thủ đức', 'quận 1', 'quận 2', 'quận 3'
        ];
        
        $is_inner = false;
        foreach ($inner_districts as $inner) {
            if (strpos($district, $inner) !== false) {
                $is_inner = true;
                break;
            }
        }
        
        if ($is_inner) {
            return 30000;
        } else {
            // Ngoại thành HCM: 40,000₫
            return 40000;
        }
    }
    
    // 3. Miền Nam (khác HCM): 50,000₫ - 70,000₫
    $south_provinces = [
        'bình dương', 'đồng nai', 'bà rịa - vũng tàu', 'bà rịa vũng tàu', 'tây ninh', 
        'bình phước', 'long an', 'tiền giang', 'bến tre', 'vĩnh long', 'đồng tháp',
        'an giang', 'kiên giang', 'cà mau', 'bạc liêu', 'sóc trăng', 'trà vinh',
        'hậu giang', 'cần thơ'
    ];
    
    $is_south = false;
    foreach ($south_provinces as $province) {
        if (strpos($city, $province) !== false) {
            $is_south = true;
            break;
        }
    }
    
    if ($is_south) {
        // Gần HCM: 50,000₫
        $near_hcm = ['bình dương', 'đồng nai', 'bà rịa', 'vũng tàu', 'tây ninh', 'bình phước', 'long an'];
        foreach ($near_hcm as $near) {
            if (strpos($city, $near) !== false) {
                return 50000;
            }
        }
        // Xa hơn: 70,000₫
        return 70000;
    }
    
    // 4. Miền Trung: 80,000₫ - 100,000₫
    $central_provinces = [
        'đà nẵng', 'quảng nam', 'quảng ngãi', 'bình định', 'phú yên', 'khánh hòa',
        'ninh thuận', 'bình thuận', 'quảng bình', 'quảng trị', 'thừa thiên huế',
        'kon tum', 'gia lai', 'đắk lắk', 'đắk nông', 'lâm đồng'
    ];
    
    $is_central = false;
    foreach ($central_provinces as $province) {
        if (strpos($city, $province) !== false) {
            $is_central = true;
            break;
        }
    }
    
    if ($is_central) {
        // Gần: 80,000₫
        $near_central = ['đà nẵng', 'quảng nam', 'khánh hòa', 'bình thuận'];
        foreach ($near_central as $near) {
            if (strpos($city, $near) !== false) {
                return 80000;
            }
        }
        // Xa: 100,000₫
        return 100000;
    }
    
    // 5. Miền Bắc: 100,000₫ - 150,000₫
    $north_provinces = [
        'hà nội', 'hải phòng', 'hải dương', 'hưng yên', 'thái bình', 'nam định',
        'ninh bình', 'hà nam', 'bắc ninh', 'bắc giang', 'quảng ninh', 'lạng sơn',
        'cao bằng', 'bắc kạn', 'thái nguyên', 'tuyên quang', 'hà giang', 'yên bái',
        'lào cai', 'điện biên', 'sơn la', 'hoà bình', 'phú thọ', 'vĩnh phúc'
    ];
    
    $is_north = false;
    foreach ($north_provinces as $province) {
        if (strpos($city, $province) !== false) {
            $is_north = true;
            break;
        }
    }
    
    if ($is_north) {
        // Hà Nội và các tỉnh gần: 100,000₫
        $near_north = ['hà nội', 'hải phòng', 'hải dương', 'bắc ninh', 'hưng yên', 'vĩnh phúc'];
        foreach ($near_north as $near) {
            if (strpos($city, $near) !== false) {
                return 100000;
            }
        }
        // Xa hơn: 120,000₫ - 150,000₫
        return 150000;
    }
    
    // 6. Mặc định (không xác định được): 80,000₫
    return 80000;
}

/**
 * Lấy mô tả phí vận chuyển
 */
function get_shipping_fee_description($city, $district) {
    $fee = calculate_shipping_fee($city, $district);
    
    $city_lower = mb_strtolower(trim($city), 'UTF-8');
    $district_lower = mb_strtolower(trim($district), 'UTF-8');
    
    $hcm_variants = ['hồ chí minh', 'ho chi minh', 'hcm', 'tp.hcm', 'tp hcm'];
    $is_hcm = in_array($city_lower, $hcm_variants) || strpos($city_lower, 'hồ chí minh') !== false;
    $binh_thanh_variants = ['bình thạnh', 'binh thanh'];
    $is_binh_thanh = in_array($district_lower, $binh_thanh_variants) || strpos($district_lower, 'bình thạnh') !== false;
    
    if ($is_hcm && $is_binh_thanh) {
        return "Giao hàng nội quận Bình Thạnh";
    } elseif ($is_hcm) {
        return "Giao hàng nội thành HCM";
    } else {
        return "Giao hàng liên tỉnh";
    }
}
?>


