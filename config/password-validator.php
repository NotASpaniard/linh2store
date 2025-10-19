<?php
/**
 * Password Strength Validator
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 */

class PasswordValidator {
    
    /**
     * Kiểm tra độ mạnh mật khẩu theo thang điểm 5
     * Quy định:
     * - 9 ký tự trở lên: +1 điểm
     * - Có chữ thường: +1 điểm  
     * - Có chữ in hoa: +1 điểm
     * - Có ký tự đặc biệt: +1 điểm
     * - Có số: +1 điểm
     */
    public static function checkStrength($password) {
        $score = 0;
        $feedback = [];
        
        // Kiểm tra độ dài (9 ký tự trở lên)
        if (strlen($password) >= 9) {
            $score++;
        } else {
            $feedback[] = 'Mật khẩu phải có ít nhất 9 ký tự';
        }
        
        // Kiểm tra chữ thường
        if (preg_match('/[a-z]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Mật khẩu phải có ít nhất 1 chữ thường (a-z)';
        }
        
        // Kiểm tra chữ in hoa
        if (preg_match('/[A-Z]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Mật khẩu phải có ít nhất 1 chữ in hoa (A-Z)';
        }
        
        // Kiểm tra ký tự đặc biệt
        if (preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?~`]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Mật khẩu phải có ít nhất 1 ký tự đặc biệt (!@#$%^&*...)';
        }
        
        // Kiểm tra số
        if (preg_match('/[0-9]/', $password)) {
            $score++;
        } else {
            $feedback[] = 'Mật khẩu phải có ít nhất 1 số (0-9)';
        }
        
        // Xác định độ mạnh
        $strength = self::getStrengthLevel($score);
        
        return [
            'score' => $score,
            'max_score' => 5,
            'strength' => $strength,
            'feedback' => $feedback,
            'is_valid' => $score >= 3 // Tối thiểu 3/5 điểm
        ];
    }
    
    /**
     * Lấy mức độ mạnh dựa trên điểm số
     */
    private static function getStrengthLevel($score) {
        switch ($score) {
            case 0:
            case 1:
                return [
                    'level' => 'very_weak',
                    'label' => 'Rất yếu',
                    'color' => '#f44336',
                    'width' => '20%'
                ];
            case 2:
                return [
                    'level' => 'weak',
                    'label' => 'Yếu',
                    'color' => '#ff9800',
                    'width' => '40%'
                ];
            case 3:
                return [
                    'level' => 'fair',
                    'label' => 'Trung bình',
                    'color' => '#ffeb3b',
                    'width' => '60%'
                ];
            case 4:
                return [
                    'level' => 'good',
                    'label' => 'Tốt',
                    'color' => '#4caf50',
                    'width' => '80%'
                ];
            case 5:
                return [
                    'level' => 'strong',
                    'label' => 'Rất mạnh',
                    'color' => '#2196f3',
                    'width' => '100%'
                ];
            default:
                return [
                    'level' => 'unknown',
                    'label' => 'Không xác định',
                    'color' => '#9e9e9e',
                    'width' => '0%'
                ];
        }
    }
    
    /**
     * Kiểm tra mật khẩu có đủ mạnh không
     */
    public static function isValid($password) {
        $result = self::checkStrength($password);
        return $result['is_valid'];
    }
    
    /**
     * Lấy feedback cho mật khẩu
     */
    public static function getFeedback($password) {
        $result = self::checkStrength($password);
        return $result['feedback'];
    }
    
    /**
     * Tạo HTML cho password strength indicator
     */
    public static function getStrengthIndicator($password) {
        $result = self::checkStrength($password);
        
        $html = '<div class="password-strength-indicator">';
        $html .= '<div class="strength-bar">';
        $html .= '<div class="strength-fill" style="width: ' . $result['strength']['width'] . '; background-color: ' . $result['strength']['color'] . '"></div>';
        $html .= '</div>';
        $html .= '<div class="strength-label" style="color: ' . $result['strength']['color'] . '">';
        $html .= 'Độ mạnh: ' . $result['strength']['label'] . ' (' . $result['score'] . '/5)';
        $html .= '</div>';
        
        if (!empty($result['feedback'])) {
            $html .= '<div class="strength-feedback">';
            foreach ($result['feedback'] as $item) {
                $html .= '<div class="feedback-item">• ' . htmlspecialchars($item) . '</div>';
            }
            $html .= '</div>';
        }
        
        $html .= '</div>';
        
        return $html;
    }
    
    /**
     * Validate password với các rule bổ sung
     */
    public static function validatePassword($password, $confirm_password = null) {
        $errors = [];
        
        // Kiểm tra độ mạnh
        $strength_result = self::checkStrength($password);
        if (!$strength_result['is_valid']) {
            $errors[] = 'Mật khẩu không đủ mạnh. ' . implode(', ', $strength_result['feedback']);
        }
        
        // Kiểm tra xác nhận mật khẩu
        if ($confirm_password !== null && $password !== $confirm_password) {
            $errors[] = 'Mật khẩu xác nhận không khớp';
        }
        
        // Kiểm tra độ dài tối đa
        if (strlen($password) > 128) {
            $errors[] = 'Mật khẩu không được vượt quá 128 ký tự';
        }
        
        // Kiểm tra mật khẩu phổ biến
        $common_passwords = [
            'password', 'JQKA56', 'JQKA56789', 'qwerty', 'abc123',
            'password123', 'admin', 'letmein', 'welcome', 'monkey'
        ];
        
        if (in_array(strtolower($password), $common_passwords)) {
            $errors[] = 'Mật khẩu này quá phổ biến, vui lòng chọn mật khẩu khác';
        }
        
        return [
            'is_valid' => empty($errors),
            'errors' => $errors,
            'strength' => $strength_result
        ];
    }
}
?>
