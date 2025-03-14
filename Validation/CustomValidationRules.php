<?php
namespace App\Validation;

use Valitron\Validator;

class CustomValidationRules {
    public static function register() {

        Validator::addRule('phone', [self::class, 'validatePhone'], 'phone');
        
    }

    public static function validatePhone($field, $value) {
       
        return preg_match('/^\+?[0-9]{7,14}$/', $value);
    }
    
}



?>