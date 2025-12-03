<?php
    // Kiểm tra quyền admin
    $is_admin = false;
    if (isset($_SESSION['user_id']) && !empty($_SESSION['user_id'])) {
        // Kiểm tra xem connect.php đã được include chưa
        if (!isset($conn)) {
            include 'connect.php';
        }
        
        if (isset($conn) && $conn) {
            $user_id = $_SESSION['user_id'];
            $check_admin_query = "SELECT role FROM users WHERE IdUser = ?";
            $stmt_admin = mysqli_prepare($conn, $check_admin_query);
            if ($stmt_admin) {
                mysqli_stmt_bind_param($stmt_admin, "i", $user_id);
                mysqli_stmt_execute($stmt_admin);
                $result_admin = mysqli_stmt_get_result($stmt_admin);
                if ($row_admin = mysqli_fetch_assoc($result_admin)) {
                    $is_admin = ($row_admin['role'] == 0); // role = 0 là admin
                }
                mysqli_stmt_close($stmt_admin);
            }
        }
    }
?>
 <!-- MENU-NAV -->
        <div class="main-nav">
            <nav class="navbar">
                <!-- Trang chủ -->
                <div class="menu-btn"><i class="fa-solid fa-bars" id = "btn-sidebar"></i></div>
                <ul class ="nav-list">
                    <li class="nav-list-item">
                        <a href="/Home/index.php" class="nav-link">
                            <img src="https://img.icons8.com/fluency-systems-filled/50/home.png" alt="home"/>
                            <span class = "link-text">Trang chủ</span>
                        </a>
                        <span class = "tooltip">Trang chủ</span>
                    </li>
        
                    <!-- Anime -->
                    <li class="nav-list-item">
                        <a href="/Anime/Ani_index.php" class ="nav-link">
                            <img src="https://img.icons8.com/ios-filled/50/doraemon.png" alt="doraemon"/>
                            <span>Bảng xếp hạng Anime</span>
                        </a>
                        <span class = "tooltip">Bảng xếp hạng Anime</span>
                    </li>
                    <!-- Managa -->
                    <li class="nav-list-item">
                        <a href="/Pagesproducts/Manga/manga.php" class ="nav-link">
                            <img src="https://img.icons8.com/ios-glyphs/30/book.png" alt="book"/>
                            <span>Manga</span>
                        </a>
                        <span class = "tooltip">Manga</span>
                    </li>
                    <!-- Mô hình -->
                    <li class="nav-list-item">
                        <a href="/Pagesproducts/Mohinh/mohinh.php" class ="nav-link">
                            <img src="https://img.icons8.com/ios-filled/50/mobile-suit-gundam.png" alt="mobile-suit-gundam"/>
                            <span>Mô hình</span>
                        </a>
                        <span class = "tooltip">Mô hình</span>
                    </li>
                    <!-- Trang phục Cosplay -->
                    <li class="nav-list-item">
                        <a href="/Pagesproducts/Cosplay/cosplay.php" class ="nav-link">
                            <img src="https://img.icons8.com/external-smashingstocks-glyph-smashing-stocks/66/external-Cosplay-geek-smashingstocks-glyph-smashing-stocks.png" alt="external-Cosplay-geek-smashingstocks-glyph-smashing-stocks"/>
                            <span>Trang phục Cosplay</span>
                        </a>
                        <span class = "tooltip">Trang phục Cosplay</span>
                    </li>
                    
                    <!-- Thống kê -->
                    <li class="nav-list-item">
                        <a href="/thongke/thongke.php" class ="nav-link">
                            <img src="https://img.icons8.com/ios-filled/50/graph.png" alt="statistics"/>
                            <span>Thống kê</span>
                        </a>
                        <span class = "tooltip">Thống kê</span>
                    </li>
                    
                    <!-- Quản lý (Chỉ hiển thị cho Admin) -->
                    <?php if ($is_admin): ?>
                    <li class="nav-list-item">
                        <a href="/admin/admin.php" class ="nav-link">
                            <img src="https://img.icons8.com/ios-filled/50/settings.png" alt="settings"/>
                            <span>Quản lý</span>
                        </a>
                        <span class = "tooltip">Quản lý</span>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>