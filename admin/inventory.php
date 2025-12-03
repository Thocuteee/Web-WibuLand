<?php
session_start();
include("../components/connect.php");

if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] != 1) {
    header('Location: ../login&registration/login.php');
    exit();
}

if (!isset($_GET["category"])) {
    $category = "mohinh";
} else { 
    $category = $_GET["category"];
}

$stats = [];
$categories = ['mohinh', 'magma', 'cosplay'];
$total_stock = 0;
$total_sold = 0;
$total_value = 0;
$low_stock_count = 0;

foreach ($categories as $cat) {
    $query = "SELECT 
        COUNT(*) as total_products,
        SUM(SoLuongTonKho) as total_stock,
        SUM(SoLuongDaBan) as total_sold,
        SUM(SoLuongTonKho * Gia) as total_value
    FROM `$cat`";
    
    $result = $conn->query($query);
    if ($result && $row = $result->fetch_assoc()) {
        $stats[$cat] = $row;
        $total_stock += (int)$row['total_stock'];
        $total_sold += (int)$row['total_sold'];
        $total_value += (int)$row['total_value'];
    }
    
    $low_stock_query = "SELECT COUNT(*) as count FROM `$cat` WHERE SoLuongTonKho < 10 AND SoLuongTonKho > 0";
    $low_result = $conn->query($low_stock_query);
    if ($low_result && $low_row = $low_result->fetch_assoc()) {
        $low_stock_count += (int)$low_row['count'];
    }
}

$products_query = "SELECT * FROM `$category` ORDER BY SoLuongTonKho ASC";
$products = $conn->query($products_query);

$low_stock_products = [];
foreach ($categories as $cat) {
    $query = "SELECT '$cat' as category, ID, Name, SoLuongTonKho, SoLuongDaBan, Gia 
              FROM `$cat` 
              WHERE SoLuongTonKho < 10 AND SoLuongTonKho > 0
              ORDER BY SoLuongTonKho ASC 
              LIMIT 10";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $low_stock_products[] = $row;
        }
    }
}

$out_of_stock = [];
foreach ($categories as $cat) {
    $query = "SELECT '$cat' as category, ID, Name, SoLuongDaBan, Gia 
              FROM `$cat` 
              WHERE SoLuongTonKho = 0
              ORDER BY SoLuongDaBan DESC 
              LIMIT 10";
    $result = $conn->query($query);
    if ($result) {
        while ($row = $result->fetch_assoc()) {
            $out_of_stock[] = $row;
        }
    }
}

