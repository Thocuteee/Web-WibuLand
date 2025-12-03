<?php
header('Content-Type: application/json; charset=utf-8');
include 'connect.php';

if (!isset($_GET['q']) || empty(trim($_GET['q']))) {
    echo json_encode(['status' => 'error', 'results' => []]);
    exit();
}

$search_term = mysqli_real_escape_string($conn, trim($_GET['q']));
$search_pattern = "%{$search_term}%";

$results = [];
$categories = ['mohinh', 'magma', 'cosplay'];
$category_names = [
    'mohinh' => 'Mô hình',
    'magma' => 'Manga', 
    'cosplay' => 'Cosplay'
];

foreach ($categories as $category) {
    $query = "SELECT ID, Name, Img1, Gia, Sale, SoLuongTonKho 
              FROM `$category` 
              WHERE Name LIKE ? 
              ORDER BY Name ASC 
              LIMIT 5";
    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $search_pattern);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        
        while ($row = mysqli_fetch_assoc($result)) {
            $price = $row['Gia'];
            $final_price = $price;
            
            if ($row['Sale'] > 0) {
                $final_price = $price * (1 - $row['Sale'] / 100);
            }
            
            $results[] = [
                'id' => $row['ID'],
                'name' => $row['Name'],
                'image' => $row['Img1'],
                'price' => number_format($final_price),
                'original_price' => ($row['Sale'] > 0) ? number_format($price) : null,
                'sale' => $row['Sale'],
                'stock' => $row['SoLuongTonKho'],
                'category' => $category,
                'category_name' => $category_names[$category],
                'url' => "/Pagesproducts/product_detail.php?id={$row['ID']}&category={$category}"
            ];
        }
        mysqli_stmt_close($stmt);
    }
}

if (count($results) > 8) {
    $results = array_slice($results, 0, 8);
}

echo json_encode([
    'status' => 'success',
    'results' => $results,
    'total' => count($results)
], JSON_UNESCAPED_UNICODE);
?>

