<?php
/**
 * =============================================================
 *  TravelWithNaomi — Leads service (shared core)
 * -------------------------------------------------------------
 *  The single source of truth for validating, storing, and
 *  notifying on a new lead. Both the JSON API (api/index.php)
 *  and the classic HTML form handler (submit.php) call this so
 *  there is exactly one validation + insert + email path.
 * =============================================================
 */

declare(strict_types=1);

require_once __DIR__ . '/Validator.php';

final class Leads
{
    /** Travel-interest values allowed by the form's <select>. */
    public const ALLOWED_INTERESTS = [
        'Family Vacation',
        'Beach Getaway',
        'Cruise',
        'City Break',
        'Weekend Trip',
        'Honeymoon or Anniversary',
        'Visiting Family or Friends',
        'All of the Above',
    ];

    public function __construct(private PDO $pdo)
    {
    }

    /** Naomi's referral link, read from config with a safe fallback. */
    public static function referralLink(): string
    {
        return defined('APP_REFERRAL_LINK') ? APP_REFERRAL_LINK : '[MY_REFERRAL_LINK]';
    }

    /** Where lead notifications are sent, read from config with a fallback. */
    public static function notifyEmail(): string
    {
        return defined('APP_NOTIFY_EMAIL') ? APP_NOTIFY_EMAIL : '[MY_EMAIL]';
    }

    /**
     * Validate and normalise raw input.
     *
     * @param array<string,mixed> $input
     * @return array{0: array<string,mixed>, 1: array<string,string>} [clean, errors]
     */
    public static function validate(array $input): array
    {
        $fullName = Validator::clean($input['full_name'] ?? '');
        $email    = Validator::clean($input['email'] ?? '');
        $whatsapp = Validator::clean($input['whatsapp'] ?? '');
        $country  = Validator::clean($input['country'] ?? '');
        $interest = Validator::clean($input['travel_interest'] ?? '');

        $leadSource = Validator::slug($input['lead_source'] ?? '', 'direct-form');

        $utm = [];
        foreach (['utm_source', 'utm_medium', 'utm_campaign', 'utm_content', 'utm_term'] as $k) {
            $utm[$k] = Validator::nullableCapped($input[$k] ?? '', 100);
        }

        $errors = [];

        if ($fullName === '' || mb_strlen($fullName) > 120) {
            $errors['full_name'] = 'Full name is required (max 120 characters).';
        }
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 180) {
            $errors['email'] = 'A valid email address is required.';
        }
        // WhatsApp is optional, but if present must look like a phone number.
        if ($whatsapp !== '') {
            $digits = preg_replace('/\D+/', '', $whatsapp) ?? '';
            if (strlen($digits) < 7 || strlen($digits) > 20) {
                $errors['whatsapp'] = 'WhatsApp number looks invalid.';
            }
        }
        if ($country !== '' && mb_strlen($country) > 80) {
            $errors['country'] = 'Country name is too long (max 80 characters).';
        }
        if (!in_array($interest, self::ALLOWED_INTERESTS, true)) {
            $errors['travel_interest'] = 'Travel interest must be one of the allowed options.';
        }

        $clean = [
            'full_name'       => $fullName,
            'email'           => $email,
            'whatsapp'        => $whatsapp,
            'country'         => $country,
            'travel_interest' => $interest,
            'lead_source'     => $leadSource,
            'utm_source'      => $utm['utm_source'],
            'utm_medium'      => $utm['utm_medium'],
            'utm_campaign'    => $utm['utm_campaign'],
            'utm_content'     => $utm['utm_content'],
            'utm_term'        => $utm['utm_term'],
        ];