$category_names = [
    'mohinh' => 'Mô hình',
    'magma' => 'Manga',
    'cosplay' => 'Cosplay'
];
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý Tồn kho - Wibu Dreamland</title>
    <link rel="stylesheet" href="css/admin.css">
    <script src="https://kit.fontawesome.com/eff669a9ab.js" crossorigin="anonymous"></script>
    <style>
        .stats-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .stat-card.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        }
        
        .stat-card.success {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        }
        
        .stat-card.info {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }
        
        .stat-card h3 {
            margin: 0 0 10px 0;
            font-size: 14px;
            opacity: 0.9;
            text-transform: uppercase;
        }
        
        .stat-card .value {
            font-size: 32px;
            font-weight: bold;
            margin: 10px 0;
        }
        
        .stat-card .subtitle {
            font-size: 12px;
            opacity: 0.8;
        }
        
        .inventory-section {
            background: white;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        .inventory-section h2 {
            margin-top: 0;
            color: #333;
            border-bottom: 3px solid #667eea;
            padding-bottom: 10px;
        }
        
        .stock-badge {
            padding: 5px 10px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .stock-badge.low {
            background: #fff3cd;
            color: #856404;
        }
        
        .stock-badge.out {
            background: #f8d7da;
            color: #721c24;
        }
        
        .stock-badge.good {
            background: #d4edda;
            color: #155724;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
        }
        
        table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
        }
        
        table td {
            padding: 12px;
            border-bottom: 1px solid #dee2e6;
        }
        
        table tr:hover {
            background: #f8f9fa;
        }
        
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 10px;
        }
        
        .category-tabs {
            display: flex;
            gap: 10px;
        }
        
        .category-tabs button {
            padding: 10px 20px;
            border: 2px solid #667eea;
            background: white;
            color: #667eea;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        
        .category-tabs button:hover,
        .category-tabs button.active {
            background: #667eea;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="action-bar">
            <h1><i class="fa-solid fa-boxes-stacked"></i> Quản lý Tồn kho</h1>
            <div style="display: flex; gap: 10px;">
                <a href="admin.php" class="btn-func" style="background-color: #6c757d;">
                    <i class="fa-solid fa-arrow-left"></i> Quay lại
                </a>
                <a href="../Home/index.php" class="btn-func" style="background-color: #4caf50;">
                    <i class="fa-solid fa-home"></i> Trang chủ
                </a>
                <a href="orders.php" class="btn-func" style="background-color: #f9a01b;">
                    <i class="fa-solid fa-box"></i> Đơn hàng
                </a>
            </div>
        </div>

        <!-- Thống kê tổng quan -->
        <div class="stats-container">
            <div class="stat-card">
                <h3><i class="fa-solid fa-box"></i> Tổng Tồn kho</h3>
                <div class="value"><?php echo number_format($total_stock); ?></div>
                <div class="subtitle">sản phẩm trong kho</div>
            </div>
            
            <div class="stat-card success">
                <h3><i class="fa-solid fa-chart-line"></i> Đã Bán</h3>
                <div class="value"><?php echo number_format($total_sold); ?></div>
                <div class="subtitle">sản phẩm đã bán ra</div>
            </div>
            
            <div class="stat-card info">
                <h3><i class="fa-solid fa-dollar-sign"></i> Giá trị Kho</h3>
                <div class="value"><?php echo number_format($total_value); ?>₫</div>
                <div class="subtitle">tổng giá trị tồn kho</div>
            </div>
            
            <div class="stat-card warning">
                <h3><i class="fa-solid fa-triangle-exclamation"></i> Cảnh báo</h3>
                <div class="value"><?php echo $low_stock_count; ?></div>
                <div class="subtitle">sản phẩm sắp hết hàng</div>
            </div>
        </div>

        <!-- Sản phẩm sắp hết hàng -->
        <?php if (count($low_stock_products) > 0): ?>
        <div class="inventory-section">
            <h2><i class="fa-solid fa-exclamation-triangle"></i> Sản phẩm Sắp hết hàng (< 10)</h2>
            <table>
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th>Tên sản phẩm</th>
                        <th>Tồn kho</th>
                        <th>Đã bán</th>
                        <th>Giá trị</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($low_stock_products as $product): ?>
                    <tr>
                        <td><?php echo $category_names[$product['category']]; ?></td>
                        <td><?php echo $product['Name']; ?></td>
                        <td><strong style="color: #e74c3c;"><?php echo $product['SoLuongTonKho']; ?></strong></td>
                        <td><?php echo $product['SoLuongDaBan']; ?></td>
                        <td><?php echo number_format($product['Gia']); ?>₫</td>
                        <td><span class="stock-badge low">Sắp hết</span></td>
                        <td>
                            <a href="admin.php?category=<?php echo $product['category']; ?>&id=<?php echo $product['ID']; ?>" class="btn-func">Nhập thêm</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Sản phẩm hết hàng -->
        <?php if (count($out_of_stock) > 0): ?>
        <div class="inventory-section">
            <h2><i class="fa-solid fa-box-open"></i> Sản phẩm Hết hàng</h2>
            <table>
                <thead>
                    <tr>
                        <th>Danh mục</th>
                        <th>Tên sản phẩm</th>
                        <th>Đã bán</th>
                        <th>Giá</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($out_of_stock as $product): ?>
                    <tr>
                        <td><?php echo $category_names[$product['category']]; ?></td>
                        <td><?php echo $product['Name']; ?></td>
                        <td><?php echo $product['SoLuongDaBan']; ?></td>
                        <td><?php echo number_format($product['Gia']); ?>₫</td>
                        <td><span class="stock-badge out">Hết hàng</span></td>
                        <td>
                            <a href="admin.php?category=<?php echo $product['category']; ?>&id=<?php echo $product['ID']; ?>" class="btn-func" style="background: #e74c3c;">Nhập hàng</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <!-- Chi tiết theo danh mục -->
        <div class="inventory-section">
            <h2><i class="fa-solid fa-list"></i> Chi tiết Tồn kho theo Danh mục</h2>
            
            <div class="category-tabs">
                <button onclick="window.location.href='inventory.php?category=mohinh'" 
                        class="<?php echo $category == 'mohinh' ? 'active' : ''; ?>">
                    Mô hình
                </button>
                <button onclick="window.location.href='inventory.php?category=magma'" 
                        class="<?php echo $category == 'magma' ? 'active' : ''; ?>">
                    Manga
                </button>
                <button onclick="window.location.href='inventory.php?category=cosplay'" 
                        class="<?php echo $category == 'cosplay' ? 'active' : ''; ?>">
                    Cosplay
                </button>
            </div>
            
            <br>
            
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tên sản phẩm</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Đã bán</th>
                        <th>Giá trị kho</th>
                        <th>Trạng thái</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php 
                    if ($products->num_rows > 0) {
                        while ($row = $products->fetch_assoc()) {
                            $stock = (int)$row['SoLuongTonKho'];
                            $value = $stock * $row['Gia'];
                            
                            if ($stock == 0) {
                                $badge = '<span class="stock-badge out">Hết hàng</span>';
                            } elseif ($stock < 10) {
                                $badge = '<span class="stock-badge low">Sắp hết</span>';
                            } else {
                                $badge = '<span class="stock-badge good">Còn hàng</span>';
                            }
                            
                            echo "<tr>";
                            echo "<td>" . $row['ID'] . "</td>";
                            echo "<td>" . $row['Name'] . "</td>";
                            echo "<td>" . number_format($row['Gia']) . "₫</td>";
                            echo "<td><strong>" . $stock . "</strong></td>";
                            echo "<td>" . $row['SoLuongDaBan'] . "</td>";
                            echo "<td>" . number_format($value) . "₫</td>";
                            echo "<td>" . $badge . "</td>";
                            echo "<td>
                                    <a href='admin.php?category=$category&id=" . $row['ID'] . "' class='btn-func'>Chỉnh sửa</a>
                                  </td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align: center;'>Không có sản phẩm nào</td></tr>";
                    }
                    ?>
                </tbody>
            </table>
        </div>

        <!-- Thống kê theo danh mục -->
        <div class="stats-container">
            <?php foreach ($categories as $cat): ?>
            <div class="inventory-section">
                <h3><i class="fa-solid fa-chart-pie"></i> <?php echo $category_names[$cat]; ?></h3>
                <p><strong>Tổng sản phẩm:</strong> <?php echo $stats[$cat]['total_products'] ?? 0; ?></p>
                <p><strong>Tồn kho:</strong> <?php echo number_format($stats[$cat]['total_stock'] ?? 0); ?></p>
                <p><strong>Đã bán:</strong> <?php echo number_format($stats[$cat]['total_sold'] ?? 0); ?></p>
                <p><strong>Giá trị:</strong> <?php echo number_format($stats[$cat]['total_value'] ?? 0); ?>₫</p>
            </div>
            <?php endforeach; ?>
        </div>

    </div>

    <script src="js/admin.js"></script>
</body>
</html>

