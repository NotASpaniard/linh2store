<?php
use App\Core\Security;
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Thanh toán</h1>
<p>Quy trình nhiều bước: Đang bảo trì. Tạm thời điền thông tin cơ bản.</p>
<form method="post" action="/thanh-toan" style="background:#fff;padding:16px;border-radius:12px;max-width:520px;">
  <div style="margin-bottom:12px;">
    <label>Họ tên</label>
    <input required type="text" name="name" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <div style="margin-bottom:12px;">
    <label>Địa chỉ</label>
    <input required type="text" name="address" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
  </div>
  <div style="margin-bottom:12px;">
    <label>Phương thức thanh toán</label>
    <select name="payment" style="width:100%;padding:10px;border-radius:8px;border:1px solid #ddd;">
      <option>COD</option>
      <option>Chuyển khoản</option>
      <option>Ví điện tử</option>
    </select>
  </div>
  <button class="btn-cta" type="submit">Đặt hàng</button>
</form>
<?php
$content = ob_get_clean();
$title = 'Thanh toán - Linh2Store';
include __DIR__ . '/../layout/base.php';
