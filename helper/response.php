<?php



function response(string $status, mixed $result = null, array $errorsValidation = [], string $errorsDatabase = null, array $errorsLogic = [],   $translator = null): void
{
    // http_response_code($status === 'success' ? 200 : 400); // يمكنك إضافة رموز حالة HTTP إذا لزم الأمر
    header('Content-Type: application/json');

    $errors = [];
    if (!empty($errorsValidation)) {
        $errors = $errorsValidation; //  $errors['validation'] = $errorsValidation;
    }

    if (!empty($errorsDatabase)) {
        if ($translator !== null) {
            $errors= $translator->trans($errorsDatabase, [], null, $translator->getLocale()); //  $errors['database']
        } else {
            $errors = $errorsDatabase;
        }
    }

    if (!empty($errorsLogic)) {
        $errors = $errorsLogic; //$errors['logic']
    }

    echo json_encode([
        'status' => $status,
        'result' => $result,
        'errors' => $errors,
    ]);

    exit;
}

// مثال على الاستخدام:
// response('error', null, ['email' => 'البريد الإلكتروني غير صالح'], 'database.error1', ['الحساب غير موجود'], $translator);
?>