<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đang bảo trì - Linh2Store</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="maintenance-page">
        <div class="maintenance-content">
            <div class="maintenance-icon">
                <i class="fas fa-tools"></i>
            </div>
            <h1>Đang bảo trì hệ thống</h1>
            <p>Chúng tôi đang cập nhật và cải thiện website để mang đến trải nghiệm tốt nhất cho bạn.</p>
            <p>Vui lòng quay lại sau ít phút.</p>
            
            <div class="maintenance-info">
                <div class="info-item">
                    <i class="fas fa-clock"></i>
                    <span>Thời gian dự kiến: 30 phút</span>
                </div>
                <div class="info-item">
                    <i class="fas fa-envelope"></i>
                    <span>Liên hệ: info@linh2store.com</span>
                </div>
            </div>
            
            <div class="maintenance-actions">
                <a href="index.php" class="btn btn-primary">
                    <i class="fas fa-home"></i>
                    Về trang chủ
                </a>
                <button onclick="location.reload()" class="btn btn-outline">
                    <i class="fas fa-refresh"></i>
                    Tải lại trang
                </button>
            </div>
        </div>
    </div>
    
    <style>
        .maintenance-page {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--accent-color) 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: var(--spacing-xl);
        }
        
        .maintenance-content {
            background: var(--white);
            border-radius: var(--radius-xl);
            padding: var(--spacing-3xl);
            text-align: center;
            box-shadow: var(--shadow-xl);
            max-width: 500px;
            width: 100%;
        }
        
        .maintenance-icon {
            font-size: var(--font-size-3xl);
            color: var(--cta-color);
            margin-bottom: var(--spacing-xl);
        }
        
        .maintenance-content h1 {
            color: var(--text-dark);
            margin-bottom: var(--spacing-lg);
        }
        
        .maintenance-content p {
            color: var(--text-light);
            margin-bottom: var(--spacing-md);
        }
        
        .maintenance-info {
            display: flex;
            flex-direction: column;
            gap: var(--spacing-md);
            margin: var(--spacing-xl) 0;
            padding: var(--spacing-lg);
            background: var(--primary-color);
            border-radius: var(--radius-lg);
        }
        
        .info-item {
            display: flex;
            align-items: center;
            gap: var(--spacing-sm);
            color: var(--text-dark);
        }
        
        .info-item i {
            color: var(--cta-color);
            width: 20px;
        }
        
        .maintenance-actions {
            display: flex;
            gap: var(--spacing-md);
            justify-content: center;
            margin-top: var(--spacing-xl);
        }
        
        @media (max-width: 480px) {
            .maintenance-content {
                padding: var(--spacing-xl);
            }
            
            .maintenance-actions {
                flex-direction: column;
            }
        }
    </style>
</body>
</html>
