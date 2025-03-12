<?php

namespace App\Controller;

use App\model\User;
use App\Validation\UserValidator;
use App\Services\JwtService;
use Symfony\Component\Translation\Translator;
use PDO;

class auth
{
    private $userModel;
    private $validator;
    private $jwtService;
    private $translator;

    public function __construct(PDO $pdo, Translator $translator)
    {
        $this->userModel = new User($pdo);
        $this->validator = new UserValidator();
        $this->jwtService = new JwtService(jwtKey);
        $this->translator = $translator;
    }

    public function create($data)
    {
        $v = $this->validator->validateRegistration();

        if ($v->validate() && $result = $this->userModel->create($data)) {
            return ['success', $result];
        } else {
            return ['error', null, $v->errors(), $this->userModel->errorMessages, [], $this->translator];
        }
    }

    public function login($data)
    {
        $v = $this->validator->validateLogin();

        if ($v->validate() && $result = $this->userModel->login($data)) {
            $result->Token = $this->jwtService->generateToken((array)$result, 3600 * 24);
            $result->refreshToken = $this->jwtService->generateToken((array)$result, 3600 * 30);
            return ['success', $result];
        } else {
            return ['error', null, $v->errors(), $this->userModel->errorMessages, [], $this->translator];
        }
    }

    public function resetPassword($data)
    {
        $v = $this->validator->validateResetPassword();
        if (!$v->validate()) {
            return ['error', null, $v->errors(), null, [], null];
        }

        $user = $this->userModel->crud->selectWhere('users', 'uid, email', ['email' => $data['email']], 1);
        if (!$user) {
            $message = $this->translator->trans('email_not_fond', [], null, $this->translator->getLocale());
            $result = ["message" => $message, "email" => "user@example.com"];
            return ['error', $result, [], null, [], null];
        }

        $code = $this->userModel->createResetPasswordCode($user->uid, $user->email, 'email');
        if (!$code) {
            return ['error', ["message" => "فشل في إنشاء رمز التحقق."], [], null, [], null];
        }

        $url = "$code";
        $lang = $this->translator->getLocale();
        $datax = smartInclude("lang/$lang/email/resetpassword.php");
        $datax['url'] = $url;
        $datax['company_name'] = 'wadiea';
        $datax['dir'] = 'rtl';
        $datax['language'] = $lang;
        $subject = $datax['subject'];

        $loader = new \Twig\Loader\FilesystemLoader(__DIR__ . '/../view/templates');
        $twig = new \Twig\Environment($loader, ['cache' => __DIR__ . '/../cache', 'debug' => true]);
        $body = $twig->render('email/resetpassword.twig', $datax);

        $mailer = new \App\helper\Mailer();
        $to = $user->email;
        if (!$mailer->send($to, $subject, $body, true)) {
            return ['error', ["message" => "فشل في إرسال البريد الإلكتروني."], [], null, [], null];
        }

        $message = $this->translator->trans('sent_verification_email', [], null, $this->translator->getLocale());
        $result = ["message" => $message, "email" =>$data['email'] , "redirect_to" => "/api/auth.php?action=updatepassword"];
        return ['success', $result, $v->errors(), $this->userModel->errorMessages, [], $this->translator];
    }

    public function updatePassword($data)
    {
        $v = $this->validator->validateUpdatePassword();

        if (!$v->validate()) {
            return ['error', [], $v->errors(), null, [], $this->translator];
        }

        $user = $this->userModel->crud->selectWhere('users', 'uid, email', ['email' => $data['email']], 1);

        if (!$user) {
            $message = $this->translator->trans('email_not_fond', [], null, $this->translator->getLocale());
            $result = ["message" => $message, "email" => "user@example.com"];
            return ['error', $result, [], null, [], null];
        }

        if (!$this->userModel->verifyResetPasswordCode($user->uid, $data['code'])) {
            $message = $this->translator->trans('bad_otp', [], null, $this->translator->getLocale());
            $result = ["message" => $message];
            return ['error', $result, [], null, [], null];
        }

        if (!$this->userModel->resetPassword($user->uid, $data['new_password'])) {
            $message = $this->translator->trans('failed_resetPassword', [], null, $this->translator->getLocale());
            $result = ["message" => $message];
            return ['error', $result, [], null, [], null];
        }

        return ['success', null, $v->errors(), $this->userModel->errorMessages, [], $this->translator];
    }
}