        return [$clean, $errors];
    }

    /**
     * Insert a validated lead, log the referral click, and fire the
     * notification email (best-effort — never throws on email failure).
     *
     * @param array<string,mixed> $clean Output of validate() (already valid).
     * @return int The new lead id.
     */
    public function create(array $clean, string $ip): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO leads
                (full_name, email, whatsapp, country, travel_interest,
                 lead_source, utm_source, utm_medium, utm_campaign, utm_content, utm_term, ip_address)
             VALUES
                (:full_name, :email, :whatsapp, :country, :travel_interest,
                 :lead_source, :utm_source, :utm_medium, :utm_campaign, :utm_content, :utm_term, :ip)'
        );
        $stmt->execute([
            ':full_name'       => $clean['full_name'],
            ':email'           => $clean['email'],
            ':whatsapp'        => $clean['whatsapp'],
            ':country'         => $clean['country'],
            ':travel_interest' => $clean['travel_interest'],
            ':lead_source'     => $clean['lead_source'],
            ':utm_source'      => $clean['utm_source'],
            ':utm_medium'      => $clean['utm_medium'],
            ':utm_campaign'    => $clean['utm_campaign'],
            ':utm_content'     => $clean['utm_content'],
            ':utm_term'        => $clean['utm_term'],
            ':ip'              => $ip,
        ]);

        $leadId = (int) $this->pdo->lastInsertId();

        // Log the referral click that is about to happen.
        $this->logClick($ip);

        // Notify the ambassador (best-effort, non-blocking).
        $this->notify($clean, $ip);

        return $leadId;
    }

    /** Record a referral click row. Returns false on DB error (never throws). */
    public function logClick(string $ip): bool
    {
        try {
            $this->pdo->prepare('INSERT INTO referral_clicks (ip_address) VALUES (:ip)')
                ->execute([':ip' => $ip]);
            return true;
        } catch (PDOException $e) {
            error_log('[TravelWithNaomi] Click log failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Send the lead-notification email. Uses SMTP via PHPMailer when
     * configured, otherwise falls back to mail(). Failures are logged
     * and swallowed so they never block the caller.
     *
     * @param array<string,mixed> $clean
     */
    private function notify(array $clean, string $ip): void
    {
        $to = self::notifyEmail();
        if ($to === '[MY_EMAIL]' || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            return;
        }

        $subject = 'New TravelWithNaomi lead: ' . $clean['full_name'];
        $body =
            "You captured a new lead from your landing page.\n\n" .
            "Name:            {$clean['full_name']}\n" .
            "Email:           {$clean['email']}\n" .
            "WhatsApp:        {$clean['whatsapp']}\n" .
            "Country:         {$clean['country']}\n" .
            "Travel interest: {$clean['travel_interest']}\n" .
            "Lead source:     {$clean['lead_source']}\n" .
            "IP address:      {$ip}\n" .
            'Captured at:     ' . gmdate('Y-m-d H:i:s') . " UTC\n";

        if (function_exists('smtp_ready') && smtp_ready()) {
            $this->sendSmtp($to, $subject, $body, $clean);
            return;
        }

        $headers = [
            'From: TravelWithNaomi <no-reply@' . ($_SERVER['HTTP_HOST'] ?? 'localhost') . '>',
            'Reply-To: ' . $clean['email'],
            'Content-Type: text/plain; charset=UTF-8',
        ];
        // @-suppressed: mail() may be disabled on some hosts; never block.
        @mail($to, $subject, $body, implode("\r\n", $headers));
    }

    /** SMTP delivery via the bundled PHPMailer. Errors are logged only. */
    private function sendSmtp(string $to, string $subject, string $body, array $clean): void
    {
        require_once __DIR__ . '/../../libs/phpmailer/Exception.php';
        require_once __DIR__ . '/../../libs/phpmailer/PHPMailer.php';
        require_once __DIR__ . '/../../libs/phpmailer/SMTP.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(false);
        try {
            $mail->isSMTP();
            $mail->Host       = SMTP_HOST;
            $mail->SMTPAuth   = true;
            $mail->Username   = SMTP_USERNAME;
            $mail->Password   = SMTP_PASSWORD;
            $mail->Port       = SMTP_PORT;
            $mail->SMTPSecure = (SMTP_PORT === 465)
                ? PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;

            $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
            $mail->addAddress($to);
            $mail->addReplyTo($clean['email'], $clean['full_name']);
            $mail->CharSet = 'UTF-8';
            $mail->Subject = $subject;
            $mail->Body    = $body;
            $mail->send();
        } catch (\Throwable $e) {
            error_log('[TravelWithNaomi] SMTP send failed: ' . $e->getMessage() . ' | ' . $mail->ErrorInfo);
        }
    }
}
