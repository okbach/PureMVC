<?php
use Valitron\Validator;

class CustomValidationRules {
    public static function register() {

        Validator::addRule('phone', [self::class, 'validatePhone'], 'رقم الهاتف غير صحيح. يجب أن يحتوي على 7 إلى 14 رقمًا مع رمز الدولة الاختياري (+).');

        Validator::addRule('customRule', [self::class, 'validateCustomRule'], 'رسالة الخطأ المخصصة هنا.');
    }

    public static function validatePhone($field, $value) {
       
        return preg_match('/^\+?[0-9]{7,14}$/', $value);
    }

    public static function validateCustomRule($field, $value) {
     
        return $value === 'القيمة الصحيحة';
    }
}


CustomValidationRules::register();
?>