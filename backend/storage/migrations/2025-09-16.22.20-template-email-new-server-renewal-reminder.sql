-- Ultra-modern server renewal reminder email template with cutting-edge design
-- Compatible with all major email clients

-- Update server_renewal_reminder template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Renewal Reminder - ${app_name}</title>
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
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
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
            background: linear-gradient(90deg, #ff9800 0%, #f57c00 50%, #ffb74d 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
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
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(255, 152, 0, 0.3);
        }
        
        .icon-circle::before {
            content: "⏰";
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
        
        .urgent-box {
            background: linear-gradient(135deg, #fff3e0 0%, #ffe0b2 100%);
            border: 1px solid #ffcc02;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #ff9800;
            text-align: center;
        }
        
        .urgent-box p {
            margin: 0;
            color: #e65100;
            font-weight: 700;
            font-size: 18px;
        }
        
        .server-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid #ff9800;
            position: relative;
            overflow: hidden;
        }
        
        .server-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(255, 152, 0, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .server-card h3 {
            color: #2d3748;
            font-size: 20px;
            margin: 0 0 20px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .server-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin: 20px 0;
            position: relative;
            z-index: 1;
        }
        
        .detail-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            border: 1px solid #e2e8f0;
        }
        
        .detail-label {
            color: #718096;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin: 0 0 5px 0;
        }
        
        .detail-value {
            color: #2d3748;
            font-size: 16px;
            font-weight: 700;
            margin: 0;
        }
        
        .expiration-highlight {
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: white !important;
            border: none !important;
        }
        
        .expiration-highlight .detail-value {
            color: white !important;
            font-size: 18px;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(255, 152, 0, 0.3);
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
            box-shadow: 0 15px 35px rgba(255, 152, 0, 0.4);
        }
        
        .warning-info {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #f39c12;
        }
        
        .warning-info h3 {
            color: #e67e22;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .warning-info ul {
            margin: 0;
            padding-left: 20px;
            color: #e67e22;
        }
        
        .warning-info li {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .benefits-box {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #4caf50;
        }
        
        .benefits-box h3 {
            color: #2e7d32;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .benefits-box ul {
            margin: 0;
            padding-left: 20px;
            color: #2e7d32;
        }
        
        .benefits-box li {
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
            color: #ff9800;
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
            .server-details {
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
                <p class="subtitle">Server Renewal Reminder</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>Server Renewal Required</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>Your server is approaching its expiration date and requires renewal to prevent data loss. We''re sending this reminder to ensure uninterrupted service.</p>
                
                <div class="urgent-box">
                    <p>⏰ Action Required: Your server expires in 7 days!</p>
                </div>
                
                <div class="server-card">
                    <h3>Server Information</h3>
                    <div class="server-details">
                        <div class="detail-item">
                            <p class="detail-label">Server ID</p>
                            <p class="detail-value">${pterodactyl_id}</p>
                        </div>
                        <div class="detail-item expiration-highlight">
                            <p class="detail-label">Expiration Date</p>
                            <p class="detail-value">${expiration_date}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Renewal Amount</p>
                            <p class="detail-value">$${renewal_amount}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Status</p>
                            <p class="detail-value">Active (Expiring Soon)</p>
                        </div>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/servers/${pterodactyl_id}/renew" class="button">Renew Server Now</a>
                </div>
                
                <div class="warning-info">
                    <h3>⚠️ Important Information:</h3>
                    <ul>
                        <li>Your server will be automatically deleted if not renewed</li>
                        <li>All data will be permanently lost after deletion</li>
                        <li>No data recovery is possible after the expiration date</li>
                        <li>Backup your data before the expiration if needed</li>
                    </ul>
                </div>
                
                <div class="benefits-box">
                    <h3>✅ Benefits of Renewing:</h3>
                    <ul>
                        <li>Maintain uninterrupted service</li>
                        <li>Preserve all your data and configurations</li>
                        <li>Keep your server running smoothly</li>
                        <li>Avoid setup time and configuration loss</li>
                    </ul>
                </div>
                
                <p>If you have any questions about the renewal process or need assistance, please don''t hesitate to contact our support team. We''re here to help!</p>
                
                <p><strong>Don''t wait until the last minute - renew now to ensure continuous service!</strong></p>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This renewal reminder was sent to ${email} to protect your server.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/servers">Servers</a>
                    <a href="${app_url}/billing">Billing</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '⏰ Server Renewal Required - Expires in 7 Days'
WHERE `name` = 'server_renewal_reminder';
