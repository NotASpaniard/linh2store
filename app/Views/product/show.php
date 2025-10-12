<?php
use App\Core\Security;
ob_start();
?>
<article class="row">
  <div class="col" style="width:100%;max-width:50%;flex:0 0 50%;">
    <div style="position:relative;overflow:hidden;border-radius:12px;">
      <img src="<?= Security::e($product['img']) ?>" alt="<?= Security::e($product['name']) ?>" style="width:100%;transition:transform .2s ease;" onmouseover="this.style.transform='scale(1.06)'" onmouseout="this.style.transform='scale(1)'" />
    </div>
  </div>
  <div class="col" style="width:100%;max-width:50%;flex:0 0 50%;">
    <h1 style="font-family: 'Playfair Display', serif; margin-top:0;"><?= Security::e($product['name']) ?></h1>
    <div class="price" style="font-size:20px;color:#C2185B;font-weight:700;"><?= number_format($product['price']) ?> đ</div>
    <div class="swatches">
      <?php foreach ($product['colors'] as $c): ?>
        <div class="swatch" title="Màu" style="background: <?= Security::e($c) ?>"></div>
      <?php endforeach; ?>
    </div>
    <p>Thông tin kỹ thuật, thành phần: Đang bảo trì</p>
    <form method="post" action="/gio-hang/them">
      <input type="hidden" name="product_id" value="<?= (int)$product['id'] ?>">
      <button class="btn-cta" type="submit">Thêm vào giỏ</button>
    </form>
  </div>
</article>
<?php
$content = ob_get_clean();
$title = Security::e(($product['name'] ?? 'Sản phẩm')) . ' - Linh2Store';
include __DIR__ . '/../layout/base.php';
