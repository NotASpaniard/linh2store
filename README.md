# Linh2Store - Website Bán Son Môi & Mỹ Phẩm Cao Cấp

## 📋 Mô tả dự án

Linh2Store là website thương mại điện tử chuyên bán son môi và mỹ phẩm cao cấp dành cho phụ nữ 18-45 tuổi. Website được thiết kế với giao diện hiện đại, tông màu xanh pastel và hồng pastel, tạo cảm giác tinh tế và sang trọng.


## 🏗️ Cấu trúc dự án

```
linh2store/
├── assets/
│   ├── css/
│   │   └── main.css          # CSS framework tùy chỉnh
│   └── js/
│       └── main.js          # JavaScript chính
├── config/
│   ├── database.php         # Cấu hình database
│   └── session.php          # Quản lý session
├── database/
│   └── schema.sql           # Cấu trúc database
├── auth/
│   ├── dang-nhap.php        # Trang đăng nhập
│   ├── dang-ky.php          # Trang đăng ký
│   └── dang-xuat.php         # Trang đăng xuất
├── san-pham/
│   ├── index.php            # Danh sách sản phẩm
│   └── chi-tiet.php         # Chi tiết sản phẩm
│   └── index.php            # Trang giỏ hàng
├── user/
│   └── index.php            # User dashboard
├── admin/
│   └── index.php            # Admin dashboard
├── api/
│   └── cart.php             # API giỏ hàng
├── index.php                # Trang chủ
├── bao-tri.php              # Trang bảo trì
└── README.md               # Tài liệu dự án
```

## 🚀 Cài đặt

### Yêu cầu hệ thống:
- PHP 7.4+
- MySQL 5.7+
- XAMPP (khuyến nghị)

### Các bước cài đặt:

1. **Clone dự án:**
   ```bash
   git clone [repository-url]
   cd linh2store
   ```

2. **Cấu hình database:**
   - Tạo database `linh2store`
   - Import file `database/schema.sql`
   - Cập nhật thông tin kết nối trong `config/database.php`

3. **Cấu hình OAuth (Google/Facebook):**
   ```bash
   # Copy file template
   cp config/oauth-config.example.php config/oauth-config.php
   
   # Chỉnh sửa file oauth-config.php với credentials thật của bạn
   
   ```

4. **Cấu hình web server:**
   - Đặt thư mục dự án vào `htdocs` của XAMPP
   - Truy cập `http://localhost/linh2store`

5. **Tạo tài khoản admin:**
   - Username: `admin`
   - Email: `admin@linh2store.com`
   - Password: `password`

## 📱 Tính năng

### ✅ Đã hoàn thành:
- [x] Giao diện responsive
- [x] Hệ thống đăng nhập/đăng ký
- [x] Trang chủ với sản phẩm nổi bật
- [x] Danh sách sản phẩm với bộ lọc
- [x] Chi tiết sản phẩm
- [x] Giỏ hàng
- [x] User dashboard
- [x] Admin dashboard
- [x] CSS framework tùy chỉnh

### 🔄 Đang phát triển:
- [ ] Thanh toán
- [ ] Quản lý đơn hàng
- [ ] Quản lý sản phẩm
- [ ] Hệ thống đánh giá
- [ ] Wishlist
- [ ] Tìm kiếm nâng cao


## 🔧 Công nghệ sử dụng

### Backend:
- PHP 7.4+
- MySQL với PDO
- Custom session management

### Frontend:
- HTML5, CSS3, JavaScript
- Font Awesome icons
- Custom CSS framework
- Responsive design

### Development:
- XAMPP local environment
- Git version control

