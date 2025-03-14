<?php

namespace App\Validation;

use Valitron\Validator;
use App\Validation\CustomValidationRules;

class BaseValidator
{
    protected $data;
    protected $language = 'ar';
    protected $v;
    

    public function __construct($language = 'ar')
    {

        
        Validator::langDir(__DIR__ . '/../lang');
        Validator::lang($this->language); // تصحيح: استخدام $this->language
        ;
        //print_r(json_decode(file_get_contents('php://input'), true));
        $this->data = json_decode(file_get_contents('php://input'), true);
        $this->v = new Validator($this->data);
        $this->language = $language;
        CustomValidationRules::register();
    }

    public function getValidator()
    {
        return $this->v;
    }

    public function getData()
    {
        return $this->data;
    }


    
}