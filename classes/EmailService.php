<?php
/**
 * Email Service for Jack Nine Tables
 * Supports both SMTP (Mailtrap) and SendGrid HTTP API
 */

class EmailService
{
    private $driver;
    private $apiKey;
    private $fromEmail;
    private $fromName;

    public function __construct()
    {
        require_once __DIR__ . '/../config/email.php';

        $this->driver = defined('MAIL_DRIVER') ? MAIL_DRIVER : 'sendgrid';
        $this->apiKey = SENDGRID_API_KEY;
        $this->fromEmail = MAIL_FROM_ADDRESS;
        $this->fromName = MAIL_FROM_NAME;
    }

    /**
     * Send email - routes to appropriate driver
     */
    public function send($to, $subject, $htmlContent, $options = [])
    {
        if ($this->driver === 'smtp') {
            return $this->sendViaSMTP($to, $subject, $htmlContent, $options);
        }
        return $this->sendViaSendGrid($to, $subject, $htmlContent, $options);
    }

    /**
     * Send email via SMTP (Mailtrap)
     */
    private function sendViaSMTP($to, $subject, $htmlContent, $options = [])
    {
        $host = SMTP_HOST;
        $port = SMTP_PORT;
        $username = SMTP_USERNAME;
        $password = SMTP_PASSWORD;

        // Connect to SMTP server
        $socket = @fsockopen($host, $port, $errno, $errstr, 30);
        if (!$socket) {
            error_log("SMTP connection failed: $errstr ($errno)");
            return false;
        }

        // Set socket timeout
        stream_set_timeout($socket, 30);

        // Helper function to read response (handles multi-line)
        $readResponse = function() use ($socket) {
            $response = '';
            while ($line = fgets($socket, 512)) {
                $response .= $line;
                // Check if this is the last line (space after code, not dash)
                if (isset($line[3]) && $line[3] === ' ') break;
                // Also break if line starts with a code and has no continuation
                if (preg_match('/^\d{3} /', $line)) break;
            }
            return $response;
        };

        // Helper function to send command and get response
        $sendCommand = function($command) use ($socket, $readResponse) {
            fwrite($socket, $command . "\r\n");
            return $readResponse();
        };

        // Get greeting
        $greeting = $readResponse();
        if (strpos($greeting, '220') !== 0) {
            error_log("SMTP greeting failed: $greeting");
            fclose($socket);
            return false;
        }

        // EHLO
        $ehloResponse = $sendCommand("EHLO localhost");
        if (strpos($ehloResponse, '250') === false) {
            error_log("SMTP EHLO failed: $ehloResponse");
            fclose($socket);
            return false;
        }

        // AUTH LOGIN - send command and wait for 334 challenge
        $authResponse = $sendCommand("AUTH LOGIN");
        if (strpos($authResponse, '334') !== 0) {
            error_log("SMTP AUTH LOGIN failed: $authResponse");
            fclose($socket);
            return false;
        }

        // Send username (base64) and wait for password challenge
        $userResponse = $sendCommand(base64_encode($username));
        if (strpos($userResponse, '334') !== 0) {
            error_log("SMTP username failed: $userResponse");
            fclose($socket);
            return false;
        }

        // Send password (base64) and wait for auth success
        $passResponse = $sendCommand(base64_encode($password));
        if (strpos($passResponse, '235') !== 0) {
            error_log("SMTP password failed: $passResponse");
            fclose($socket);
            return false;
        }

        // MAIL FROM
        $fromResponse = $sendCommand("MAIL FROM:<{$this->fromEmail}>");
        if (strpos($fromResponse, '250') !== 0) {
            error_log("SMTP MAIL FROM failed: $fromResponse");
            fclose($socket);
            return false;
        }

        // RCPT TO
        $rcptResponse = $sendCommand("RCPT TO:<{$to}>");
        if (strpos($rcptResponse, '250') !== 0) {
            error_log("SMTP RCPT TO failed: $rcptResponse");
            fclose($socket);
            return false;
        }

        // DATA
        $dataResponse = $sendCommand("DATA");
        if (strpos($dataResponse, '354') !== 0) {
            error_log("SMTP DATA failed: $dataResponse");
            fclose($socket);
            return false;
        }

        // Build headers
        $headers = "From: {$this->fromName} <{$this->fromEmail}>\r\n";
        $headers .= "To: {$to}\r\n";
        $headers .= "Subject: {$subject}\r\n";
        if (!empty($options['reply_to'])) {
            $replyName = $options['reply_to_name'] ?? '';
            $headers .= "Reply-To: {$replyName} <{$options['reply_to']}>\r\n";
        }
        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
        $headers .= "\r\n";

        // Send message body, ending with <CRLF>.<CRLF>
        fwrite($socket, $headers . $htmlContent . "\r\n.\r\n");
        $response = $readResponse();

        // QUIT
        $sendCommand("QUIT");
        fclose($socket);

        // Check if message was accepted (250 response)
        if (strpos($response, '250') === 0) {
            return true;
        }

        error_log("SMTP error: $response");
        return false;
    }

