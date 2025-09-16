-- Ultra-modern new invoice email template with cutting-edge design
-- Compatible with all major email clients

-- Update new_invoice template with ultra-modern design
UPDATE `mythicaldash_mail_templates` SET 
    `body` = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Invoice from ${app_name}</title>
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
            background: linear-gradient(135deg, #26a69a 0%, #00695c 100%);
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
            background: linear-gradient(90deg, #26a69a 0%, #00695c 50%, #4db6ac 100%);
        }
        
        .header {
            background: linear-gradient(135deg, #26a69a 0%, #00695c 100%);
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
            background: linear-gradient(135deg, #26a69a 0%, #00695c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            box-shadow: 0 10px 25px rgba(38, 166, 154, 0.3);
        }
        
        .icon-circle::before {
            content: "📄";
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
        
        .invoice-card {
            background: linear-gradient(135deg, #f7fafc 0%, #edf2f7 100%);
            border: 1px solid #e2e8f0;
            border-radius: 16px;
            padding: 30px;
            margin: 30px 0;
            border-left: 4px solid #26a69a;
            position: relative;
            overflow: hidden;
        }
        
        .invoice-card::before {
            content: "";
            position: absolute;
            top: 0;
            right: 0;
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, rgba(38, 166, 154, 0.1) 0%, transparent 100%);
            border-radius: 50%;
            transform: translate(30px, -30px);
        }
        
        .invoice-card h3 {
            color: #2d3748;
            font-size: 20px;
            margin: 0 0 20px 0;
            font-weight: 700;
            position: relative;
            z-index: 1;
        }
        
        .invoice-details {
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
        
        .amount-highlight {
            background: linear-gradient(135deg, #26a69a 0%, #00695c 100%);
            color: white !important;
            border: none !important;
        }
        
        .amount-highlight .detail-value {
            color: white !important;
            font-size: 20px;
        }
        
        .button-container {
            text-align: center;
            margin: 40px 0;
        }
        
        .button {
            display: inline-block;
            background: linear-gradient(135deg, #26a69a 0%, #00695c 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 18px 40px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 16px;
            text-align: center;
            box-shadow: 0 10px 25px rgba(38, 166, 154, 0.3);
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
            box-shadow: 0 15px 35px rgba(38, 166, 154, 0.4);
        }
        
        .payment-info {
            background: #e8f5e8;
            border: 1px solid #c8e6c9;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            border-left: 4px solid #4caf50;
        }
        
        .payment-info h3 {
            color: #2e7d32;
            font-size: 16px;
            margin: 0 0 10px 0;
            font-weight: 600;
        }
        
        .payment-info ul {
            margin: 0;
            padding-left: 20px;
            color: #2e7d32;
        }
        
        .payment-info li {
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
            color: #26a69a;
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
            .invoice-details {
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
                <p class="subtitle">New Invoice Available</p>
            </div>
            <div class="content">
                <div class="icon-section">
                    <div class="icon-circle"></div>
                </div>
                <h2>New Invoice Available</h2>
                <p>Hello <strong>${first_name} ${last_name}</strong>,</p>
                <p>You have a new invoice from ${app_name}. Please review the details below and complete your payment when convenient.</p>
                
                <div class="invoice-card">
                    <h3>Invoice Details</h3>
                    <div class="invoice-details">
                        <div class="detail-item">
                            <p class="detail-label">Invoice Number</p>
                            <p class="detail-value">${invoice_number}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Issue Date</p>
                            <p class="detail-value">${issue_date}</p>
                        </div>
                        <div class="detail-item">
                            <p class="detail-label">Due Date</p>
                            <p class="detail-value">${due_date}</p>
                        </div>
                        <div class="detail-item amount-highlight">
                            <p class="detail-label">Total Amount</p>
                            <p class="detail-value">${invoice_amount}</p>
                        </div>
                    </div>
                </div>
                
                <div class="button-container">
                    <a href="${app_url}/invoices/${invoice_id}/view" class="button">View & Pay Invoice</a>
                </div>
                
                <div class="payment-info">
                    <h3>💳 Payment Options:</h3>
                    <ul>
                        <li>Credit/Debit Card</li>
                        <li>Bank Transfer</li>
                        <li>PayPal</li>
                        <li>Cryptocurrency (where available)</li>
                    </ul>
                </div>
                
                <p>If you have any questions about this invoice or need assistance with payment, please don''t hesitate to contact our support team. We''re here to help!</p>
                
                <p><strong>Thank you for your business!</strong></p>
            </div>
            <div class="footer">
                <p>&copy; 2025 ${app_name}. All rights reserved.</p>
                <p>This invoice was sent to ${email} for your records.</p>
                <div class="social-links">
                    <a href="${app_url}/support">Support</a>
                    <a href="${app_url}/billing">Billing</a>
                    <a href="${app_url}/privacy">Privacy</a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>',
    `subject` = '📄 New Invoice #${invoice_number} - ${app_name}'
WHERE `name` = 'new_invoice';
