<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/thongtinkhachhang.css" />
  </head>
  <body>
    <div class="container">
      <!-- Sidebar -->
      <div class="sidebar">
        <div class="profile">
          <div class="profile-pic">DT</div>
          <p>thuanthong675@gmail.com</p>
        </div>
        <nav>
          <div class="nav-item active">
            <img src="img/user.png" alt="User Icon" class="icon" />
            <a href="#">Thông tin cá nhân</a>
          </div>
          <div class="nav-item">
            <img src="img/box.png" alt="Box Icon" class="icon" />
            <a href="#">Đơn hàng thông thường</a>
          </div>
          <div class="nav-item">
            <img src="img/box.png" alt="Pre-order Icon" class="icon" />
            <a href="#">Đơn hàng đặt trước/mua hộ</a>
          </div>
          <div class="nav-item">
            <img src="img/home.png" alt="Home Icon" class="icon" />
            <a href="#">Địa chỉ giao hàng</a>
          </div>
        </nav>
      </div>

      <!-- Main Content -->
      <div class="main-content">
        <h2>Thông tin tài khoản</h2>
        <form>
          <table>
            <tr>
              <td><label>Họ và tên</label></td>
              <td><input type="text" placeholder="Nhập họ và tên" /></td>
            </tr>

            <tr>
              <td><label>Giới tính </label></td>
              <td>
                <div class="gender">
                  <label
                    ><input type="radio" name="gender" value="male" />
                    Nam</label
                  >
                  <label
                    ><input type="radio" name="gender" value="female" />
                    Nữ</label
                  >
                </div>
              </td>
            </tr>

            <tr>
              <td><label>Địa chỉ</label></td>
              <td>
                <input type="text" name="địa chỉ" placeholder="Nhập địa chỉ" />
              </td>
            </tr>

            <tr>
              <td><label>Quốc gia</label></td>
              <td>
                <select>
                  <option>Việt Nam</option>
                </select>
              </td>
            </tr>

            <tr>
              <td><label>Tỉnh thành</label></td>
              <td>
                <select>
                  <option>TP Hồ Chí Minh</option>
                </select>
              </td>
            </tr>

            <tr>
              <td><label>Email</label></td>
              <td><input type="email" placeholder="*****@gmail.com" /></td>
            </tr>

            <tr>
              <td><label>Số điện thoại</label></td>
              <td><input type="tel" placeholder="Nhập số điện thoại" /></td>
            </tr>

            <tr>
              <td><label>Ngày sinh</label></td>
              <td><input type="date" /></td>
            </tr>
          </table>

          <button type="submit">CẬP NHẬT</button>
        </form>
      </div>
    </div>
  </body>
</html>