-- Ultra-modern welcome email template with cutting-edge design
-- Compatible with all major email clients

-- Update welcome template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ${app_name}</title>
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
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
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
            background: linear-gradient(90deg, #9c27b0 0%, #673ab7 50%, #ba68c8 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
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
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(156, 39, 176, 0.3);
        }
        
        .icon-circle::before {
            content: "🎉";
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
        
        .welcome-box {
            background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
            border: 1px solid #ce93d8;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #9c27b0;
            text-align: center;
        }
        
        .welcome-box p {
            margin: 0;
            color: #7b1fa2;
            font-weight: 700;
            font-size: 18px;
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 30px 0;
        }
        
        .feature-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        
        .feature-icon {
            font-size: 24px;
            margin-bottom: 10px;
        }
        
        .feature-card h3 {
            color: #2d3748;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .feature-card p {
            color: #4a5568;
            font-size: 14px;
            margin: 0;
            line-height: 1.5;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #9c27b0 0%, #673ab7 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(156, 39, 176, 0.3);
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
            box-shadow: 0 15px 35px rgba(156, 39, 176, 0.4);
        }
        
        .getting-started {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #2196f3;
        }
        
        .getting-started h3 {
            color: #1976d2;
            font-size: 18px;
            margin: 0 0 15px 0;
            font-weight: 600;
        }
        
        .getting-started ul {
            margin: 0;
            padding-left: 20px;
            color: #1976d2;
        }
        
        .getting-started li {
            margin: 8px 0;
            font-size: 14px;
            line-height: 1.5;
        }
        
        .support-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #f39c12;
            text-align: center;
        }
        
        .support-box h3 {
            color: #e67e22;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .support-box p {
            color: #e67e22;
            font-size: 14px;
            margin: 0;
            font-weight: 500;
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
            color: #9c27b0;
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
            .features-grid {
                grid-template-columns: 1fr;
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
                <p class="subtitle">Welcome to Our Community</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>Welcome to ${app_name}!</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>Welcome aboard! We''re absolutely thrilled to have you join the ${app_name} family. You''ve just taken the first step toward an incredible journey with us.</p>
                
                <div class="welcome-box">
                    <p>🎊 You''re now part of something amazing!</p>
                </div>
                
                <div class="features-grid">
                    <div class="feature-card">
                        <div class="feature-icon">🚀</div>
                        <h3>Powerful Features</h3>
                        <p>Access cutting-edge tools and features designed to help you succeed</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">🛡️</div>
                        <h3>Secure Platform</h3>
                        <p>Your data and privacy are protected with enterprise-grade security</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">📱</div>
                        <h3>Mobile Ready</h3>
                        <p>Access your account from anywhere with our responsive design</p>
                    </div>
                    <div class="feature-card">
                        <div class="feature-icon">💬</div>
                        <h3>24/7 Support</h3>
                        <p>Our dedicated support team is always here to help you</p>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/dashboard" class="button">Explore Your Dashboard</a>
                </div>
                
                <div class="getting-started">
                    <h3>🚀 Let''s Get You Started:</h3>
                    <ul>
                        <li>Complete your profile setup to personalize your experience</li>
                        <li>Explore the dashboard and familiarize yourself with the interface</li>
                        <li>Browse our services and discover what we have to offer</li>
                        <li>Connect with our community and join the conversation</li>
                        <li>Set up notifications to stay updated on important updates</li>
                    </ul>
                </div>
                
                <div class="support-box">
                    <h3>💬 Need Help?</h3>
                    <p>Our support team is here to help you get the most out of ${app_name}. Don''t hesitate to reach out!</p>
                </div>
                
                <p>We''re committed to providing you with an exceptional experience. If you have any questions, suggestions, or just want to say hello, we''d love to hear from you.</p>
                
                <p><strong>Welcome to the future with ${app_name}!</strong></p>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This welcome message was sent to ${email} because you joined our community.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/community">Community</a>
                    <a href="${app_url}/blog">Blog</a>
                    <a href="${app_url}/docs">Documentation</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '🎉 Welcome to ${app_name} - Let''s Get Started!'
WHERE `name` = 'welcome';
