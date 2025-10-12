<?php
use App\Core\Security;
ob_start();
?>
<section class="hero">
  <div>
    <h1>Thăng hoa vẻ đẹp với Linh2Store</h1>
    <p>Son môi & mỹ phẩm cao cấp, tôn vinh làn da Việt.</p>
    <div class="actions">
      <a href="/san-pham"><button class="btn-cta">Khám phá ngay</button></a>
    </div>
  </div>
  <div>
    <img src="/assets/img/hero.jpg" alt="Hero Linh2Store" style="width:100%; border-radius:12px;" />
  </div>
</section>

<section class="container" style="margin-top:24px;">
  <h2 style="font-family: 'Playfair Display', serif;">Sản phẩm nổi bật</h2>
  <div class="row">
    <?php foreach (($featured ?? []) as $p): ?>
      <div class="col" style="width:100%; max-width: 33.33%; flex: 0 0 33.33%;">
        <div class="product-card">
          <img src="<?= Security::e($p['img']) ?>" alt="<?= Security::e($p['name']) ?>" />
          <div class="name"><?= Security::e($p['name']) ?></div>
          <div class="price"><?= number_format($p['price']) ?> đ</div>
          <div class="swatches">
            <?php foreach ($p['colors'] as $c): ?>
              <div class="swatch" style="background: <?= Security::e($c) ?>"></div>
            <?php endforeach; ?>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="container" style="margin-top:24px;">
  <h2 style="font-family: 'Playfair Display', serif;">Tin tức làm đẹp</h2>
  <p>Đang bảo trì</p>
</section>
<section class="container" style="margin-top:24px;">
  <h2 style="font-family: 'Playfair Display', serif;">Khách hàng nói gì?</h2>
  <p>Testimonial: Đang bảo trì</p>
</section>

<?php
$content = ob_get_clean();
$title = 'Trang chủ - Linh2Store';
include __DIR__ . '/../layout/base.php';
