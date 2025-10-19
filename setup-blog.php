<?php
/**
 * Blog System Setup
 * Linh2Store - Setup database và dữ liệu mẫu cho blog
 */

require_once 'config/database.php';

try {
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h1>🚀 Blog System Setup</h1>";
    echo "<p>Đang thiết lập hệ thống blog...</p>";
    
    // Đọc và chạy schema
    $schema = file_get_contents('database/blog-schema.sql');
    $statements = explode(';', $schema);
    
    foreach ($statements as $statement) {
        $statement = trim($statement);
        if (!empty($statement)) {
            $conn->exec($statement);
        }
    }
    
    echo "<p>✅ Database schema đã được tạo thành công!</p>";
    
    // Insert dữ liệu mẫu
    echo "<p>Đang thêm dữ liệu mẫu...</p>";
    
    // Categories
    $categories = [
        ['Xu hướng làm đẹp', 'xu-huong-lam-dep', 'Những xu hướng làm đẹp mới nhất'],
        ['Tips làm đẹp', 'tips-lam-dep', 'Mẹo và bí quyết làm đẹp'],
        ['Review sản phẩm', 'review-san-pham', 'Đánh giá chi tiết các sản phẩm mỹ phẩm'],
        ['Chăm sóc da', 'cham-soc-da', 'Hướng dẫn chăm sóc da mặt'],
        ['Phong cách', 'phong-cach', 'Phong cách thời trang và makeup'],
        ['Kiến thức', 'kien-thuc', 'Kiến thức về mỹ phẩm và làm đẹp']
    ];
    
    $stmt = $conn->prepare("INSERT INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
    foreach ($categories as $category) {
        $stmt->execute($category);
    }
    
    // Tags
    $tags = [
        ['Son môi', 'son-moi'],
        ['Kem nền', 'kem-nen'],
        ['Mascara', 'mascara'],
        ['Phấn mắt', 'phan-mat'],
        ['Chăm sóc da', 'cham-soc-da'],
        ['Xu hướng', 'xu-huong'],
        ['Review', 'review'],
        ['Tips', 'tips']
    ];
    
    $stmt = $conn->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?)");
    foreach ($tags as $tag) {
        $stmt->execute($tag);
    }
    
    // Sample blog posts
    $posts = [
        [
            'title' => 'Xu hướng son môi 2025: Những màu sắc hot nhất',
            'slug' => 'xu-huong-son-moi-2025-nhung-mau-sac-hot-nhat',
            'excerpt' => 'Khám phá những xu hướng son môi mới nhất năm 2025, từ màu nude ấm áp đến những tông đỏ rực rỡ.',
            'content' => '<p>Năm 2025 mang đến những xu hướng son môi vô cùng thú vị và đa dạng. Từ những tông màu nude ấm áp đến những màu đỏ rực rỡ, mỗi màu sắc đều có câu chuyện riêng của mình.</p><p>Xu hướng chủ đạo năm nay là những màu nude ấm áp, phù hợp với mọi tông da. Những màu như nude hồng, nude cam và nude nâu đang được các beauty blogger yêu thích.</p><p>Bên cạnh đó, những màu đỏ cổ điển cũng không bao giờ lỗi thời. Từ đỏ cherry đến đỏ wine, những màu này luôn tạo nên sự quyến rũ và tự tin cho người sử dụng.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1586495777744-4413f21062fa?w=600',
            'author_id' => 1,
            'category_id' => 1,
            'status' => 'published',
            'featured' => 1
        ],
        [
            'title' => 'Cách chọn son môi phù hợp với tông da',
            'slug' => 'cach-chon-son-moi-phu-hop-voi-tong-da',
            'excerpt' => 'Hướng dẫn chi tiết cách chọn màu son môi phù hợp với từng tông da để tôn lên vẻ đẹp tự nhiên.',
            'content' => '<p>Việc chọn đúng màu son môi có thể thay đổi hoàn toàn gương mặt của bạn. Một màu son phù hợp sẽ giúp tôn lên vẻ đẹp tự nhiên và tạo sự tự tin.</p><p>Đối với da trắng hồng, những màu nude hồng và đỏ cherry sẽ rất phù hợp. Với da ngăm, những màu nude nâu và đỏ wine sẽ tạo nên sự quyến rũ.</p><p>Quan trọng nhất là bạn phải thử màu trước khi mua. Hãy thử trên môi thật để thấy được hiệu ứng cuối cùng.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c0?w=600',
            'author_id' => 1,
            'category_id' => 2,
            'status' => 'published',
            'featured' => 1
        ],
        [
            'title' => 'Review top 5 son môi MAC được yêu thích nhất',
            'slug' => 'review-top-5-son-moi-mac-duoc-yeu-thich-nhat',
            'excerpt' => 'Đánh giá chi tiết 5 màu son môi MAC được các beauty blogger yêu thích nhất.',
            'content' => '<p>MAC là một trong những thương hiệu son môi được yêu thích nhất hiện nay. Với chất lượng cao và màu sắc đa dạng, MAC đã chinh phục được trái tim của hàng triệu phụ nữ.</p><p>1. Ruby Woo - Màu đỏ cổ điển không bao giờ lỗi thời<br>2. Velvet Teddy - Nude ấm áp phù hợp mọi tông da<br>3. Chili - Đỏ cam cá tính và năng động<br>4. Twig - Nude hồng thanh lịch<br>5. Diva - Đỏ wine quyến rũ</p><p>Mỗi màu đều có đặc điểm riêng và phù hợp với những phong cách khác nhau.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c1?w=600',
            'author_id' => 1,
            'category_id' => 3,
            'status' => 'published',
            'featured' => 1
        ],
        [
            'title' => 'Cách bảo quản son môi để giữ được lâu',
            'slug' => 'cach-bao-quan-son-moi-de-giu-duoc-lau',
            'excerpt' => 'Những mẹo hay để bảo quản son môi đúng cách, giúp sản phẩm giữ được chất lượng tốt nhất.',
            'content' => '<p>Son môi là một sản phẩm mỹ phẩm cần được bảo quản cẩn thận để giữ được chất lượng và thời hạn sử dụng.</p><p>1. Tránh ánh nắng trực tiếp - Để son ở nơi khô ráo, thoáng mát<br>2. Đậy nắp kín - Luôn đậy nắp sau khi sử dụng<br>3. Không chia sẻ - Tránh dùng chung để ngăn vi khuẩn<br>4. Kiểm tra hạn sử dụng - Thay mới khi cần thiết<br>5. Vệ sinh định kỳ - Lau sạch phần đầu son</p><p>Với những mẹo này, son môi của bạn sẽ giữ được chất lượng tốt nhất.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c2?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Son môi cho từng dịp: Công sở, hẹn hò, tiệc tùng',
            'slug' => 'son-moi-cho-tung-dip-cong-so-hen-ho-tiec-tung',
            'excerpt' => 'Gợi ý màu son môi phù hợp cho từng hoàn cảnh và dịp đặc biệt.',
            'content' => '<p>Mỗi dịp khác nhau đòi hỏi một phong cách son môi khác nhau. Việc chọn đúng màu sẽ giúp bạn tự tin và phù hợp với hoàn cảnh.</p><p><strong>Công sở:</strong> Nude hồng, nude nâu - Thanh lịch và chuyên nghiệp<br><strong>Hẹn hò:</strong> Đỏ cherry, hồng pastel - Quyến rũ và nữ tính<br><strong>Tiệc tùng:</strong> Đỏ wine, đỏ cam - Nổi bật và cá tính<br><strong>Hàng ngày:</strong> Nude ấm, hồng nhạt - Tự nhiên và thoải mái</p><p>Hãy chọn màu phù hợp với phong cách và hoàn cảnh của bạn.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c3?w=600',
            'author_id' => 1,
            'category_id' => 5,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'So sánh son môi lì vs son môi bóng: Nên chọn loại nào?',
            'slug' => 'so-sanh-son-moi-li-vs-son-moi-bong-nen-chon-loai-nao',
            'excerpt' => 'Phân tích ưu nhược điểm của son môi lì và son môi bóng để bạn có lựa chọn phù hợp.',
            'content' => '<p>Son môi lì và son môi bóng đều có những ưu điểm riêng. Việc chọn loại nào phụ thuộc vào sở thích và phong cách của bạn.</p><p><strong>Son môi lì:</strong><br>✅ Bền màu lâu<br>✅ Không bóng dầu<br>✅ Phù hợp công sở<br>❌ Có thể khô môi<br>❌ Khó tẩy trang</p><p><strong>Son môi bóng:</strong><br>✅ Dưỡng ẩm tốt<br>✅ Dễ tẩy trang<br>✅ Phù hợp trẻ trung<br>❌ Dễ trôi<br>❌ Cần bôi lại thường xuyên</p><p>Hãy chọn loại phù hợp với nhu cầu và sở thích của bạn.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1594736797933-d0c0c0c0c0c4?w=600',
            'author_id' => 1,
            'category_id' => 6,
            'status' => 'published',
            'featured' => 0
        ]
    ];
    
    $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, excerpt, content, featured_image, author_id, category_id, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute($post);
    }
    
    // Link posts with tags
    $postTags = [
        [1, 1], [1, 6], [1, 7], // Post 1: Son môi, Xu hướng, Review
        [2, 1], [2, 8], [2, 4], // Post 2: Son môi, Tips, Chăm sóc da
        [3, 1], [3, 7], [3, 6], // Post 3: Son môi, Review, Xu hướng
        [4, 1], [4, 5], [4, 8], // Post 4: Son môi, Chăm sóc da, Tips
        [5, 1], [5, 6], [5, 8], // Post 5: Son môi, Xu hướng, Tips
        [6, 1], [6, 8], [6, 6]  // Post 6: Son môi, Tips, Xu hướng
    ];
    
    $stmt = $conn->prepare("INSERT INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
    foreach ($postTags as $postTag) {
        $stmt->execute($postTag);
    }
    
    // Sample SEO data
    $seoData = [
        [1, 'Xu hướng son môi 2025', 'Khám phá những xu hướng son môi mới nhất năm 2025', 'son môi, xu hướng, 2025, makeup, beauty'],
        [2, 'Cách chọn son môi phù hợp', 'Hướng dẫn chọn màu son môi phù hợp với tông da', 'chọn son môi, tông da, màu sắc, makeup'],
        [3, 'Review son môi MAC', 'Đánh giá top 5 son môi MAC được yêu thích', 'review, son môi MAC, đánh giá, beauty'],
        [4, 'Bảo quản son môi', 'Mẹo bảo quản son môi để giữ được lâu', 'bảo quản son môi, mẹo, chăm sóc'],
        [5, 'Son môi theo dịp', 'Gợi ý màu son môi cho từng hoàn cảnh', 'son môi, dịp, công sở, hẹn hò'],
        [6, 'Son môi lì vs bóng', 'So sánh ưu nhược điểm son môi lì và bóng', 'son môi lì, son môi bóng, so sánh']
    ];
    
    $stmt = $conn->prepare("INSERT INTO blog_seo (post_id, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?)");
    foreach ($seoData as $seo) {
        $stmt->execute($seo);
    }
    
    echo "<p>✅ Dữ liệu mẫu đã được thêm thành công!</p>";
    echo "<p>🎉 Blog system đã sẵn sàng sử dụng!</p>";
    echo "<p><a href='blog/'>Xem blog</a> | <a href='admin/blog.php'>Quản trị blog</a></p>";
    
} catch (Exception $e) {
    echo "<p>❌ Lỗi: " . $e->getMessage() . "</p>";
}
?>
