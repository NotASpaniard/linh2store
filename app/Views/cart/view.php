<?php
use App\Core\Security;
ob_start();
$total = 0;
?>
<h1 style="font-family: 'Playfair Display', serif;">Giỏ hàng</h1>
<?php if (empty($cart)): ?>
  <p>Giỏ hàng trống.</p>
<?php else: ?>
  <table style="width:100%;background:#fff;border-radius:12px;overflow:hidden;border-collapse:collapse;">
    <thead style="background:#BBDEFB;">
      <tr>
        <th style="text-align:left;padding:10px;">Sản phẩm</th>
        <th style="text-align:right;padding:10px;">Giá</th>
        <th style="text-align:center;padding:10px;">SL</th>
        <th style="text-align:right;padding:10px;">Tạm tính</th>
        <th style="padding:10px;">#</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($cart as $id => $line): $p = $line['product']; $qty = $line['qty']; $sub = $p['price'] * $qty; $total += $sub; ?>
        <tr>
          <td style="padding:10px;">
            <div style="display:flex;gap:12px;align-items:center;">
              <img src="<?= Security::e($p['img']) ?>" alt="" style="width:64px;height:64px;border-radius:8px;object-fit:cover;" />
              <div>
                <div class="name" style="font-weight:600;"><?= Security::e($p['name']) ?></div>
              </div>
            </div>
          </td>
          <td style="text-align:right;padding:10px;"><?= number_format($p['price']) ?> đ</td>
          <td style="text-align:center;padding:10px;">
            <form method="post" action="/gio-hang/cap-nhat" style="display:flex;gap:6px;justify-content:center;">
              <input type="hidden" name="product_id" value="<?= (int)$id ?>" />
              <input type="number" min="1" name="qty" value="<?= (int)$qty ?>" style="width:64px;padding:6px;">
              <button type="submit">Cập nhật</button>
            </form>
          </td>
          <td style="text-align:right;padding:10px;"><?= number_format($sub) ?> đ</td>
          <td style="padding:10px;">
            <form method="post" action="/gio-hang/xoa">
              <input type="hidden" name="product_id" value="<?= (int)$id ?>" />
              <button type="submit">Xoá</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
  <div style="text-align:right;margin-top:12px;">
    <strong>Tổng tiền: <?= number_format($total) ?> đ</strong>
  </div>
  <div style="text-align:right;margin-top:12px;">
    <a href="/thanh-toan"><button class="btn-cta">Thanh toán</button></a>
  </div>
<?php endif; ?>
<?php
$content = ob_get_clean();
$title = 'Giỏ hàng - Linh2Store';
include __DIR__ . '/../layout/base.php';
