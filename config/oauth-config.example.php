<?php
/**
 * OAuth Configuration Template
 * Linh2Store - Website bán son môi & mỹ phẩm cao cấp
 * 
 * ⚠️ COPY THIS FILE TO oauth-config.php AND FILL IN YOUR ACTUAL CREDENTIALS
 * 
 * INSTRUCTIONS:
 * 1. Copy this file: cp oauth-config.example.php oauth-config.php
 * 2. Replace YOUR_*_HERE with your actual OAuth credentials
 * 3. Never commit oauth-config.php to git (it's in .gitignore)
 */

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', 'YOUR_GOOGLE_CLIENT_ID_HERE');
define('GOOGLE_CLIENT_SECRET', 'YOUR_GOOGLE_CLIENT_SECRET_HERE');
define('GOOGLE_REDIRECT_URI', 'http://localhost/linh2store/auth/oauth-callback.php');

// Facebook OAuth Configuration  
define('FACEBOOK_APP_ID', 'YOUR_FACEBOOK_APP_ID_HERE');
define('FACEBOOK_APP_SECRET', 'YOUR_FACEBOOK_APP_SECRET_HERE');
define('FACEBOOK_REDIRECT_URI', 'http://localhost/linh2store/auth/oauth-callback.php');

/**
 * SETUP INSTRUCTIONS:
 * 
 * GOOGLE OAUTH:
 * 1. Go to https://console.cloud.google.com/
 * 2. Create a new project or select existing
 * 3. Enable Google+ API
 * 4. Create OAuth 2.0 Client ID (Web application)
 * 5. Add redirect URI: http://localhost/linh2store/auth/oauth-callback.php
 * 6. Copy Client ID and Client Secret here
 * 
 * FACEBOOK OAUTH:
 * 1. Go to https://developers.facebook.com/
 * 2. Create a new app
 * 3. Add Facebook Login product
 * 4. Add redirect URI: http://localhost/linh2store/auth/oauth-callback.php
 * 5. Copy App ID and App Secret here
 */
?>
