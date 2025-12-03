<?php
require_once '../components/connect.php';

// Tính toán thống kê doanh thu
$categories = ['mohinh' => 'Mô hình', 'magma' => 'Manga', 'cosplay' => 'Cosplay'];
$stats = [
    'total_revenue' => 0,
    'total_sold' => 0,
    'total_products' => 0,
    'categories' => []
];

$best_sellers = [];

foreach ($categories as $table => $cat_name) {
    $query = "SELECT 
        COUNT(*) as total_products,
        SUM(SoLuongDaBan) as total_sold,
        SUM(SoLuongDaBan * Gia) as total_revenue,
        SUM(SoLuongTonKho) as total_stock
    FROM `$table`";
    
    $result = mysqli_query($conn, $query);
    if ($result && $row = mysqli_fetch_assoc($result)) {
        $revenue = (float)$row['total_revenue'];
        $sold = (int)$row['total_sold'];
        $products = (int)$row['total_products'];
        $stock = (int)$row['total_stock'];
        
        $stats['categories'][$table] = [
            'name' => $cat_name,
            'revenue' => $revenue,
            'sold' => $sold,
            'products' => $products,
            'stock' => $stock
        ];
        
        $stats['total_revenue'] += $revenue;
        $stats['total_sold'] += $sold;
        $stats['total_products'] += $products;
    }
    
    $best_query = "SELECT *, '$table' as category, '$cat_name' as category_name 
                   FROM `$table` 
                   WHERE SoLuongDaBan > 0
                   ORDER BY SoLuongDaBan DESC 
                   LIMIT 5";
    
    $best_result = mysqli_query($conn, $best_query);
    if ($best_result) {
        while ($product = mysqli_fetch_assoc($best_result)) {
            $product['revenue'] = $product['SoLuongDaBan'] * $product['Gia'];
            $best_sellers[] = $product;
        }
    }
}

usort($best_sellers, function($a, $b) {
    return $b['revenue'] - $a['revenue'];
});
$best_sellers = array_slice($best_sellers, 0, 10);

