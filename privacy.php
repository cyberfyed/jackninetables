<?php
require_once 'config/config.php';

$pageTitle = 'Privacy Policy';
require_once 'includes/header.php';
?>

<section class="hero" style="padding: 3rem 0;">
    <div class="container hero-content">
        <h1>Privacy Policy</h1>
        <p>How we collect, use, and protect your information</p>
    </div>
</section>

<section class="section">
    <div class="container container-narrow">
        <div class="legal-content">
            <p class="legal-updated">Last updated: <?= date('F j, Y') ?></p>

            <div class="legal-section">
                <h2>1. Introduction</h2>
                <p>Welcome to Jack Nine Tables ("we," "our," or "us"). We are committed to protecting your personal information and your right to privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our website and use our services.</p>
                <p>Please read this privacy policy carefully. If you do not agree with the terms of this privacy policy, please do not access the site.</p>
            </div>

            <div class="legal-section">
                <h2>2. Information We Collect</h2>

                <h3>2.1 Personal Information You Provide</h3>
                <p>We collect personal information that you voluntarily provide to us when you:</p>
                <ul>
                    <li>Register for an account</li>
                    <li>Design and save custom table configurations</li>
                    <li>Request a quote for a custom table</li>
                    <li>Place an order</li>
                    <li>Contact us with inquiries</li>
                </ul>

                <p>This information may include:</p>
                <ul>
                    <li><strong>Identity Data:</strong> First name, last name, username</li>
                    <li><strong>Contact Data:</strong> Email address, phone number, shipping address, billing address</li>
                    <li><strong>Account Data:</strong> Password (encrypted), account preferences</li>
                    <li><strong>Order Data:</strong> Table design specifications, order history, delivery preferences</li>
                    <li><strong>Communication Data:</strong> Messages sent through our contact form or email</li>
                </ul>

                <h3>2.2 Information Automatically Collected</h3>
                <p>When you visit our website, we automatically collect certain information about your device, including:</p>
                <ul>
                    <li><strong>Device Data:</strong> Browser type, operating system, device type</li>
                    <li><strong>Usage Data:</strong> Pages viewed, time spent on pages, click patterns</li>
                    <li><strong>Location Data:</strong> General location based on IP address</li>
                    <li><strong>Cookies and Tracking:</strong> See our Cookie Policy section below</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>3. How We Use Your Information</h2>
                <p>We use the information we collect for various purposes, including to:</p>
                <ul>
                    <li>Create and manage your account</li>
                    <li>Save your table designs and preferences</li>
                    <li>Process quote requests and orders</li>
                    <li>Communicate about your order status and delivery</li>
                    <li>Provide customer support</li>
                    <li>Send promotional communications (with your consent)</li>
                    <li>Improve our website and services</li>
                    <li>Comply with legal obligations</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>4. Sharing Your Information</h2>
                <p>We may share your information in the following situations:</p>

                <h3>4.1 With Service Providers</h3>
                <p>We share information with third-party vendors who provide services such as:</p>
                <ul>
                    <li>Payment processing</li>
                    <li>Shipping and delivery</li>
                    <li>Email delivery</li>
                    <li>Website analytics</li>
                </ul>

                <h3>4.2 For Shipping and Delivery</h3>
                <p>When you place an order, we share your name, address, and phone number with shipping carriers to deliver your table.</p>

                <h3>4.3 For Legal Purposes</h3>
                <p>We may disclose your information where required by law or to protect our rights, privacy, safety, or property.</p>

                <h3>4.4 Business Transfers</h3>
                <p>If we are involved in a merger, acquisition, or sale of assets, your information may be transferred as part of that transaction.</p>
            </div>

            <div class="legal-section">
                <h2>5. Payment Processing</h2>
                <p>All payments are processed by trusted third-party payment processors. When you make a payment:</p>
                <ul>
                    <li>Your payment card details are transmitted directly to the payment processor's secure servers</li>
                    <li>We do not store your full card details on our systems</li>
                    <li>We only receive confirmation of payment success and the last 4 digits of your card for your reference</li>
                </ul>
                <p>Our payment processors employ industry-leading security measures including PCI-DSS compliance, encryption, and fraud detection.</p>
            </div>

            <div class="legal-section">
                <h2>6. Cookies and Tracking Technologies</h2>
                <p>We use cookies and similar tracking technologies to collect and track information about your browsing activities. Cookies are small data files placed on your device.</p>

                <h3>Types of Cookies We Use:</h3>
                <table class="legal-table">
                    <thead>
                        <tr>
                            <th>Cookie Type</th>
                            <th>Purpose</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><strong>Essential Cookies</strong></td>
                            <td>Required for the website to function (login sessions, security tokens, saved designs)</td>
                        </tr>
                        <tr>
                            <td><strong>Functional Cookies</strong></td>
                            <td>Remember your preferences (table builder selections, recently viewed items)</td>
                        </tr>
                        <tr>
                            <td><strong>Analytics Cookies</strong></td>
                            <td>Help us understand how visitors use our site</td>
                        </tr>
                    </tbody>
                </table>

                <p>You can control cookies through your browser settings. However, disabling certain cookies may limit your ability to use some features of our website, such as saving table designs.</p>
            </div>

            <div class="legal-section">
                <h2>7. Data Retention</h2>
                <p>We retain your personal information for as long as necessary to fulfill the purposes outlined in this privacy policy, unless a longer retention period is required by law.</p>
                <ul>
                    <li><strong>Account data:</strong> Retained while your account is active and for 3 years after deletion</li>
                    <li><strong>Order data:</strong> Retained for 7 years for tax and legal purposes</li>
                    <li><strong>Saved designs:</strong> Retained while your account is active</li>
                    <li><strong>Marketing data:</strong> Retained until you unsubscribe</li>
                </ul>
            </div>

            <div class="legal-section">
                <h2>8. Your Privacy Rights</h2>
                <p>Depending on your location, you may have the following rights:</p>
                <ul>
                    <li><strong>Access:</strong> Request a copy of your personal data</li>
                    <li><strong>Correction:</strong> Request correction of inaccurate data</li>
                    <li><strong>Deletion:</strong> Request deletion of your personal data</li>
                    <li><strong>Portability:</strong> Request transfer of your data to another service</li>
                    <li><strong>Opt-out:</strong> Opt out of marketing communications</li>
                    <li><strong>Withdraw Consent:</strong> Withdraw consent for data processing</li>
                </ul>
                <p>To exercise these rights, please contact us at <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</p>
            </div>

            <div class="legal-section">
                <h2>9. California Privacy Rights (CCPA)</h2>
                <p>If you are a California resident, you have specific rights under the California Consumer Privacy Act (CCPA):</p>

                <h3>9.1 Right to Know</h3>
                <p>You have the right to request that we disclose the categories and specific pieces of personal information we have collected about you.</p>

                <h3>9.2 Right to Delete</h3>
                <p>You have the right to request that we delete personal information we have collected from you, subject to certain exceptions.</p>

                <h3>9.3 Right to Opt-Out of Sale</h3>
                <p>Jack Nine Tables does not sell your personal information to third parties.</p>

                <h3>9.4 Right to Non-Discrimination</h3>
                <p>We will not discriminate against you for exercising any of your CCPA rights.</p>

                <p>To exercise your California privacy rights, please email us at <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a>.</p>
            </div>

            <div class="legal-section">
                <h2>10. Data Security</h2>
                <p>We implement appropriate technical and organizational security measures to protect your personal information, including:</p>
                <ul>
                    <li>Encryption of data in transit (SSL/TLS)</li>
                    <li>Secure password hashing</li>
                    <li>Regular security assessments</li>
                    <li>Limited employee access to personal data</li>
                </ul>
                <p>However, no method of transmission over the Internet is 100% secure. We cannot guarantee absolute security of your data.</p>
            </div>

            <div class="legal-section">
                <h2>11. Children's Privacy</h2>
                <p>Our services are not intended for children under 13 years of age. We do not knowingly collect personal information from children under 13. If you believe we have collected information from a child under 13, please contact us immediately.</p>
            </div>

            <div class="legal-section">
                <h2>12. Third-Party Links</h2>
                <p>Our website may contain links to third-party websites. We are not responsible for the privacy practices of these external sites. We encourage you to read their privacy policies.</p>
            </div>

            <div class="legal-section">
                <h2>13. Changes to This Policy</h2>
                <p>We may update this privacy policy from time to time. We will notify you of any changes by posting the new policy on this page and updating the "Last updated" date. Significant changes will be communicated via email or prominent notice on our website.</p>
            </div>

            <div class="legal-section">
                <h2>14. Contact Us</h2>
                <p>If you have questions or concerns about this privacy policy or our data practices, please contact us:</p>
                <div class="legal-contact">
                    <p><strong><?= SITE_NAME ?></strong></p>
                    <p>Email: <a href="mailto:<?= SITE_EMAIL ?>"><?= SITE_EMAIL ?></a></p>
                    <p>Or use our <a href="<?= SITE_URL ?>/contact.php">Contact Form</a></p>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>
