# Linh2Store - Website Bán Son Môi & Mỹ Phẩm Cao Cấp

## 📋 Mô tả dự án

Linh2Store là website thương mại điện tử chuyên bán son môi và mỹ phẩm cao cấp dành cho phụ nữ 18-45 tuổi. Website được thiết kế với giao diện hiện đại, tông màu xanh pastel và hồng pastel, tạo cảm giác tinh tế và sang trọng.

## 🎨 Thiết kế

### Màu sắc chủ đạo
- **Xanh pastel nhạt** (#E3F2FD) - màu chủ đạo
- **Xanh pastel đậm** (#BBDEFB) - secondary
- **Hồng pastel** (#FCE4EC) - accent nhẹ
- **Hồng cá tính** (#EC407A) - CTA buttons
- **Xám nhạt** (#F5F5F5) - background phụ
- **Trắng** (#FFFFFF) - nền chính

### Typography
- **Font chính**: Poppins cho body text
- **Font phụ**: Playfair Display cho tiêu đề
- **Kích thước chuẩn**: 16px base, scale hợp lý

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

## 🗄️ Database

### Các bảng chính:
- `users` - Người dùng
- `brands` - Thương hiệu
- `products` - Sản phẩm
- `product_colors` - Màu son
- `categories` - Danh mục
- `cart` - Giỏ hàng
- `orders` - Đơn hàng
- `order_items` - Chi tiết đơn hàng
- `reviews` - Đánh giá
- `wishlist` - Yêu thích

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
   # ⚠️ QUAN TRỌNG: File oauth-config.php đã được .gitignore, không commit lên GitHub
   ```

4. **Cấu hình web server:**
   - Đặt thư mục dự án vào `htdocs` của XAMPP
   - Truy cập `http://localhost/linh2store`

5. **Tạo tài khoản admin:**
   - Username: `admin`
   - Email: `admin@linh2store.com`
   - Password: `password`

## 🔐 Bảo mật

### OAuth Credentials:
- **File `config/oauth-config.php` chứa thông tin nhạy cảm**
- **Đã được .gitignore để không commit lên GitHub**
- Sử dụng file `config/oauth-config.example.php` làm template
- Thay thế `YOUR_*_HERE` bằng credentials thật của bạn

### Files được bảo vệ:
- `config/oauth-config.php` - OAuth credentials
- `.env` - Environment variables
- `config/database-secrets.php` - Database secrets (nếu có)
- `*.log` - Log files

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

## 🎯 Đường dẫn kiểm tra

### Trang chính:
- **Trang chủ**: `http://localhost/linh2store/`
- **Sản phẩm**: `http://localhost/linh2store/san-pham/`
- **Thanh toán**: `http://localhost/linh2store/thanh-toan/`

### Xác thực:
- **Đăng nhập**: `http://localhost/linh2store/auth/dang-nhap.php`
- **Đăng ký**: `http://localhost/linh2store/auth/dang-ky.php`

### Dashboard:
- **User Dashboard**: `http://localhost/linh2store/user/`
- **Admin Dashboard**: `http://localhost/linh2store/admin/`

### Trang bổ sung:
- **Thương hiệu**: `http://localhost/linh2store/thuong-hieu/`
- **Blog**: `http://localhost/linh2store/blog/`
- **Liên hệ**: `http://localhost/linh2store/lien-he/`
- **Bảo trì**: `http://localhost/linh2store/bao-tri.php`

## 📊 Dữ liệu mẫu

Website đã được tích hợp với **100 sản phẩm mẫu** từ 10 thương hiệu nổi tiếng:
- MAC, Chanel, Dior, YSL, Tom Ford
- NARS, Urban Decay, Fenty Beauty
- Charlotte Tilbury, Pat McGrath

### Hình ảnh placeholder:
- Sử dụng Unsplash API cho hình ảnh sản phẩm
- Placeholder services cho logo thương hiệu
- Tự động tạo hình ảnh với màu sắc thương hiệu

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

## 📝 Ghi chú

- Website được thiết kế theo nguyên tắc mobile-first
- Sử dụng màu sắc xanh pastel và hồng pastel (KHÔNG sử dụng màu vàng)
- Tất cả comment và giao diện đều sử dụng tiếng Việt
- Cấu trúc thư mục được tổ chức rõ ràng, dễ bảo trì

## 🤝 Đóng góp

Nếu bạn muốn đóng góp vào dự án, vui lòng:
1. Fork repository
2. Tạo feature branch
3. Commit changes
4. Push to branch
5. Tạo Pull Request

## 📄 License

Dự án này được phát hành dưới giấy phép MIT.
