<?php
/**
 * AI Sentiment Analysis Demo Page
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

require_once 'config/auth-middleware.php';
require_once 'config/ai-sentiment-analysis.php';

// Check if user is logged in
if (!AuthMiddleware::isLoggedIn()) {
    header('Location: auth/dang-nhap.php');
    exit;
}

$user = AuthMiddleware::getCurrentUser();
$sentiment = new AISentimentAnalysis();

// Get sentiment summary
$summary = $sentiment->getSentimentSummary(30);
$keywordStats = $sentiment->getKeywordStats();
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>AI Sentiment Analysis Demo - Linh2Store</title>
    <link rel="stylesheet" href="assets/css/main.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="container">
            <div class="header-content">
                <div class="logo">
                    <a href="index.php">
                        <img src="assets/images/logo.png" alt="Linh2Store">
                    </a>
                </div>
                
                <nav class="nav">
                    <a href="index.php" class="nav-link">Trang chủ</a>
                    <a href="san-pham/" class="nav-link">Sản phẩm</a>
                    <a href="thuong-hieu/" class="nav-link">Thương hiệu</a>
                    <a href="blog/" class="nav-link">Blog</a>
                    <a href="lien-he/" class="nav-link">Liên hệ</a>
                </nav>
                
                <div class="user-actions">
                    <a href="user/" class="user-icon" title="Tài khoản">
                        <i class="fas fa-user"></i>
                    </a>
                    <a href="gio-hang.php" class="cart-icon" title="Giỏ hàng">
                        <i class="fas fa-shopping-cart"></i>
                        <span class="cart-count">0</span>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="main">
        <div class="container">
            <div class="sentiment-demo">
                <h1>😊 AI Sentiment Analysis Demo</h1>
                <p>Xin chào <strong><?php echo htmlspecialchars($user['full_name']); ?></strong>! Phân tích cảm xúc với AI:</p>
                
                <!-- Sentiment Summary -->
                <div class="sentiment-summary">
                    <h3>📊 Sentiment Summary (30 ngày)</h3>
                    <div class="summary-grid">
                        <?php foreach ($summary['stats'] as $stat): ?>
                        <div class="summary-card sentiment-<?php echo $stat['sentiment_label']; ?>">
                            <h4><?php echo ucfirst($stat['sentiment_label']); ?></h4>
                            <p class="count"><?php echo $stat['count']; ?> reviews</p>
                            <p class="score">Score: <?php echo number_format($stat['avg_score'], 2); ?></p>
                            <p class="confidence">Confidence: <?php echo number_format($stat['avg_confidence'], 2); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Sentiment Alerts -->
                <?php if (!empty($summary['alerts'])): ?>
                <div class="sentiment-alerts">
                    <h3>⚠️ Sentiment Alerts</h3>
                    <?php foreach ($summary['alerts'] as $alert): ?>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong><?php echo $alert['type']; ?>:</strong> <?php echo $alert['message']; ?>
                        <br>Count: <?php echo $alert['count']; ?>, Avg Score: <?php echo number_format($alert['avg_score'], 2); ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <!-- Sentiment Analysis Tool -->
                <div class="sentiment-tool">
                    <h3>🔍 Sentiment Analysis Tool</h3>
                    <div class="analysis-form">
                        <textarea id="sentimentText" placeholder="Nhập văn bản cần phân tích cảm xúc..." rows="4"></textarea>
                        <button onclick="analyzeSentiment()" class="btn btn-primary">
                            <i class="fas fa-brain"></i> Analyze Sentiment
                        </button>
                    </div>
                    
                    <div id="sentimentResult" class="sentiment-result" style="display: none;">
                        <h4>Kết quả phân tích:</h4>
                        <div id="sentimentDetails"></div>
                    </div>
                </div>
                
                <!-- Bulk Analysis -->
                <div class="bulk-analysis">
                    <h3>📝 Bulk Sentiment Analysis</h3>
                    <div class="bulk-form">
                        <textarea id="bulkTexts" placeholder="Nhập nhiều văn bản, mỗi văn bản trên một dòng..." rows="6"></textarea>
                        <button onclick="analyzeBulkSentiment()" class="btn btn-outline">
                            <i class="fas fa-list"></i> Analyze Bulk
                        </button>
                    </div>
                    
                    <div id="bulkResults" class="bulk-results" style="display: none;">
                        <h4>Kết quả phân tích hàng loạt:</h4>
                        <div id="bulkDetails"></div>
                    </div>
                </div>
                
                <!-- Keyword Statistics -->
                <div class="keyword-stats">
                    <h3>🔤 Keyword Statistics</h3>
                    <div class="keyword-grid">
                        <?php foreach (array_slice($keywordStats, 0, 10) as $keyword): ?>
                        <div class="keyword-card">
                            <h4><?php echo htmlspecialchars($keyword['keyword']); ?></h4>
                            <p>Type: <?php echo ucfirst($keyword['sentiment_type']); ?></p>
                            <p>Usage: <?php echo $keyword['usage_count']; ?> times</p>
                            <p>Impact: <?php echo number_format($keyword['avg_impact'], 2); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <!-- Sentiment Trends -->
                <div class="sentiment-trends">
                    <h3>📈 Sentiment Trends</h3>
                    <div class="trends-controls">
                        <button onclick="loadTrends(7)" class="btn btn-outline">7 ngày</button>
                        <button onclick="loadTrends(30)" class="btn btn-outline">30 ngày</button>
                        <button onclick="loadTrends(90)" class="btn btn-outline">90 ngày</button>
                    </div>
                    <div id="trendsChart" class="trends-chart">
                        <!-- Trends will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2024 Linh2Store. Tất cả quyền được bảo lưu.</p>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
    <script>
        // Analyze sentiment
        function analyzeSentiment() {
            const text = document.getElementById('sentimentText').value.trim();
            if (!text) {
                showAlert('Vui lòng nhập văn bản cần phân tích', 'error');
                return;
            }
            
            showLoading();
            fetch('api/ai-sentiment-analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'analyze',
                    text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displaySentimentResult(data.result);
                } else {
                    showAlert('Lỗi phân tích: ' + data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Lỗi kết nối: ' + error.message, 'error');
            });
        }
        
        // Display sentiment result
        function displaySentimentResult(result) {
            const resultDiv = document.getElementById('sentimentResult');
            const detailsDiv = document.getElementById('sentimentDetails');
            
            const sentimentClass = `sentiment-${result.sentiment_label}`;
            const sentimentIcon = getSentimentIcon(result.sentiment_label);
            
            detailsDiv.innerHTML = `
                <div class="sentiment-analysis-result ${sentimentClass}">
                    <div class="sentiment-header">
                        <i class="${sentimentIcon}"></i>
                        <h4>${result.sentiment_label.toUpperCase()}</h4>
                    </div>
                    <div class="sentiment-metrics">
                        <p><strong>Sentiment Score:</strong> ${result.sentiment_score.toFixed(3)}</p>
                        <p><strong>Confidence:</strong> ${(result.confidence_score * 100).toFixed(1)}%</p>
                    </div>
                    ${result.emotion_scores && Object.keys(result.emotion_scores).length > 0 ? `
                        <div class="emotion-scores">
                            <h5>Emotion Scores:</h5>
                            ${Object.entries(result.emotion_scores).map(([emotion, score]) => 
                                `<span class="emotion-tag">${emotion}: ${(score * 100).toFixed(1)}%</span>`
                            ).join('')}
                        </div>
                    ` : ''}
                    ${result.keywords && result.keywords.length > 0 ? `
                        <div class="keywords">
                            <h5>Keywords Found:</h5>
                            ${result.keywords.map(keyword => 
                                `<span class="keyword-tag keyword-${keyword.type}">${keyword.keyword} (${keyword.type})</span>`
                            ).join('')}
                        </div>
                    ` : ''}
                </div>
            `;
            
            resultDiv.style.display = 'block';
        }
        
        // Get sentiment icon
        function getSentimentIcon(sentiment) {
            switch (sentiment) {
                case 'positive': return 'fas fa-smile';
                case 'negative': return 'fas fa-frown';
                case 'neutral': return 'fas fa-meh';
                default: return 'fas fa-question';
            }
        }
        
        // Analyze bulk sentiment
        function analyzeBulkSentiment() {
            const texts = document.getElementById('bulkTexts').value.trim();
            if (!texts) {
                showAlert('Vui lòng nhập văn bản cần phân tích', 'error');
                return;
            }
            
            const textArray = texts.split('\n').filter(text => text.trim());
            if (textArray.length === 0) {
                showAlert('Vui lòng nhập ít nhất một văn bản', 'error');
                return;
            }
            
            showLoading();
            fetch('api/ai-sentiment-analysis.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: 'analyze_bulk',
                    texts: textArray
                })
            })
            .then(response => response.json())
            .then(data => {
                hideLoading();
                if (data.success) {
                    displayBulkResults(data.results);
                } else {
                    showAlert('Lỗi phân tích: ' + data.message, 'error');
                }
            })
            .catch(error => {
                hideLoading();
                showAlert('Lỗi kết nối: ' + error.message, 'error');
            });
        }
        
        // Display bulk results
        function displayBulkResults(results) {
            const resultDiv = document.getElementById('bulkResults');
            const detailsDiv = document.getElementById('bulkDetails');
            
            let html = '<div class="bulk-analysis-results">';
            results.forEach((result, index) => {
                if (result.error) {
                    html += `<div class="bulk-item error">Text ${index + 1}: ${result.error}</div>`;
                } else {
                    const sentimentClass = `sentiment-${result.sentiment_label}`;
                    html += `
                        <div class="bulk-item ${sentimentClass}">
                            <strong>Text ${index + 1}:</strong> ${result.sentiment_label.toUpperCase()} 
                            (Score: ${result.sentiment_score.toFixed(3)}, Confidence: ${(result.confidence_score * 100).toFixed(1)}%)
                        </div>
                    `;
                }
            });
            html += '</div>';
            
            detailsDiv.innerHTML = html;
            resultDiv.style.display = 'block';
        }
        
        // Load trends
        function loadTrends(days) {
            showLoading();
            fetch(`api/ai-sentiment-analysis.php?action=get_trends&timeframe=${days}`)
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        displayTrends(data.trends);
                    } else {
                        showAlert('Lỗi tải trends: ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    hideLoading();
                    showAlert('Lỗi kết nối: ' + error.message, 'error');
                });
        }
        
        // Display trends
        function displayTrends(trends) {
            const chartDiv = document.getElementById('trendsChart');
            
            // Group trends by date
            const trendsByDate = {};
            trends.forEach(trend => {
                if (!trendsByDate[trend.date]) {
                    trendsByDate[trend.date] = {};
                }
                trendsByDate[trend.date][trend.sentiment_label] = {
                    count: trend.count,
                    avg_score: trend.avg_score
                };
            });
            
            let html = '<div class="trends-table">';
            html += '<table><thead><tr><th>Date</th><th>Positive</th><th>Negative</th><th>Neutral</th></tr></thead><tbody>';
            
            Object.entries(trendsByDate).forEach(([date, sentiments]) => {
                html += `<tr>
                    <td>${date}</td>
                    <td>${sentiments.positive ? sentiments.positive.count : 0}</td>
                    <td>${sentiments.negative ? sentiments.negative.count : 0}</td>
                    <td>${sentiments.neutral ? sentiments.neutral.count : 0}</td>
                </tr>`;
            });
            
            html += '</tbody></table></div>';
            chartDiv.innerHTML = html;
        }
        
        // Load trends on page load
        document.addEventListener('DOMContentLoaded', function() {
            loadTrends(30);
        });
    </script>
    
    <style>
        .sentiment-demo {
            padding: 2rem 0;
        }
        
        .sentiment-demo h1 {
            color: #EC407A;
            margin-bottom: 1rem;
            text-align: center;
        }
        
        .sentiment-summary {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .summary-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .summary-card {
            padding: 1rem;
            border-radius: 8px;
            text-align: center;
            color: white;
        }
        
        .summary-card.sentiment-positive {
            background: #28a745;
        }
        
        .summary-card.sentiment-negative {
            background: #dc3545;
        }
        
        .summary-card.sentiment-neutral {
            background: #6c757d;
        }
        
        .summary-card h4 {
            margin: 0 0 0.5rem 0;
        }
        
        .summary-card p {
            margin: 0.25rem 0;
        }
        
        .sentiment-alerts {
            margin: 2rem 0;
        }
        
        .sentiment-tool, .bulk-analysis {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .analysis-form, .bulk-form {
            margin: 1rem 0;
        }
        
        .analysis-form textarea, .bulk-form textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid #ddd;
            border-radius: 4px;
            resize: vertical;
            margin-bottom: 1rem;
        }
        
        .sentiment-result, .bulk-results {
            margin-top: 1rem;
            padding: 1rem;
            background: white;
            border-radius: 8px;
            border: 1px solid #eee;
        }
        
        .sentiment-analysis-result {
            padding: 1rem;
            border-radius: 8px;
            margin: 1rem 0;
        }
        
        .sentiment-analysis-result.sentiment-positive {
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        
        .sentiment-analysis-result.sentiment-negative {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        
        .sentiment-analysis-result.sentiment-neutral {
            background: #e2e3e5;
            border: 1px solid #d6d8db;
        }
        
        .sentiment-header {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .sentiment-header i {
            font-size: 1.5rem;
        }
        
        .sentiment-metrics p {
            margin: 0.5rem 0;
        }
        
        .emotion-scores, .keywords {
            margin-top: 1rem;
        }
        
        .emotion-tag, .keyword-tag {
            display: inline-block;
            padding: 0.25rem 0.5rem;
            margin: 0.25rem;
            border-radius: 4px;
            font-size: 0.8rem;
        }
        
        .emotion-tag {
            background: #e9ecef;
            color: #495057;
        }
        
        .keyword-tag.keyword-positive {
            background: #d4edda;
            color: #155724;
        }
        
        .keyword-tag.keyword-negative {
            background: #f8d7da;
            color: #721c24;
        }
        
        .keyword-tag.keyword-neutral {
            background: #e2e3e5;
            color: #495057;
        }
        
        .bulk-analysis-results {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .bulk-item {
            padding: 0.5rem;
            margin: 0.25rem 0;
            border-radius: 4px;
            border-left: 4px solid #ccc;
        }
        
        .bulk-item.sentiment-positive {
            background: #d4edda;
            border-left-color: #28a745;
        }
        
        .bulk-item.sentiment-negative {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .bulk-item.sentiment-neutral {
            background: #e2e3e5;
            border-left-color: #6c757d;
        }
        
        .bulk-item.error {
            background: #f8d7da;
            border-left-color: #dc3545;
        }
        
        .keyword-stats {
            margin: 2rem 0;
        }
        
        .keyword-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .keyword-card {
            background: white;
            padding: 1rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .keyword-card h4 {
            margin: 0 0 0.5rem 0;
            color: #333;
        }
        
        .keyword-card p {
            margin: 0.25rem 0;
            font-size: 0.9rem;
            color: #666;
        }
        
        .sentiment-trends {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 2rem 0;
        }
        
        .trends-controls {
            margin: 1rem 0;
        }
        
        .trends-controls .btn {
            margin-right: 0.5rem;
        }
        
        .trends-table {
            margin-top: 1rem;
        }
        
        .trends-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .trends-table th, .trends-table td {
            padding: 0.5rem;
            text-align: center;
            border: 1px solid #ddd;
        }
        
        .trends-table th {
            background: #f8f9fa;
            font-weight: bold;
        }
    </style>
</body>
</html>
