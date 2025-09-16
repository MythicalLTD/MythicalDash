-- Ultra-modern server deleted email template with cutting-edge design
-- Compatible with all major email clients

-- Update server_deleted template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Server Deleted - ${app_name}</title>
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
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
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
            background: linear-gradient(90deg, #9e9e9e 0%, #616161 50%, #bdbdbd 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
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
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(158, 158, 158, 0.3);
        }
        
        .icon-circle::before {
            content: "🗑️";
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
        
        .deletion-box {
            background: linear-gradient(135deg, #f5f5f5 0%, #eeeeee 100%);
            border: 1px solid #bdbdbd;
            border-radius: 12px;
            padding: 25px;
            margin: 30px 0;
            border-left: 4px solid #9e9e9e;
            text-align: center;
        }
        
        .deletion-box p {
            margin: 0;
            color: #424242;
            font-weight: 700;
            font-size: 18px;
        }
        
        .server-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid #9e9e9e;
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
            background: linear-gradient(135deg, rgba(158, 158, 158, 0.1) 0%, transparent 100%);
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
        
        .deletion-highlight {
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
            color: white !important;
            border: none !important;
        }
        
        .deletion-highlight .detail-value {
            color: white !important;
            font-size: 18px;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #9e9e9e 0%, #616161 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(158, 158, 158, 0.3);
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
            box-shadow: 0 15px 35px rgba(158, 158, 158, 0.4);
        }
        
        .data-loss-warning {
            background: #ffebee;
            border: 1px solid #ffcdd2;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #f44336;
        }
        
        .data-loss-warning h3 {
            color: #c62828;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .data-loss-warning ul {
            margin: 0;
            padding-left: 20px;
            color: #c62828;
        }
        
        .data-loss-warning li {
            margin: 5px 0;
            font-size: 14px;
        }
        
        .next-steps {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #4caf50;
        }
        
        .next-steps h3 {
            color: #2e7d32;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .next-steps ul {
            margin: 0;
            padding-left: 20px;
            color: #2e7d32;
        }
        
        .next-steps li {
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
            color: #9e9e9e;
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
                <p class="subtitle">Server Deletion Notice</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>Server Deleted</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>We regret to inform you that your server has been permanently deleted due to non-renewal. This action is irreversible and all associated data has been removed.</p>
                
                <div class="deletion-box">
                    <p>🗑️ Your server has been permanently deleted</p>
                </div>
                
                <div class="server-card">
                    <h3>Deletion Details</h3>
                    <div class="server-details">
                        <div class="detail-item">
                            <p class="detail-label">Server ID</p>
                            <p class="detail-value">${pterodactyl_id}</p>
                        </div>
                        <div class="detail-item deletion-highlight">
                            <p class="detail-label">Deletion Date</p>
                            <p class="detail-value">${deletion_date}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Reason</p>
                            <p class="detail-value">Non-renewal</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Status</p>
                            <p class="detail-value">Permanently Deleted</p>
                        </div>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/servers" class="button">View Available Servers</a>
                </div>
                
                <div class="data-loss-warning">
                    <h3>⚠️ Data Loss Notice:</h3>
                    <ul>
                        <li>All server data has been permanently removed</li>
                        <li>No data recovery is possible</li>
                        <li>All configurations and files are lost</li>
                        <li>This action cannot be undone</li>
                    </ul>
                </div>
                
                <div class="next-steps">
                    <h3>🚀 What''s Next?</h3>
                    <ul>
                        <li>Create a new server from your dashboard</li>
                        <li>Set up automatic renewals to prevent future deletions</li>
                        <li>Consider setting up regular backups for important data</li>
                        <li>Contact support if you need assistance with setup</li>
                    </ul>
                </div>
                
                <p>We understand this may be disappointing, but we''re here to help you get back up and running with a new server. Our support team is available to assist you with any questions or setup needs.</p>
                
                <p><strong>Ready to start fresh? Create a new server today!</strong></p>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This deletion notice was sent to ${email} for your records.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/servers">Servers</a>
                    <a href="${app_url}/create">Create Server</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '🗑️ Server Deleted - ${app_name}'
WHERE `name` = 'server_deleted';
