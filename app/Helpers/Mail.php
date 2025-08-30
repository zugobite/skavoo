<?php

/**
 * Mail Helper Functions
 *
 * This file contains functions to send emails using the PHP mail function.
 * It supports sending multipart emails with both HTML and plain text content.
 */
function send_mail(string $to, string $subject, string $html, string $text): bool
{
    // Build a multipart/alternative email
    $boundary = 'b' . bin2hex(random_bytes(12));
    $from = getenv('MAIL_FROM') ?: 'no-reply@skavoo.local';
    $headers = [];
    $headers[] = "From: Skavoo <$from>";
    $headers[] = "MIME-Version: 1.0";
    $headers[] = "Content-Type: multipart/alternative; boundary=\"$boundary\"";

    $body  = "--$boundary\r\n";
    $body .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $text . "\r\n\r\n";
    $body .= "--$boundary\r\n";
    $body .= "Content-Type: text/html; charset=UTF-8\r\n";
    $body .= "Content-Transfer-Encoding: 8bit\r\n\r\n";
    $body .= $html . "\r\n\r\n";
    $body .= "--$boundary--";

    return mail($to, $subject, $body, implode("\r\n", $headers));
}