if ($stats['total_revenue'] > 0) {
    foreach ($stats['categories'] as $key => &$cat) {
        $cat['revenue_percent'] = ($cat['revenue'] / $stats['total_revenue']) * 100;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../components/css/global.css">
    <link rel="stylesheet" href="../components/css/header_sidebar_footer.css">
    <link rel="stylesheet" href="../Home/css/home.css">
    <link rel="stylesheet" href="css/thongke.css">
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
    <title>📊 Thống kê Doanh thu - Wibu Dreamland</title>
</head>
<body>
    <?php include '../components/header.php'; ?>
    
    <main>
        <?php include '../components/sidebar.php'; ?>
        
        <div class="home-content">
            <div class="stats-page-header">
                <h1><i class="fa-solid fa-chart-line"></i> Thống Kê Doanh Thu</h1>
                <p style="font-size: 1.6rem; margin-top: 1rem;">
                    Tổng quan về doanh thu và sản phẩm bán chạy
                </p>
            </div>

            <div class="content">
                <!-- Tổng quan -->
                <div class="stats-grid">
                    <div class="stat-card red">
                        <div class="stat-icon"><i class="fa-solid fa-sack-dollar"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['total_revenue'] / 1000000, 1); ?>tr</div>
                        <div class="stat-label">Tổng Doanh Thu</div>
                    </div>

                    <div class="stat-card green">
                        <div class="stat-icon"><i class="fa-solid fa-box-open"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['total_sold']); ?></div>
                        <div class="stat-label">Tổng Đã Bán</div>
                    </div>

                    <div class="stat-card gold">
                        <div class="stat-icon"><i class="fa-solid fa-boxes-stacked"></i></div>
                        <div class="stat-value"><?php echo number_format($stats['total_products']); ?></div>
                        <div class="stat-label">Tổng Sản Phẩm</div>
                    </div>

                    <div class="stat-card blue">
                        <div class="stat-icon"><i class="fa-solid fa-fire"></i></div>
                        <div class="stat-value"><?php echo count($best_sellers); ?></div>
                        <div class="stat-label">Top Bán Chạy</div>
                    </div>
                </div>

                <!-- Doanh thu theo danh mục -->
                <div class="category-revenue">
                    <h2><i class="fa-solid fa-chart-pie"></i> Doanh Thu Theo Danh Mục</h2>
                    
                    <div class="category-cards">
                        <?php 
                        $cat_colors = [
                            'mohinh' => ['icon' => '🎎', 'gradient' => 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'],
                            'magma' => ['icon' => '📚', 'gradient' => 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)'],
                            'cosplay' => ['icon' => '👘', 'gradient' => 'linear-gradient(135deg, #4facfe 0%, #00f2fe 100%)']
                        ];
                        
                        foreach ($stats['categories'] as $key => $cat): 
                        ?>
                        <div class="category-card <?php echo $key; ?>">
                            <div class="category-header">
                                <div class="category-icon"><?php echo $cat_colors[$key]['icon']; ?></div>
                                <div class="category-name"><?php echo $cat['name']; ?></div>
                            </div>

                            <div class="stat-row">
                                <span class="label"><i class="fa-solid fa-dollar-sign"></i> Doanh thu:</span>
                                <span class="value"><?php echo number_format($cat['revenue']); ?>₫</span>
                            </div>

                            <div class="stat-row">
                                <span class="label"><i class="fa-solid fa-chart-simple"></i> % Doanh thu:</span>
                                <span class="value"><?php echo number_format($cat['revenue_percent'] ?? 0, 1); ?>%</span>
                            </div>

                            <div class="stat-row">
                                <span class="label"><i class="fa-solid fa-box"></i> Đã bán:</span>
                                <span class="value"><?php echo number_format($cat['sold']); ?> sản phẩm</span>
                            </div>

                            <div class="stat-row">
                                <span class="label"><i class="fa-solid fa-warehouse"></i> Tồn kho:</span>
                                <span class="value"><?php echo number_format($cat['stock']); ?></span>
                            </div>

                            <div class="stat-row">
                                <span class="label"><i class="fa-solid fa-tag"></i> Tổng SP:</span>
                                <span class="value"><?php echo $cat['products']; ?></span>
                            </div>

                            <div class="revenue-highlight" style="background: <?php echo $cat_colors[$key]['gradient']; ?>">
                                <div style="font-size: 1.3rem; opacity: 0.9;">Doanh thu trung bình/SP</div>
                                <div class="amount">
                                    <?php 
                                    $avg = $cat['sold'] > 0 ? $cat['revenue'] / $cat['sold'] : 0;
                                    echo number_format($avg); 
                                    ?>₫
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Top 10 sản phẩm bán chạy -->
                <div class="best-sellers">
                    <h2><i class="fa-solid fa-trophy"></i> Top 10 Sản Phẩm Bán Chạy Nhất</h2>
                    
                    <?php if (count($best_sellers) > 0): ?>
                        <div class="best-sellers-list">
                            <?php foreach ($best_sellers as $index => $product): 
                                $rank = $index + 1;
                                $rank_class = '';
                                if ($rank == 1) $rank_class = 'top-1';
                                elseif ($rank == 2) $rank_class = 'top-2';
                                elseif ($rank == 3) $rank_class = 'top-3';
                            ?>
                            <div class="seller-item">
                                <div class="seller-rank <?php echo $rank_class; ?>">
                                    <?php if ($rank == 1): ?>
                                        🥇
                                    <?php elseif ($rank == 2): ?>
                                        🥈
                                    <?php elseif ($rank == 3): ?>
                                        🥉
                                    <?php else: ?>
                                        #<?php echo $rank; ?>
                                    <?php endif; ?>
                                </div>

                                <img src="/admin/<?php echo $product['Img1']; ?>" 
                                     alt="<?php echo htmlspecialchars($product['Name']); ?>" 
                                     class="seller-image">

                                <div class="seller-info">
                                    <div class="seller-name"><?php echo htmlspecialchars($product['Name']); ?></div>
                                    <div class="seller-category"><?php echo $product['category_name']; ?></div>
                                    <div class="seller-stats">
                                        <div class="stat">
                                            <i class="fa-solid fa-box"></i> 
                                            Đã bán: <strong><?php echo number_format($product['SoLuongDaBan']); ?></strong>
                                        </div>
                                        <div class="stat">
                                            <i class="fa-solid fa-tag"></i> 
                                            Giá: <strong><?php echo number_format($product['Gia']); ?>₫</strong>
                                        </div>
                                        <?php if ($product['Sale'] > 0): ?>
                                        <div class="stat">
                                            <i class="fa-solid fa-fire"></i> 
                                            Sale: <strong style="color: #d42426;">-<?php echo $product['Sale']; ?>%</strong>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="seller-revenue">
                                    <div class="amount"><?php echo number_format($product['revenue']); ?>₫</div>
                                    <div class="label">Doanh thu</div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="no-data">
                            <i class="fa-solid fa-chart-line" style="font-size: 5rem; color: #ddd; margin-bottom: 2rem;"></i>
                            <p>Chưa có dữ liệu bán hàng.</p>
                        </div>
                    <?php endif; ?>
                </div>

            </div>
        </div>
    </main>

    <?php include '../components/footer.php'; ?>
    
    <script src="../components/js/global.js"></script>
</body>
</html>

