<?php

/**
 * Local File-Based Mailer
 *
 * This mailer simulates email sending by writing `.eml` files into the
 * `storage/mail/` directory (or a custom directory defined via MAIL_LOG_DIR).
 * It supports multipart (HTML + plain text) emails and generates valid MIME
 * headers for each message. Useful for local development and testing.
 *
 * @package Skavoo
 */

if (session_status() === PHP_SESSION_NONE) session_start();
require_once __DIR__ . '/env.php';

/**
 * Sends an email using the local file-based mailer.
 *
 * This is a simple wrapper around {@see send_mail_filebox()} to make usage
 * consistent across environments. It writes emails as `.eml` files rather
 * than sending them via SMTP.
 *
 * @param string $to        The recipient's email address.
 * @param string $subject   The subject of the email.
 * @param string $htmlBody  The HTML version of the email body.
 * @param string $textBody  (Optional) The plain text version of the email body.
 *                          If omitted, it will be auto-generated from $htmlBody.
 *
 * @return bool Returns true if the mail file was successfully written, false otherwise.
 */
function send_mail(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    return send_mail_filebox($to, $subject, $htmlBody, $textBody);
}

/**
 * Writes an email message to the local file system as a `.eml` file.
 *
 * This function creates a complete MIME email message with both text and HTML
 * parts, and writes it into the configured mail directory. It automatically
 * handles character encoding, MIME boundaries, and generates appropriate
 * message headers such as Message-ID and Date.
 *
 * @param string $to        The recipient's email address.
 * @param string $subject   The subject of the email.
 * @param string $htmlBody  The HTML version of the email content.
 * @param string $textBody  (Optional) The plain text version of the email content.
 *                          If empty, it will be derived from the HTML body.
 *
 * @return bool True if the `.eml` file was created successfully, false otherwise.
 *
 * @throws RuntimeException If the mail directory cannot be created or written to.
 */
function send_mail_filebox(string $to, string $subject, string $htmlBody, string $textBody = ''): bool
{
    // Determine storage directory (default: storage/mail/)
    $defaultDir = dirname(__DIR__, 2) . '/storage/mail';
    $dir = rtrim(getenv('MAIL_LOG_DIR') ?: $defaultDir, '/');

    // Create directory if it doesn't exist
    if (!is_dir($dir)) {
        @mkdir($dir, 0777, true);
    }

    // Load sender and reply-to information from environment variables
    $from     = getenv('MAIL_FROM_ADDRESS') ?: 'no-reply@example.test';
    $fromName = getenv('MAIL_FROM_NAME') ?: 'Skavoo Mailer';
    $replyTo  = getenv('MAIL_REPLY_TO') ?: $from;

    // Automatically derive plain text body if not provided
    if ($textBody === '' && $htmlBody !== '') {
        $converted = preg_replace('/<br\s*\/?>/i', "\n", $htmlBody);
        $textBody = trim(strip_tags($converted));
    }

    // Encode subject for UTF-8 compatibility
    if (function_exists('mb_encode_mimeheader')) {
        $subjectEncoded = mb_encode_mimeheader($subject, 'UTF-8');
    } else {
        $subjectEncoded = '=?UTF-8?B?' . base64_encode($subject) . '?=';
    }

    // Generate unique message identifiers
    $boundary = 'bndry_' . bin2hex(random_bytes(8));
    $date     = date('r');
    $domain   = parse_url(getenv('APP_URL') ?: 'http://localhost', PHP_URL_HOST) ?: 'localhost';
    $mid      = sprintf('<%s.%s@%s>', bin2hex(random_bytes(8)), time(), $domain);

    // Construct headers
    $headers = [
        'Date'         => $date,
        'Message-ID'   => $mid,
        'From'         => sprintf('"%s" <%s>', addslashes($fromName), $from),
        'To'           => $to,
        'Reply-To'     => $replyTo,
        'Subject'      => $subjectEncoded,
        'MIME-Version' => '1.0',
        'Content-Type' => 'multipart/alternative; boundary="' . $boundary . '"',
    ];

    // Build header string
    $headerBlob = implode("\r\n", array_map(fn($k) => $k . ': ' . $headers[$k], array_keys($headers)));

    // Build MIME body (plain text + HTML)
    $body  = "--{$boundary}\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $textBody . "\r\n";
    $body .= "--{$boundary}\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $htmlBody . "\r\n";
    $body .= "--{$boundary}--\r\n";

    // Combine headers and body into a raw message
    $raw = $headerBlob . "\r\n\r\n" . $body;

    // Generate file name and path
    $name = sprintf('%s_%s.eml', date('Ymd-His'), bin2hex(random_bytes(3)));
    $path = "{$dir}/{$name}";

    // Write message to disk
    return (bool)file_put_contents($path, $raw);
}