    /**
     * Send email via SendGrid HTTP API
     */
    private function sendViaSendGrid($to, $subject, $htmlContent, $options = [])
    {
        if (empty($this->apiKey) || $this->apiKey === 'your-sendgrid-api-key-here') {
            error_log('SendGrid API key not configured');
            return false;
        }

        $data = [
            'personalizations' => [
                ['to' => [['email' => $to]]]
            ],
            'from' => [
                'email' => $this->fromEmail,
                'name' => $this->fromName
            ],
            'subject' => $subject,
            'content' => [
                ['type' => 'text/plain', 'value' => strip_tags(str_replace(['<br>', '<br/>', '<br />'], "\n", $htmlContent))],
                ['type' => 'text/html', 'value' => $htmlContent]
            ]
        ];

        // Add reply-to if specified
        if (!empty($options['reply_to'])) {
            $data['reply_to'] = [
                'email' => $options['reply_to'],
                'name' => $options['reply_to_name'] ?? ''
            ];
        }

        $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $this->apiKey,
            'Content-Type: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($httpCode >= 200 && $httpCode < 300) {
            return true;
        }

        error_log("SendGrid API error: HTTP $httpCode - $response - $error");
        return false;
    }

    /**
     * Wrap content in email layout
     */
    private function wrapInLayout($content, $title)
    {
        $year = date('Y');
        $siteUrl = SITE_URL;
        $siteName = SITE_NAME;

        return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
        }
        .email-wrapper {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .email-container {
            background: #ffffff;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .email-header {
            background: linear-gradient(135deg, #1a472a 0%, #0f2d1a 100%);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .email-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 600;
            color: #d4af37;
        }
        .email-body {
            padding: 30px;
        }
        .email-footer {
            background: #f9fafb;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #6b7280;
            border-top: 1px solid #e5e7eb;
        }
        .btn {
            display: inline-block;
            padding: 12px 24px;
            background: #d4af37;
            color: #1a1a1a !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 10px 0;
        }
        .btn:hover {
            background: #f4d03f;
        }
        .btn-green {
            background: #1a472a;
            color: white !important;
        }
        .btn-green:hover {
            background: #2d5a3d;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .info-table td {
            padding: 10px;
            border-bottom: 1px solid #e5e7eb;
        }
        .info-table td:first-child {
            color: #6b7280;
            width: 40%;
            font-weight: 500;
        }
        .info-table td:last-child {
            font-weight: 600;
        }
        .color-swatch {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 4px;
            vertical-align: middle;
            border: 1px solid #ddd;
        }
        .highlight-box {
            background: #f0f9f4;
            border: 1px solid #1a472a;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
        }
        .alert {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
        }
        .alert-warning {
            background: #fef3c7;
            color: #92400e;
        }
        .alert-info {
            background: #dbeafe;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="email-wrapper">
        <div class="email-container">
            <div class="email-header">
                <h1>&#9827; {$siteName}</h1>
            </div>
            <div class="email-body">
                {$content}
            </div>
            <div class="email-footer">
                <p>&copy; {$year} {$siteName}. All rights reserved.</p>
                <p>
                    <a href="{$siteUrl}" style="color: #1a472a;">Visit Website</a>
                </p>
            </div>
        </div>
    </div>
</body>
</html>
HTML;
    }

    /**
     * Send password reset email
     */
    public function sendPasswordReset($to, $name, $resetUrl)
    {
        $content = <<<HTML
<h2 style="margin-top: 0;">Reset Your Password</h2>
<p>Hi {$name},</p>
<p>We received a request to reset your password for your Jack Nine Tables account.</p>
<p>Click the button below to reset your password:</p>
<p style="text-align: center;">
    <a href="{$resetUrl}" class="btn">Reset Password</a>
</p>
<p>This link will expire in 1 hour for security reasons.</p>
<p>If you didn't request a password reset, you can safely ignore this email. Your password will remain unchanged.</p>
<div class="alert alert-warning">
    <strong>Security Tip:</strong> Never share your password or this reset link with anyone.
</div>
HTML;

        $html = $this->wrapInLayout($content, 'Reset Your Password');
        return $this->send($to, 'Reset Your Password - Jack Nine Tables', $html);
    }

    /**
     * Send quote request confirmation to customer
     */
    public function sendQuoteConfirmation($to, $name, $designData, $orderNumber = null)
    {
        $style = $designData['tableStyle'] === 'racetrack' ? 'With Racetrack' : 'Standard Rail';
        $surface = $designData['surfaceMaterial'] === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen';
        $cupHolders = $designData['cupHolders'] ? $designData['cupHolderCount'] . ' cup holders' : 'No cup holders';
        $railColor = $designData['railColor'] ?? '#1a1a1a';
        $surfaceColor = $designData['surfaceColor'] ?? '#1a472a';

        $orderInfo = $orderNumber ? "<p><strong>Quote Reference:</strong> {$orderNumber}</p>" : '';

        $content = <<<HTML
<h2 style="margin-top: 0;">Quote Request Received!</h2>
<p>Hi {$name},</p>
<p>Thank you for your interest in a custom poker table from Jack Nine Tables! We've received your quote request and will get back to you within 1-2 business days.</p>
{$orderInfo}
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Your Table Design</h3>
    <table class="info-table">
        <tr>
            <td>Table Style</td>
            <td>{$style}</td>
        </tr>
        <tr>
            <td>Size</td>
            <td>96" x 48" (8ft x 4ft)</td>
        </tr>
        <tr>
            <td>Rail Color</td>
            <td><span class="color-swatch" style="background: {$railColor};"></span></td>
        </tr>
        <tr>
            <td>Playing Surface</td>
            <td>{$surface}</td>
        </tr>
        <tr>
            <td>Surface Color</td>
            <td><span class="color-swatch" style="background: {$surfaceColor};"></span></td>
        </tr>
        <tr>
            <td>Cup Holders</td>
            <td>{$cupHolders}</td>
        </tr>
    </table>
</div>
<p>We'll review your design and prepare a detailed quote including pricing and estimated delivery time.</p>
<p>In the meantime, if you have any questions, feel free to</p>
<p style="text-align: center;">
    <a href="SITE_URL/contact.php" class="btn btn-green">Contact Us</a>
</p>
HTML;

        $content = str_replace('SITE_URL', SITE_URL, $content);
        $html = $this->wrapInLayout($content, 'Quote Request Received');
        return $this->send($to, 'Quote Request Received - Jack Nine Tables', $html);
    }

    /**
     * Send quote notification to admin
     */
    public function sendQuoteNotificationToAdmin($customerName, $customerEmail, $customerPhone, $designData, $notes, $orderNumber = null, $toEmail = null)
    {
        $toEmail = $toEmail ?? ADMIN_EMAIL;
        $style = $designData['tableStyle'] === 'racetrack' ? 'With Racetrack' : 'Standard Rail';
        $surface = $designData['surfaceMaterial'] === 'speedcloth' ? 'Suited Speed Cloth' : 'Velveteen';
        $cupHolders = $designData['cupHolders'] ? $designData['cupHolderCount'] . ' cup holders' : 'No cup holders';
        $railColor = $designData['railColor'] ?? '#1a1a1a';
        $surfaceColor = $designData['surfaceColor'] ?? '#1a472a';
        $notesText = !empty($notes) ? "Notes: " . $notes : '';
        $orderInfo = $orderNumber ? "Quote Reference: {$orderNumber}" : '';

        $content = <<<HTML
<h2 style="margin-top: 0;">New Quote Request!</h2>
<p>A new quote request has been submitted.</p>
<p>{$orderInfo}</p>
<h3>Customer Information</h3>
<p>Name: {$customerName}</p>
<p>Email: {$customerEmail}</p>
<h3>Table Design</h3>
<p>Table Style: {$style}</p>
<p>Size: 96 x 48 inches</p>
<p>Rail Color: {$railColor}</p>
<p>Playing Surface: {$surface}</p>
<p>Surface Color: {$surfaceColor}</p>
<p>Cup Holders: {$cupHolders}</p>
<p>{$notesText}</p>
HTML;

        $html = $this->wrapInLayout($content, 'New Quote Request');
        return $this->send($toEmail, 'New Quote Request from ' . $customerName, $html);
    }

    /**
     * Send contact form message to admin
     */
    public function sendContactMessage($name, $email, $phone, $subject, $message)
    {
        $phoneHtml = !empty($phone) ? "<tr><td>Phone</td><td>{$phone}</td></tr>" : '';
        $escapedMessage = nl2br(htmlspecialchars($message));

        $content = <<<HTML
<h2 style="margin-top: 0;">New Contact Form Message</h2>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Contact Information</h3>
    <table class="info-table">
        <tr>
            <td>Name</td>
            <td>{$name}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td><a href="mailto:{$email}">{$email}</a></td>
        </tr>
        {$phoneHtml}
        <tr>
            <td>Subject</td>
            <td>{$subject}</td>
        </tr>
    </table>
</div>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Message</h3>
    <p>{$escapedMessage}</p>
</div>
<p style="text-align: center;">
    <a href="mailto:{$email}" class="btn">Reply to {$name}</a>
</p>
HTML;

        $html = $this->wrapInLayout($content, 'Contact Form Message');
        return $this->send(ADMIN_EMAIL, '[Contact] ' . $subject . ' - from ' . $name, $html, [
            'reply_to' => $email,
            'reply_to_name' => $name
        ]);
    }

    /**
     * Send contact form confirmation to user
     */
    public function sendContactConfirmation($to, $name)
    {
        $content = <<<HTML
<h2 style="margin-top: 0;">Message Received!</h2>
<p>Hi {$name},</p>
<p>Thank you for contacting Jack Nine Tables! We've received your message and will get back to you as soon as possible, typically within 1-2 business days.</p>
<p>In the meantime, feel free to explore our custom table builder:</p>
<p style="text-align: center;">
    <a href="SITE_URL/builder.php" class="btn btn-green">Build Your Table</a>
</p>
<p>Thank you for your interest in our handcrafted poker tables!</p>
HTML;

        $content = str_replace('SITE_URL', SITE_URL, $content);
        $html = $this->wrapInLayout($content, 'Message Received');
        return $this->send($to, 'We received your message - Jack Nine Tables', $html);
    }

    /**
     * Send welcome email to new user
     */
    public function sendWelcomeEmail($to, $firstName)
    {
        $content = <<<HTML
<h2 style="margin-top: 0;">Welcome to Jack Nine Tables!</h2>
<p>Hi {$firstName},</p>
<p>Thank you for creating an account with us! We're excited to help you build your perfect custom poker table.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">What You Can Do Now</h3>
    <ul style="margin: 0; padding-left: 20px;">
        <li style="margin-bottom: 10px;"><strong>Design Your Table</strong> - Use our interactive builder to customize every detail</li>
        <li style="margin-bottom: 10px;"><strong>Save Your Designs</strong> - Create and save multiple designs to compare</li>
        <li style="margin-bottom: 10px;"><strong>Request a Quote</strong> - Get pricing for your custom table</li>
        <li><strong>Track Your Orders</strong> - Stay updated on your order status</li>
    </ul>
</div>
<p style="text-align: center;">
    <a href="SITE_URL/builder.php" class="btn">Start Building Your Table</a>
</p>
<p>If you have any questions, don't hesitate to reach out. We're here to help you create the perfect centerpiece for your game room!</p>
<p>Best regards,<br><strong>The Jack Nine Tables Team</strong></p>
HTML;

        $content = str_replace('SITE_URL', SITE_URL, $content);
        $html = $this->wrapInLayout($content, 'Welcome to Jack Nine Tables');
        return $this->send($to, 'Welcome to Jack Nine Tables!', $html);
    }

    /**
     * Send email verification link
     */
    public function sendVerificationEmail($to, $firstName, $verifyUrl)
    {
        $content = <<<HTML
<h2 style="margin-top: 0;">Verify Your Email Address</h2>
<p>Hi {$firstName},</p>
<p>Welcome to Jack Nine Tables! Please verify your email address to unlock all features of your account.</p>
<p style="text-align: center;">
    <a href="{$verifyUrl}" class="btn">Verify Email Address</a>
</p>
<div class="alert alert-info">
    <strong>Why verify?</strong> Once verified, you'll be able to:
    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
        <li>Save your custom table designs</li>
        <li>Request quotes for your designs</li>
        <li>Track your orders</li>
    </ul>
</div>
<p>If you didn't create an account with Jack Nine Tables, you can safely ignore this email.</p>
<p style="font-size: 12px; color: #666;">If the button doesn't work, copy and paste this link into your browser:<br>
<a href="{$verifyUrl}" style="color: #1a472a; word-break: break-all;">{$verifyUrl}</a></p>
HTML;

        $html = $this->wrapInLayout($content, 'Verify Your Email');
        return $this->send($to, 'Verify your email - Jack Nine Tables', $html);
    }

    /**
     * Send deposit payment confirmation to customer
     */
    public function sendDepositConfirmation($to, $firstName, $orderNumber, $depositAmount, $totalPrice)
    {
        $formattedDeposit = number_format($depositAmount, 2);
        $formattedTotal = number_format($totalPrice, 2);
        $remaining = number_format($totalPrice - $depositAmount, 2);
        $siteUrl = SITE_URL;

        $content = <<<HTML
<h2 style="margin-top: 0;">Deposit Payment Confirmed!</h2>
<p>Hi {$firstName},</p>
<p>Thank you for your deposit payment! Your custom poker table is now in the queue for production.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Payment Summary</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td>{$orderNumber}</td>
        </tr>
        <tr>
            <td>Deposit Paid</td>
            <td style="color: #1a472a; font-size: 1.2em;">\${$formattedDeposit}</td>
        </tr>
        <tr>
            <td>Total Order</td>
            <td>\${$formattedTotal}</td>
        </tr>
        <tr>
            <td>Remaining Balance</td>
            <td>\${$remaining}</td>
        </tr>
    </table>
</div>
<div class="alert alert-info">
    <strong>What's Next?</strong>
    <ul style="margin: 10px 0 0 0; padding-left: 20px;">
        <li>We'll begin sourcing materials for your table</li>
        <li>Production typically takes 4-6 weeks</li>
        <li>We'll keep you updated on progress</li>
        <li>Remaining balance is due before shipping</li>
    </ul>
</div>
<p style="text-align: center;">
    <a href="{$siteUrl}/my-orders.php" class="btn btn-green">View Your Orders</a>
</p>
<p>Thank you for choosing Jack Nine Tables!</p>
HTML;

        $html = $this->wrapInLayout($content, 'Deposit Payment Confirmed');
        return $this->send($to, 'Deposit Confirmed - Order ' . $orderNumber, $html);
    }

    /**
     * Send deposit notification to admin
     */
    public function sendDepositNotificationToAdmin($customerName, $customerEmail, $orderNumber, $depositAmount, $totalPrice)
    {
        $formattedDeposit = number_format($depositAmount, 2);
        $formattedTotal = number_format($totalPrice, 2);
        $siteUrl = SITE_URL;

        $content = <<<HTML
<h2 style="margin-top: 0;">New Deposit Payment Received!</h2>
<p>A customer has paid their deposit and is ready for production.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Payment Details</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td><strong>{$orderNumber}</strong></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>{$customerName}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td><a href="mailto:{$customerEmail}">{$customerEmail}</a></td>
        </tr>
        <tr>
            <td>Deposit Paid</td>
            <td style="color: #1a472a; font-size: 1.2em;">\${$formattedDeposit}</td>
        </tr>
        <tr>
            <td>Total Order</td>
            <td>\${$formattedTotal}</td>
        </tr>
    </table>
</div>
<p style="text-align: center;">
    <a href="{$siteUrl}/admin/quotes.php" class="btn">View in Admin</a>
</p>
HTML;

        $html = $this->wrapInLayout($content, 'New Deposit Payment');
        return $this->send(ADMIN_EMAIL, 'Deposit Received - Order ' . $orderNumber . ' - $' . $formattedDeposit, $html);
    }

    /**
     * Send quote price email to customer
     */
    public function sendQuotePriceEmail($to, $firstName, $orderNumber, $price, $depositAmount, $orderId = null)
    {
        $formattedPrice = number_format($price, 2);
        $formattedDeposit = number_format($depositAmount, 2);
        $depositPercent = DEPOSIT_PERCENTAGE;
        $siteUrl = SITE_URL;
        $payLink = $orderId ? "{$siteUrl}/pay-deposit.php?id={$orderId}" : "{$siteUrl}/my-orders.php";

        $content = <<<HTML
<h2 style="margin-top: 0;">Your Quote is Ready!</h2>
<p>Hi {$firstName},</p>
<p>Great news! We've reviewed your custom poker table design and have your quote ready.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Quote Details</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td><strong>{$orderNumber}</strong></td>
        </tr>
        <tr>
            <td>Total Price</td>
            <td style="color: #1a472a; font-size: 1.4em; font-weight: bold;">\${$formattedPrice}</td>
        </tr>
        <tr>
            <td>Deposit Required ({$depositPercent}%)</td>
            <td style="font-size: 1.1em;">\${$formattedDeposit}</td>
        </tr>
    </table>
</div>
<div class="alert alert-info">
    <strong>Ready to proceed?</strong> Pay your {$depositPercent}% deposit to secure your spot in our production queue. The deposit is non-refundable as we begin sourcing materials specifically for your table.
</div>
<p style="text-align: center;">
    <a href="{$payLink}" class="btn">Pay Deposit Now</a>
</p>
<p>This quote is valid for 30 days. If you have any questions about your quote or the build process, don't hesitate to reach out!</p>
<p>Thank you for choosing Jack Nine Tables!</p>
HTML;

        $html = $this->wrapInLayout($content, 'Your Quote is Ready');
        return $this->send($to, 'Your Quote is Ready - Order ' . $orderNumber, $html);
    }

    /**
     * Send final invoice email to customer
     */
    public function sendFinalInvoiceEmail($to, $firstName, $orderNumber, $remainingBalance, $totalPrice, $orderId = null)
    {
        $formattedRemaining = number_format($remainingBalance, 2);
        $formattedTotal = number_format($totalPrice, 2);
        $depositPaid = number_format($totalPrice - $remainingBalance, 2);
        $siteUrl = SITE_URL;
        $payLink = $orderId ? "{$siteUrl}/pay-final.php?id={$orderId}" : "{$siteUrl}/my-orders.php";

        $content = <<<HTML
<h2 style="margin-top: 0;">Your Table is Complete!</h2>
<p>Hi {$firstName},</p>
<p>Exciting news! Your custom poker table has been completed and is ready for delivery. Please complete your final payment to arrange shipping.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Final Invoice</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td><strong>{$orderNumber}</strong></td>
        </tr>
        <tr>
            <td>Total Price</td>
            <td>\${$formattedTotal}</td>
        </tr>
        <tr>
            <td>Deposit Paid</td>
            <td style="color: #1a472a;">-\${$depositPaid}</td>
        </tr>
        <tr style="border-top: 2px solid #1a472a;">
            <td><strong>Balance Due</strong></td>
            <td style="color: #1a472a; font-size: 1.4em; font-weight: bold;">\${$formattedRemaining}</td>
        </tr>
    </table>
</div>
<div class="alert alert-warning">
    <strong>Payment Required:</strong> Please complete your payment within 7 days to arrange delivery. We'll coordinate shipping details once payment is received.
</div>
<p style="text-align: center;">
    <a href="{$payLink}" class="btn">Pay Now</a>
</p>
<p>We can't wait for you to enjoy your new custom poker table! If you have any questions about delivery or setup, please let us know.</p>
<p>Thank you for choosing Jack Nine Tables!</p>
HTML;

        $html = $this->wrapInLayout($content, 'Your Table is Complete - Final Invoice');
        return $this->send($to, 'Final Invoice - Order ' . $orderNumber . ' - Balance Due: $' . $formattedRemaining, $html);
    }

    /**
     * Send final payment confirmation to customer
     */
    public function sendFinalPaymentConfirmation($to, $firstName, $orderNumber, $amountPaid, $totalPrice)
    {
        $formattedAmount = number_format($amountPaid, 2);
        $formattedTotal = number_format($totalPrice, 2);
        $siteUrl = SITE_URL;

        $content = <<<HTML
<h2 style="margin-top: 0;">Payment Complete - Thank You!</h2>
<p>Hi {$firstName},</p>
<p>Your final payment has been received. Your custom poker table is now fully paid and ready for delivery!</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Payment Confirmation</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td><strong>{$orderNumber}</strong></td>
        </tr>
        <tr>
            <td>Final Payment</td>
            <td>\${$formattedAmount}</td>
        </tr>
        <tr>
            <td>Total Paid</td>
            <td style="color: #1a472a; font-size: 1.2em; font-weight: bold;">\${$formattedTotal}</td>
        </tr>
        <tr>
            <td>Status</td>
            <td style="color: #1a472a;"><strong>PAID IN FULL</strong></td>
        </tr>
    </table>
</div>
<div class="alert alert-info">
    <strong>Delivery:</strong> We'll contact you shortly to coordinate delivery of your new poker table. If you have any special delivery instructions, please let us know!
</div>
<p style="text-align: center;">
    <a href="{$siteUrl}/my-orders.php" class="btn btn-green">View Your Order</a>
</p>
<p>Thank you for choosing Jack Nine Tables. We hope you enjoy many great games at your new table!</p>
HTML;

        $html = $this->wrapInLayout($content, 'Payment Complete');
        return $this->send($to, 'Payment Complete - Order ' . $orderNumber . ' Paid in Full', $html);
    }

    /**
     * Send final payment notification to admin
     */
    public function sendFinalPaymentNotificationToAdmin($customerName, $customerEmail, $orderNumber, $amountPaid, $totalPrice)
    {
        $formattedAmount = number_format($amountPaid, 2);
        $formattedTotal = number_format($totalPrice, 2);
        $siteUrl = SITE_URL;

        $content = <<<HTML
<h2 style="margin-top: 0;">Final Payment Received!</h2>
<p>Great news! A customer has completed their final payment and the order is ready for delivery.</p>
<div class="highlight-box">
    <h3 style="margin-top: 0; color: #1a472a;">Payment Details</h3>
    <table class="info-table">
        <tr>
            <td>Order Number</td>
            <td><strong>{$orderNumber}</strong></td>
        </tr>
        <tr>
            <td>Customer</td>
            <td>{$customerName}</td>
        </tr>
        <tr>
            <td>Email</td>
            <td><a href="mailto:{$customerEmail}">{$customerEmail}</a></td>
        </tr>
        <tr>
            <td>Final Payment</td>
            <td>\${$formattedAmount}</td>
        </tr>
        <tr>
            <td>Total Collected</td>
            <td style="color: #1a472a; font-size: 1.2em; font-weight: bold;">\${$formattedTotal}</td>
        </tr>
    </table>
</div>
<div class="alert alert-info">
    <strong>Action Required:</strong> Contact the customer to arrange delivery of their completed table.
</div>
<p style="text-align: center;">
    <a href="{$siteUrl}/admin/quotes.php" class="btn">View in Admin</a>
</p>
HTML;

        $html = $this->wrapInLayout($content, 'Final Payment Received');
        return $this->send(ADMIN_EMAIL, 'Final Payment - Order ' . $orderNumber . ' - $' . $formattedAmount, $html);
    }
}
