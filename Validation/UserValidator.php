<?php

namespace App\Validation;

class UserValidator extends BaseValidator
{
    public function validateRegistration()
    {
        $this->v->rule('required', 'name')->rule('alphaNum', 'name')->rule('lengthBetween', 'name', 4, 10);
        $this->v->rule('required', 'email')->rule('email', 'email');
        $this->v->rule('phone', 'phone_number')->rule('required', 'phone_number');
        $this->v->rule('lengthMin', 'password', 8)->rule('required', 'password');
        $this->v->rule('regex', 'password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
            ->message('Make sure your password contains uppercase and lowercase letters, numbers and symbols.');
        return $this->v;
    }

    public function validateLogin()
    {
        $this->v->rule('required', 'email')->rule('email', 'email');
        $this->v->rule('lengthMin', 'password', 8)->rule('required', 'password');
        $this->v->rule('regex', 'password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
            ->message('Make sure your password contains uppercase and lowercase letters, numbers and symbols.');
        return $this->v;
    }

    public function validateResetPassword()
    {
        $this->v->rule('required', 'email')->rule('email', 'email');
        return $this->v;
    }

    public function validateUpdatePassword()
    {
        $this->v->rule('required', 'email')->rule('email', 'email');
        $this->v->rule('required', 'code')->rule('length', 'code', 32);
        $this->v->rule('required', 'new_password')->rule('lengthMin', 'new_password', 8);
        $this->v->rule('regex', 'new_password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
            ->message('يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة وأرقام ورموز.');
        return $this->v;
    }
}