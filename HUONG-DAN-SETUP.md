# 🚀 Hướng dẫn Setup Linh2Store

## 📋 Yêu cầu hệ thống
- XAMPP (Apache + MySQL + PHP 7.4+)
- Trình duyệt web hiện đại

## ⚡ Setup nhanh (5 phút)

### Bước 1: Khởi động XAMPP
1. Mở XAMPP Control Panel
2. Start **Apache** và **MySQL**
3. Đảm bảo cả 2 service đang chạy (màu xanh)

### Bước 2: Tạo Database
1. Mở trình duyệt, vào: `http://localhost/phpmyadmin`
2. Tạo database mới tên: `linh2store`
3. Chọn collation: `utf8mb4_unicode_ci`

### Bước 3: Chạy Setup
1. Vào: `http://localhost/linh2store/setup.php`
2. Đợi script chạy xong
3. Kiểm tra thông báo thành công

### Bước 4: Đăng nhập
**Tài khoản Admin:**
- Username: `admin`
- Password: `password`
- Link: `http://localhost/linh2store/admin/`

**Tài khoản User:**
- Username: `testuser` 
- Password: `password`
- Link: `http://localhost/linh2store/`

## 🌐 Đường dẫn chính

### 🏠 Website
- **Trang chủ**: `http://localhost/linh2store/`
- **Sản phẩm**: `http://localhost/linh2store/san-pham/`
- **Thương hiệu**: `http://localhost/linh2store/thuong-hieu/`
- **Blog**: `http://localhost/linh2store/blog/`
- **Liên hệ**: `http://localhost/linh2store/lien-he/`

### 🔐 Đăng nhập/Đăng ký
- **Đăng nhập**: `http://localhost/linh2store/auth/dang-nhap.php`
- **Đăng ký**: `http://localhost/linh2store/auth/dang-ky.php`

### ⚙️ Admin Dashboard
- **Dashboard**: `http://localhost/linh2store/admin/`
- **Đơn hàng**: `http://localhost/linh2store/admin/orders.php`
- **Sản phẩm**: `http://localhost/linh2store/admin/products.php`
- **Khách hàng**: `http://localhost/linh2store/admin/customers.php`
- **Kho hàng**: `http://localhost/linh2store/admin/inventory.php`
- **Báo cáo**: `http://localhost/linh2store/admin/reports.php`

## 🎯 Tính năng đã hoàn thành

### ✅ Frontend
- [x] Trang chủ với sản phẩm nổi bật
- [x] Trang sản phẩm với bộ lọc
- [x] Trang chi tiết sản phẩm
- [x] Giỏ hàng với AJAX
- [x] Đăng ký/Đăng nhập
- [x] User dashboard
- [x] Responsive design

### ✅ Admin Dashboard
- [x] Dashboard tổng quan
- [x] Quản lý đơn hàng
- [x] Quản lý sản phẩm
- [x] Quản lý khách hàng
- [x] Quản lý kho hàng
- [x] Báo cáo & thống kê
- [x] Biểu đồ tương tác

### ✅ Database
- [x] 100 sản phẩm mẫu
- [x] 10 thương hiệu
- [x] Hình ảnh từ Unsplash
- [x] Tài khoản admin/user
- [x] Logs hệ thống

## 🔧 Troubleshooting

### Lỗi "Database connection failed"
1. Kiểm tra XAMPP MySQL đang chạy
2. Kiểm tra database `linh2store` đã tạo
3. Kiểm tra file `config/database.php`

### Lỗi "Permission denied"
1. Kiểm tra quyền thư mục
2. Đảm bảo Apache có quyền đọc file

### Lỗi "Page not found"
1. Kiểm tra URL đúng
2. Đảm bảo file tồn tại
3. Kiểm tra .htaccess (nếu có)

## 📞 Hỗ trợ
Nếu gặp vấn đề, hãy kiểm tra:
1. XAMPP services đang chạy
2. Database đã được setup
3. Tài khoản admin đã tạo
4. File permissions đúng

---
**Linh2Store** - Website bán son môi & mỹ phẩm cao cấp ✨
