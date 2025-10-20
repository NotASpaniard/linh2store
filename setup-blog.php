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
    
    $stmt = $conn->prepare("INSERT IGNORE INTO blog_categories (name, slug, description) VALUES (?, ?, ?)");
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
    
    $stmt = $conn->prepare("INSERT IGNORE INTO blog_tags (name, slug) VALUES (?, ?)");
    foreach ($tags as $tag) {
        $stmt->execute($tag);
    }
    
    // Sample blog posts - 20 bài viết về mỹ phẩm, sắc đẹp, sức khỏe
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
        ],
        [
            'title' => 'Kem nền cho da dầu: Cách chọn và sử dụng đúng',
            'slug' => 'kem-nen-cho-da-dau-cach-chon-va-su-dung-dung',
            'excerpt' => 'Hướng dẫn chi tiết cách chọn kem nền phù hợp cho da dầu và cách sử dụng hiệu quả.',
            'content' => '<p>Da dầu cần được chăm sóc đặc biệt, đặc biệt là trong việc chọn kem nền. Một loại kem nền phù hợp sẽ giúp kiểm soát dầu và tạo lớp nền hoàn hảo.</p><p><strong>Đặc điểm kem nền cho da dầu:</strong><br>• Công thức matte, không bóng dầu<br>• Khả năng kiểm soát dầu tốt<br>• Bền màu lâu, không trôi<br>• Có thành phần chống nước</p><p><strong>Cách sử dụng:</strong><br>1. Làm sạch da và dưỡng ẩm<br>2. Sử dụng primer matte<br>3. Thoa kem nền từ trong ra ngoài<br>4. Set phấn để tăng độ bền</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1596462502278-27bfdc403348?w=600',
            'author_id' => 1,
            'category_id' => 2,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Mascara waterproof: Top 5 sản phẩm tốt nhất',
            'slug' => 'mascara-waterproof-top-5-san-pham-tot-nhat',
            'excerpt' => 'Đánh giá 5 loại mascara waterproof được yêu thích nhất, phù hợp cho mọi hoạt động.',
            'content' => '<p>Mascara waterproof là lựa chọn hoàn hảo cho những ngày mưa, bơi lội hoặc khi bạn cần độ bền cao.</p><p><strong>Top 5 Mascara Waterproof:</strong><br>1. Maybelline Lash Sensational - Giá cả phải chăng, hiệu ứng tốt<br>2. L\'Oréal Voluminous - Tạo độ dày và dài<br>3. Too Faced Better Than Sex - Hiệu ứng dramatic<br>4. Benefit They\'re Real - Tạo độ cong tự nhiên<br>5. Lancôme Hypnôse - Cao cấp, bền màu</p><p>Mỗi sản phẩm đều có ưu điểm riêng, hãy chọn theo nhu cầu và ngân sách của bạn.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1512201078372-9cbbd2f25681?w=600',
            'author_id' => 1,
            'category_id' => 3,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Chăm sóc da mụn: Quy trình skincare cơ bản',
            'slug' => 'cham-soc-da-mun-quy-trinh-skincare-co-ban',
            'excerpt' => 'Hướng dẫn quy trình chăm sóc da mụn từ A-Z, giúp da sạch mụn và khỏe mạnh.',
            'content' => '<p>Da mụn cần được chăm sóc đặc biệt với quy trình phù hợp. Việc sử dụng đúng sản phẩm sẽ giúp cải thiện tình trạng da đáng kể.</p><p><strong>Quy trình skincare cho da mụn:</strong><br>1. <strong>Làm sạch:</strong> Sử dụng sữa rửa mặt dịu nhẹ, không chứa sulfate<br>2. <strong>Toner:</strong> Cân bằng pH, thu nhỏ lỗ chân lông<br>3. <strong>Serum:</strong> Niacinamide, BHA để kiểm soát dầu<br>4. <strong>Kem dưỡng:</strong> Non-comedogenic, dưỡng ẩm nhẹ<br>5. <strong>Kem chống nắng:</strong> SPF 30+ mỗi ngày</p><p>Lưu ý: Tránh sử dụng quá nhiều sản phẩm cùng lúc, hãy kiên nhẫn và theo dõi phản ứng của da.</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1570194065650-d99fb4bedf0a?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Phấn mắt cho người mới bắt đầu: Hướng dẫn cơ bản',
            'slug' => 'phan-mat-cho-nguoi-moi-bat-dau-huong-dan-co-ban',
            'excerpt' => 'Hướng dẫn chi tiết cách sử dụng phấn mắt cho người mới bắt đầu, từ chọn màu đến kỹ thuật blend.',
            'content' => '<p>Phấn mắt có thể tạo nên sự khác biệt lớn cho gương mặt, nhưng cần kỹ thuật đúng để tránh trông quá đậm hoặc không tự nhiên.</p><p><strong>Bộ dụng cụ cơ bản:</strong><br>• Phấn mắt neutral tones (nâu, be, hồng nhạt)<br>• Cọ phấn mắt (blending brush, shader brush)<br>• Primer mắt để tăng độ bền<br>• Mascara để hoàn thiện</p><p><strong>Kỹ thuật cơ bản:</strong><br>1. Thoa primer lên mí mắt<br>2. Dùng màu sáng làm base<br>3. Thêm màu trung tính vào crease<br>4. Blend kỹ để tạo độ chuyển<br>5. Highlight ở góc trong mắt</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1512496015851-a90fb38ba796?w=600',
            'author_id' => 1,
            'category_id' => 2,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Tẩy trang đúng cách: Bước quan trọng trong skincare',
            'slug' => 'tay-trang-dung-cach-buoc-quan-trong-trong-skincare',
            'excerpt' => 'Hướng dẫn cách tẩy trang đúng cách để da sạch hoàn toàn và khỏe mạnh.',
            'content' => '<p>Tẩy trang là bước quan trọng nhất trong quy trình skincare. Da sạch sẽ giúp các sản phẩm dưỡng da thẩm thấu tốt hơn.</p><p><strong>Các loại tẩy trang:</strong><br>• <strong>Dầu tẩy trang:</strong> Hiệu quả với makeup đậm<br>• <strong>Sữa tẩy trang:</strong> Dịu nhẹ cho da nhạy cảm<br>• <strong>Micellar water:</strong>: Tiện lợi, không cần rửa lại<br>• <strong>Gel tẩy trang:</strong> Phù hợp da dầu</p><p><strong>Quy trình tẩy trang:</strong><br>1. Rửa tay sạch<br>2. Thoa tẩy trang lên mặt khô<br>3. Massage nhẹ nhàng<br>4. Rửa sạch với nước ấm<br>5. Lau khô bằng khăn sạch</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Serum vitamin C: Lợi ích và cách sử dụng',
            'slug' => 'serum-vitamin-c-loi-ich-va-cach-su-dung',
            'excerpt' => 'Tìm hiểu về lợi ích của serum vitamin C và cách sử dụng hiệu quả cho làn da sáng khỏe.',
            'content' => '<p>Serum vitamin C là một trong những sản phẩm chống lão hóa hiệu quả nhất, giúp da sáng mịn và đều màu.</p><p><strong>Lợi ích của vitamin C:</strong><br>• Chống oxy hóa, bảo vệ da khỏi tác hại môi trường<br>• Kích thích sản xuất collagen<br>• Làm sáng da, giảm thâm nám<br>• Tăng cường hiệu quả kem chống nắng</p><p><strong>Cách sử dụng:</strong><br>1. Sử dụng vào buổi sáng<br>2. Thoa sau toner, trước kem dưỡng<br>3. Bắt đầu với nồng độ thấp (5-10%)<br>4. Luôn kết hợp với kem chống nắng<br>5. Bảo quản ở nơi tối, mát</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Kem chống nắng: Hướng dẫn chọn và sử dụng',
            'slug' => 'kem-chong-nang-huong-dan-chon-va-su-dung',
            'excerpt' => 'Hướng dẫn chi tiết cách chọn kem chống nắng phù hợp và sử dụng đúng cách để bảo vệ da.',
            'content' => '<p>Kem chống nắng là sản phẩm quan trọng nhất trong skincare, giúp bảo vệ da khỏi tác hại của tia UV.</p><p><strong>Chọn kem chống nắng:</strong><br>• SPF 30+ cho hoạt động hàng ngày<br>• SPF 50+ cho hoạt động ngoài trời<br>• Broad spectrum (chống cả UVA và UVB)<br>• Phù hợp với loại da</p><p><strong>Cách sử dụng:</strong><br>1. Thoa 30 phút trước khi ra nắng<br>2. Lượng đủ: 1/4 thìa cà phê cho mặt<br>3. Thoa lại sau 2 giờ<br>4. Thoa lại sau khi bơi, đổ mồ hôi<br>5. Sử dụng hàng ngày, kể cả trời râm</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Retinol: Thần dược chống lão hóa da',
            'slug' => 'retinol-than-duoc-chong-lao-hoa-da',
            'excerpt' => 'Tìm hiểu về retinol - thành phần chống lão hóa mạnh mẽ và cách sử dụng an toàn.',
            'content' => '<p>Retinol là một trong những thành phần chống lão hóa hiệu quả nhất, được các chuyên gia da liễu khuyên dùng.</p><p><strong>Lợi ích của retinol:</strong><br>• Kích thích tái tạo tế bào da<br>• Giảm nếp nhăn, làm mịn da<br>• Cải thiện kết cấu da<br>• Giảm mụn và lỗ chân lông to</p><p><strong>Cách sử dụng an toàn:</strong><br>1. Bắt đầu với nồng độ thấp (0.1-0.25%)<br>2. Sử dụng 2-3 lần/tuần<br>3. Chỉ dùng vào buổi tối<br>4. Luôn kết hợp với kem chống nắng<br>5. Tránh sử dụng với vitamin C</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Hyaluronic Acid: Dưỡng ẩm sâu cho da',
            'slug' => 'hyaluronic-acid-duong-am-sau-cho-da',
            'excerpt' => 'Khám phá lợi ích của hyaluronic acid trong việc dưỡng ẩm và làm căng da.',
            'content' => '<p>Hyaluronic Acid là thành phần dưỡng ẩm tự nhiên của da, có khả năng giữ nước gấp 1000 lần trọng lượng của nó.</p><p><strong>Lợi ích:</strong><br>• Dưỡng ẩm sâu, giữ nước cho da<br>• Làm căng da, giảm nếp nhăn<br>• An toàn cho mọi loại da<br>• Tương thích với các thành phần khác</p><p><strong>Cách sử dụng:</strong><br>1. Có thể dùng cả sáng và tối<br>2. Thoa sau toner, trước kem dưỡng<br>3. Kết hợp với các serum khác<br>4. Sử dụng hàng ngày để có hiệu quả tốt nhất</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Niacinamide: Giải pháp cho da dầu và lỗ chân lông',
            'slug' => 'niacinamide-giai-phap-cho-da-dau-va-lo-chan-long',
            'excerpt' => 'Tìm hiểu về niacinamide - thành phần đa năng giúp kiểm soát dầu và thu nhỏ lỗ chân lông.',
            'content' => '<p>Niacinamide là một vitamin B3 có nhiều lợi ích cho da, đặc biệt hiệu quả với da dầu và lỗ chân lông to.</p><p><strong>Lợi ích của niacinamide:</strong><br>• Kiểm soát dầu, giảm bóng nhờn<br>• Thu nhỏ lỗ chân lông<br>• Cải thiện kết cấu da<br>• Giảm viêm, làm dịu da</p><p><strong>Cách sử dụng:</strong><br>1. Nồng độ 5-10% là an toàn<br>2. Có thể dùng cả sáng và tối<br>3. Kết hợp với retinol để tăng hiệu quả<br>4. Tránh sử dụng với vitamin C</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Peptide: Thành phần chống lão hóa thế hệ mới',
            'slug' => 'peptide-thanh-phan-chong-lao-hoa-the-he-moi',
            'excerpt' => 'Khám phá peptide - thành phần chống lão hóa tiên tiến giúp da săn chắc và trẻ trung.',
            'content' => '<p>Peptide là chuỗi amino acid có khả năng kích thích sản xuất collagen, giúp da săn chắc và giảm nếp nhăn.</p><p><strong>Lợi ích của peptide:</strong><br>• Kích thích sản xuất collagen<br>• Làm săn chắc da<br>• Giảm nếp nhăn<br>• Cải thiện độ đàn hồi da</p><p><strong>Cách sử dụng:</strong><br>1. Thường có trong serum và kem dưỡng<br>2. Sử dụng vào buổi tối<br>3. Kết hợp với retinol để tăng hiệu quả<br>4. Cần thời gian để thấy kết quả</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'AHA/BHA: Tẩy tế bào chết hóa học an toàn',
            'slug' => 'aha-bha-tay-te-bao-chet-hoa-hoc-an-toan',
            'excerpt' => 'Hướng dẫn sử dụng AHA/BHA để tẩy tế bào chết hóa học, giúp da sáng mịn và đều màu.',
            'content' => '<p>AHA và BHA là các acid hữu cơ giúp tẩy tế bào chết hóa học, cải thiện kết cấu da và làm sáng da.</p><p><strong>AHA (Alpha Hydroxy Acid):</strong><br>• Glycolic acid: Mạnh nhất, phù hợp da dầu<br>• Lactic acid: Dịu nhẹ hơn, phù hợp da nhạy cảm<br>• Mandelic acid: Dịu nhẹ nhất</p><p><strong>BHA (Beta Hydroxy Acid):</strong><br>• Salicylic acid: Thấm sâu vào lỗ chân lông<br>• Hiệu quả với da dầu và mụn</p><p><strong>Cách sử dụng:</strong><br>1. Bắt đầu với nồng độ thấp<br>2. Sử dụng 2-3 lần/tuần<br>3. Chỉ dùng vào buổi tối<br>4. Luôn kết hợp với kem chống nắng</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Ceramide: Khôi phục hàng rào bảo vệ da',
            'slug' => 'ceramide-khoi-phuc-hang-rao-bao-ve-da',
            'excerpt' => 'Tìm hiểu về ceramide - thành phần quan trọng giúp khôi phục và bảo vệ hàng rào da.',
            'content' => '<p>Ceramide là lipid tự nhiên trong da, đóng vai trò quan trọng trong việc duy trì hàng rào bảo vệ da khỏe mạnh.</p><p><strong>Lợi ích của ceramide:</strong><br>• Khôi phục hàng rào bảo vệ da<br>• Giữ ẩm, ngăn mất nước<br>• Làm dịu da nhạy cảm<br>• Tăng cường sức đề kháng da</p><p><strong>Cách sử dụng:</strong><br>1. Có trong nhiều sản phẩm dưỡng da<br>2. Sử dụng hàng ngày<br>3. Kết hợp với hyaluronic acid<br>4. Đặc biệt tốt cho da khô và nhạy cảm</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Collagen: Thành phần vàng cho da trẻ trung',
            'slug' => 'collagen-thanh-phan-vang-cho-da-tre-trung',
            'excerpt' => 'Khám phá collagen - protein quan trọng giúp da săn chắc, đàn hồi và trẻ trung.',
            'content' => '<p>Collagen là protein quan trọng nhất trong da, chiếm 70% cấu trúc da, giúp da săn chắc và đàn hồi.</p><p><strong>Lợi ích của collagen:</strong><br>• Duy trì độ săn chắc da<br>• Giảm nếp nhăn<br>• Tăng độ đàn hồi<br>• Làm chậm quá trình lão hóa</p><p><strong>Cách bổ sung collagen:</strong><br>1. <strong>Thực phẩm:</strong> Nước hầm xương, cá, thịt<br>2. <strong>Serum:</strong> Collagen peptide<br>3. <strong>Viên uống:</strong> Collagen thủy phân<br>4. <strong>Kem dưỡng:</strong> Collagen peptide</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ],
        [
            'title' => 'Elastin: Giữ cho da đàn hồi và trẻ trung',
            'slug' => 'elastin-giu-cho-da-dan-hoi-va-tre-trung',
            'excerpt' => 'Tìm hiểu về elastin - protein giúp da đàn hồi và trẻ trung, cách bảo vệ và kích thích sản xuất.',
            'content' => '<p>Elastin là protein quan trọng giúp da đàn hồi và trở về trạng thái ban đầu sau khi bị kéo giãn.</p><p><strong>Vai trò của elastin:</strong><br>• Giúp da đàn hồi<br>• Duy trì hình dạng da<br>• Ngăn chảy xệ<br>• Giữ da trẻ trung</p><p><strong>Cách bảo vệ elastin:</strong><br>1. Tránh ánh nắng mặt trời<br>2. Không hút thuốc<br>3. Chế độ ăn lành mạnh<br>4. Sử dụng kem chống nắng<br>5. Tránh stress</p>',
            'featured_image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=600',
            'author_id' => 1,
            'category_id' => 4,
            'status' => 'published',
            'featured' => 0
        ]
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO blog_posts (title, slug, excerpt, content, featured_image, author_id, category_id, status, featured) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    foreach ($posts as $post) {
        $stmt->execute([
            $post['title'],
            $post['slug'], 
            $post['excerpt'],
            $post['content'],
            $post['featured_image'],
            $post['author_id'],
            $post['category_id'],
            $post['status'],
            $post['featured']
        ]);
    }
    
    // Link posts with tags
    $postTags = [
        [1, 1], [1, 6], [1, 7], // Post 1: Son môi, Xu hướng, Review
        [2, 1], [2, 8], [2, 4], // Post 2: Son môi, Tips, Chăm sóc da
        [3, 1], [3, 7], [3, 6], // Post 3: Son môi, Review, Xu hướng
        [4, 1], [4, 5], [4, 8], // Post 4: Son môi, Chăm sóc da, Tips
        [5, 1], [5, 6], [5, 8], // Post 5: Son môi, Xu hướng, Tips
        [6, 1], [6, 8], [6, 6], // Post 6: Son môi, Tips, Xu hướng
        [7, 2], [7, 8], [7, 5], // Post 7: Kem nền, Tips, Chăm sóc da
        [8, 3], [8, 7], [8, 6], // Post 8: Mascara, Review, Xu hướng
        [9, 5], [9, 8], [9, 4], // Post 9: Chăm sóc da, Tips, Chăm sóc da
        [10, 4], [10, 8], [10, 2], // Post 10: Phấn mắt, Tips, Kem nền
        [11, 5], [11, 8], [11, 4], // Post 11: Chăm sóc da, Tips, Chăm sóc da
        [12, 5], [12, 8], [12, 4], // Post 12: Chăm sóc da, Tips, Chăm sóc da
        [13, 5], [13, 8], [13, 4], // Post 13: Chăm sóc da, Tips, Chăm sóc da
        [14, 5], [14, 8], [14, 4], // Post 14: Chăm sóc da, Tips, Chăm sóc da
        [15, 5], [15, 8], [15, 4], // Post 15: Chăm sóc da, Tips, Chăm sóc da
        [16, 5], [16, 8], [16, 4], // Post 16: Chăm sóc da, Tips, Chăm sóc da
        [17, 5], [17, 8], [17, 4], // Post 17: Chăm sóc da, Tips, Chăm sóc da
        [18, 5], [18, 8], [18, 4], // Post 18: Chăm sóc da, Tips, Chăm sóc da
        [19, 5], [19, 8], [19, 4], // Post 19: Chăm sóc da, Tips, Chăm sóc da
        [20, 5], [20, 8], [20, 4]  // Post 20: Chăm sóc da, Tips, Chăm sóc da
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO blog_post_tags (post_id, tag_id) VALUES (?, ?)");
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
        [6, 'Son môi lì vs bóng', 'So sánh ưu nhược điểm son môi lì và bóng', 'son môi lì, son môi bóng, so sánh'],
        [7, 'Kem nền cho da dầu', 'Hướng dẫn chọn kem nền phù hợp da dầu', 'kem nền, da dầu, makeup, skincare'],
        [8, 'Mascara waterproof', 'Top 5 mascara waterproof tốt nhất', 'mascara waterproof, review, makeup'],
        [9, 'Chăm sóc da mụn', 'Quy trình skincare cho da mụn', 'da mụn, skincare, chăm sóc da'],
        [10, 'Phấn mắt cơ bản', 'Hướng dẫn sử dụng phấn mắt cho người mới', 'phấn mắt, makeup, hướng dẫn'],
        [11, 'Tẩy trang đúng cách', 'Hướng dẫn tẩy trang hiệu quả', 'tẩy trang, skincare, chăm sóc da'],
        [12, 'Serum vitamin C', 'Lợi ích và cách sử dụng vitamin C', 'vitamin C, serum, chống lão hóa'],
        [13, 'Kem chống nắng', 'Hướng dẫn chọn và sử dụng kem chống nắng', 'kem chống nắng, SPF, bảo vệ da'],
        [14, 'Retinol chống lão hóa', 'Hướng dẫn sử dụng retinol an toàn', 'retinol, chống lão hóa, skincare'],
        [15, 'Hyaluronic Acid', 'Lợi ích dưỡng ẩm của hyaluronic acid', 'hyaluronic acid, dưỡng ẩm, skincare'],
        [16, 'Niacinamide', 'Giải pháp cho da dầu và lỗ chân lông', 'niacinamide, da dầu, lỗ chân lông'],
        [17, 'Peptide chống lão hóa', 'Thành phần peptide cho da trẻ trung', 'peptide, chống lão hóa, collagen'],
        [18, 'AHA BHA tẩy tế bào chết', 'Hướng dẫn sử dụng AHA BHA an toàn', 'AHA, BHA, tẩy tế bào chết, skincare'],
        [19, 'Ceramide', 'Khôi phục hàng rào bảo vệ da', 'ceramide, hàng rào da, skincare'],
        [20, 'Collagen cho da', 'Bổ sung collagen cho da trẻ trung', 'collagen, da trẻ trung, chống lão hóa']
    ];
    
    $stmt = $conn->prepare("INSERT IGNORE INTO blog_seo (post_id, meta_title, meta_description, meta_keywords) VALUES (?, ?, ?, ?)");
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