-- Ultra-modern product suspended email template with cutting-edge design
-- Compatible with all major email clients

-- Update product_suspended template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ${app_name} product was suspended</title>
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
            background: linear-gradient(135deg, #f44336 0%, #c62828 100%);
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
            background: linear-gradient(90deg, #f44336 0%, #c62828 50%, #ef5350 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #f44336 0%, #c62828 100%);
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
            background: linear-gradient(135deg, #f44336 0%, #c62828 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(244, 67, 54, 0.3);
        }
        
        .icon-circle::before {
            content: "⚠️";
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
            background: linear-gradient(135deg, #ffebee 0%, #ffcdd2 100%);
            border: 1px solid #ef9a9a;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #f44336;
            text-align: center;
        }
        
        .alert-box p {
            margin: 0;
            color: #c62828;
            font-weight: 700;
            font-size: 18px;
        }
        
        .suspension-details {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid #f44336;
            position: relative;
            overflow: hidden;
        }
        
        .suspension-details::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(244, 67, 54, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .suspension-details h3 {
            color: #2d3748;
            font-size: 20px;
            margin: 0 0 20px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .suspension-details p {
            margin: 10px 0;
            color: #4a5568;
            font-weight: 500;
            position: relative;
            z-index: 1;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #f44336 0%, #c62828 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(244, 67, 54, 0.3);
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
            box-shadow: 0 15px 35px rgba(244, 67, 54, 0.4);
        }
        
        .support-info {
            background: #e3f2fd;
            border: 1px solid #bbdefb;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #2196f3;
        }
        
        .support-info h3 {
            color: #1976d2;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .support-info ul {
            margin: 0;
            padding-left: 20px;
            color: #1976d2;
        }
        
        .support-info li {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .reasons-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #f39c12;
        }
        
        .reasons-box h3 {
            color: #e67e22;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .reasons-box ul {
            margin: 0;
            padding-left: 20px;
            color: #e67e22;
        }
        
        .reasons-box li {
            margin: 5px 0;
            font-size: 14px;
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
            color: #f44336;
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
                <p class="subtitle">Product Suspension Notice</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>Product Suspended</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>We regret to inform you that your ${app_name} product has been suspended. We understand this may be concerning, and we''re here to help you resolve this issue.</p>
                
                <div class="alert-box">
                    <p>⚠️ Your product access has been temporarily suspended</p>
                </div>
                
                <div class="suspension-details">
                    <h3>Suspension Details</h3>
                    <p><strong>Product:</strong> ${product_name}</p>
                    <p><strong>Suspension Date:</strong> ${suspension_date}</p>
                    <p><strong>Reason:</strong> ${suspension_reason}</p>
                    <p><strong>Status:</strong> Temporarily Suspended</p>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/support" class="button">Contact Support</a>
                </div>
                
                <div class="reasons-box">
                    <h3>🔍 Common Suspension Reasons:</h3>
                    <ul>
                        <li>Payment issues or overdue invoices</li>
                        <li>Terms of service violations</li>
                        <li>Security concerns or suspicious activity</li>
                        <li>Resource usage exceeding limits</li>
                        <li>Administrative review required</li>
                    </ul>
                </div>
                
                <div class="support-info">
                    <h3>🛠️ How to Resolve:</h3>
                    <ul>
                        <li>Contact our support team immediately</li>
                        <li>Provide any requested information or documentation</li>
                        <li>Resolve any outstanding payment issues</li>
                        <li>Review and comply with our terms of service</li>
                        <li>Wait for our team to review and restore access</li>
                    </ul>
                </div>
                
                <p>We''re committed to resolving this issue as quickly as possible. Our support team is available 24/7 to assist you and answer any questions you may have.</p>
                
                <p><strong>We appreciate your patience and look forward to restoring your service soon.</strong></p>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This suspension notice was sent to ${email} for your records.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/terms">Terms</a>
                    <a href="${app_url}/privacy">Privacy</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '⚠️ Product Suspended - ${app_name}'
WHERE `name` = 'product_suspended';
