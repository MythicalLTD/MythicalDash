-- Ultra-modern reset password email template with cutting-edge design
-- Compatible with all major email clients

-- Update reset_password template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset your ${app_name} password</title>
    <style>
        /* Advanced reset for email clients */
        body, table, td, p, a, li, blockquote {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
        }
        table, td {
            mso-table-lspace: 0pt;
            mso-table-rspace: 0pt;
        }
        img {
            -ms-interpolation-mode: bicubic;
            border: 0;
            height: auto;
            line-height: 100%;
            outline: none;
            text-decoration: none;
        }
        
        /* Ultra-modern styles */
        body {
            margin: 0 !important;
            padding: 0 !important;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            min-height: 100vh;
        }
        
        .email-wrapper {
            padding: 40px 20px;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .email-container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 20px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            position: relative;
        }
        
        .email-container::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 6px;
            background: linear-gradient(90deg, #ff6b6b 0%, #ee5a24 50%, #ff9a9e 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            padding: 60px 40px 40px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .header::before {
            content: "";
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
            animation: shimmer 3s ease-in-out infinite;
        }
        
        @keyframes shimmer {
            0%, 100% { transform: translateX(-100%) translateY(-100%) rotate(30deg); }
            50% { transform: translateX(100%) translateY(100%) rotate(30deg); }
        }
        
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 32px;
            font-weight: 700;
            letter-spacing: -0.5px;
            position: relative;
            z-index: 1;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .header .subtitle {
            color: rgba(255, 255, 255, 0.9);
            font-size: 16px;
            margin: 10px 0 0 0;
            font-weight: 400;
            position: relative;
            z-index: 1;
        }
        
        .content {
            padding: 50px 40px;
            position: relative;
        }
        
        .icon-section {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .icon-circle {
            display: inline-block;
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
        }
        
        .icon-circle::before {
            content: "🔑";
            font-size: 32px;
        }
        
        .content h2 {
            color: #2d3748;
            font-size: 28px;
            margin: 0 0 20px 0;
            font-weight: 700;
            text-align: center;
            letter-spacing: -0.5px;
        }
        
        .content p {
            color: #4a5568;
            font-size: 16px;
            margin: 0 0 20px 0;
            line-height: 1.7;
        }
        
        .alert-box {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            border: 1px solid #feb2b2;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #ff6b6b;
        }
        
        .alert-box p {
            margin: 0;
            color: #c53030;
            font-weight: 600;
            font-size: 16px;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(255, 107, 107, 0.3);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .button::before {
            content: "";
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .button:hover::before {
            left: 100%;
        }
        
        .button:hover {
            transform: translateY(-2px);
            box-shadow: 0 15px 35px rgba(255, 107, 107, 0.4);
        }
        
        .security-info {
            background: #f7fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #4299e1;
        }
        
        .security-info p {
            color: #2b6cb0;
            font-size: 14px;
            margin: 0;
            font-weight: 500;
        }
        
        .steps-list {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
        }
        
        .steps-list h3 {
            color: #2d3748;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .steps-list ul {
            margin: 0;
            padding-left: 20px;
            color: #4a5568;
        }
        
        .steps-list li {
            margin: 8px 0;
            line-height: 1.6;
        }
        
        .footer {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            padding: 40px;
            text-align: center;
            border-top: 1px solid #e2e8f0;
        }
        
        .footer p {
            color: #718096;
            font-size: 14px;
            margin: 0 0 10px 0;
        }
        
        .social-links {
            margin: 20px 0 0 0;
        }
        
        .social-links a {
            display: inline-block;
            margin: 0 10px;
            color: #ff6b6b;
            text-decoration: none;
            font-size: 14px;
        }
        
        /* Mobile responsive */
        @media only screen and (max-width: 600px) {
            .email-wrapper {
                padding: 20px 10px;
            }
            .email-container {
                border-radius: 15px;
            }
            .header {
                padding: 40px 20px 30px;
            }
            .header h1 {
                font-size: 24px;
            }
            .content {
                padding: 30px 20px;
            }
            .content h2 {
                font-size: 22px;
            }
            .button {
                padding: 15px 30px;
                font-size: 15px;
            }
            .footer {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="header">
                <h1>${app_name}</h1>
                <p class="subtitle">Password Reset Request</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>Reset Your Password</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>We received a request to reset your password for your ${app_name} account. Don''t worry - this happens to the best of us!</p>
                
                <div class="alert-box">
                    <p>🔐 Click the button below to create a new secure password</p>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/auth/reset-password?token=${token}" class="button">Reset Password</a>
                </div>
                
                <div class="security-info">
                    <p>⏰ This reset link expires in 1 hour for your security. If you didn''t request this reset, please ignore this email.</p>
                </div>
                
                <div class="steps-list">
                    <h3>What happens next?</h3>
                    <ul>
                        <li>Click the reset button above</li>
                        <li>Create a new strong password</li>
                        <li>Log in with your new credentials</li>
                        <li>Enjoy secure access to your account</li>
                    </ul>
                </div>
                
                <p><strong>Security Tips:</strong></p>
                <ul style="color: #4a5568; padding-left: 20px;">
                    <li>Use a unique password for ${app_name}</li>
                    <li>Include numbers, symbols, and mixed case letters</li>
                    <li>Consider using a password manager</li>
                    <li>Enable two-factor authentication if available</li>
                </ul>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This email was sent to ${email} because a password reset was requested.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/security">Security</a>
                    <a href="${app_url}/privacy">Privacy</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '🔑 Reset Your ${app_name} Password - Security Request'
WHERE `name` = 'reset_password';
