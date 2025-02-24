<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class Mailer {
    private $mail;

    public function __construct() {
        // تحميل الإعدادات من ملف env.php

        // تهيئة PHPMailer
        $this->mail = new PHPMailer(true);

        // إعدادات SMTP
        $this->mail->isSMTP();
        $this->mail->Host = SMTP_HOST;
        $this->mail->SMTPAuth = true;
        $this->mail->Username = SMTP_USERNAME;
        $this->mail->Password = SMTP_PASSWORD;
        $this->mail->SMTPSecure = SMTP_ENCRYPTION;
        $this->mail->Port = SMTP_PORT;

        // إعدادات المرسل
        $this->mail->setFrom(FROM_EMAIL, FROM_NAME);

        // تعيين الترميز إلى UTF-8
        $this->mail->CharSet = 'UTF-8';
    }

    public function send($to, $subject, $body, $isHTML = true, $attachments = []) {
        try {
            // إعدادات المستلم
            $this->mail->clearAddresses(); // مسح أي عناوين سابقة
            $this->mail->addAddress($to);

            // ترميز العنوان إذا كان يحتوي على حروف عربية
            $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'Q');
            $this->mail->Subject = $encodedSubject;

            // إعدادات البريد الإلكتروني
            $this->mail->isHTML($isHTML);
            $this->mail->Body = $body;
            $this->mail->AltBody = strip_tags($body); // نص بديل للرسائل النصية

            // إرفاق الملفات (إذا وجدت)
            foreach ($attachments as $attachment) {
                $this->mail->addAttachment($attachment);
            }

            // إرسال البريد الإلكتروني
            $this->mail->send();
            return true;
        } catch (Exception $e) {
            throw new Exception("Message could not be sent. Mailer Error: {$this->mail->ErrorInfo}");
        }
    }
}
?>