<?php
use App\Core\Security;
ob_start();
?>
<h1 style="font-family: 'Playfair Display', serif;">Tất cả sản phẩm</h1>
<div style="display:flex;gap:12px;margin:12px 0;">
  <input placeholder="Tìm nhanh (đang bảo trì autocomplete)" style="flex:1;padding:10px;border:1px solid #ddd;border-radius:8px;">
  <select style="padding:10px;border:1px solid #ddd;border-radius:8px;">
    <option>Mới nhất</option>
    <option>Giá tăng dần</option>
    <option>Giá giảm dần</option>
  </select>
  <select style="padding:10px;border:1px solid #ddd;border-radius:8px;">
    <option>Tất cả thương hiệu</option>
  </select>
  <select style="padding:10px;border:1px solid #ddd;border-radius:8px;">
    <option>Tất cả màu</option>
  </select>
</div>
<div class="row">
  <?php foreach (($products ?? []) as $p): ?>
    <div class="col" style="width:100%; max-width: 25%; flex: 0 0 25%;">
      <a href="/san-pham/chi-tiet?id=<?= (int)$p['id'] ?>" style="text-decoration:none;color:inherit;">
        <div class="product-card">
          <img src="<?= Security::e($p['img']) ?>" alt="<?= Security::e($p['name']) ?>" />
          <div class="name"><?= Security::e($p['name']) ?></div>
          <div class="price"><?= number_format($p['price']) ?> đ</div>
          <div class="swatches">
            <?php foreach ($p['colors'] as $c): ?>
              <div class="swatch" title="Màu" style="background: <?= Security::e($c) ?>"></div>
            <?php endforeach; ?>
          </div>
        </div>
      </a>
    </div>
  <?php endforeach; ?>
</div>
<?php
$content = ob_get_clean();
$title = 'Sản phẩm - Linh2Store';
include __DIR__ . '/../layout/base.php';
