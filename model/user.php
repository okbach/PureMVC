<?php

namespace App\model;




use App\helper\DynamicCrud;
use PDO;


class User {
    public DynamicCrud $crud;
    
    public string $errorMessages = '';//only for raed no writ

    public function __construct(PDO $db)
    {
        $this->crud = new DynamicCrud($db);
    }


    public function create(array $userData): bool
    {
    
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        $code = random_int(100000, 999999); 
        $userData['api_token'] =  bin2hex(random_bytes(32));// 64 CHAR
        $userId = $this->crud->insert('users', $userData);

        if (!$userId) {
            $this->errorMessages = 'account_creation_error';
            return false;//تحتاج تفكير ربما ارجع  $userId 
        }
        
        return true;//تحتاج تفكير ربما ارجع  $userId 
       
    }

    public function login(array $userData)//: ?array 
    {
        
        
        $email = $userData['email'];
        $password =  $userData['password'] ;
        
        
        $data =  $this->crud->selectWhere('users', 'uid,email,password', ['email' => $email,'email_verified'=>true], 1);// نحتاج الباسورد في رد  من اجل تححق من انه مطابق 

        if (!$data) {

            $this->errorMessages = 'account_not_found';
            return false;
        }

        $storedHashedPassword = $data->password;
        if  ( password_verify($password, $storedHashedPassword) ) {
                unset($data->password);// ازالة الباسورد من الرد                 
                return $data;

        }else{

            //تسجيل محاول تسجيل دخول فاشلة 

            $this->errorMessages = 'login_failed';//'تحقق من كلمة سر مرة ثانية';
            return false; 
        }
        
    }

    public function getByEmail(string $email): ?array
    {
        return $this->crud->selectWhere('users', '*', ['email' => $email], 1);
    }

    
    public function update(int $userId, array $userData): bool
    {
        return $this->crud->update('users', $userData, ['id' => $userId]);
    }

    
    public function delete(int $userId): bool
    {
        return $this->crud->delete('users', ['id' => $userId]);
    }

    
    public function getAll(int $page = 1): array
    {
        return $this->crud->select_search('users', '*', [], '', $page);
    }


    public function validateLogin(string $email, string $password): ?array
    {
        
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user; 
        }
        
        return null; 
    }

    
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->crud->update('users', ['password' => $hashedPassword], ['id' => $userId]);
    }

    
    public function generateApiToken(int $userId): string
    {
        $apiToken = bin2hex(random_bytes(40)); //privet key 
        $this->crud->update('users', ['api_token' => $apiToken], ['id' => $userId]);
        return $apiToken;
    }



    public function createResetPasswordCode(int $userId, string $email, string $viaMethod = 'email'): ?string
    {
        
        $code = random_int(100000, 999999); //bin2hex(random_bytes(16));

        
        $verificationData = [
            'user_id' => $userId,
            'code' => $code,
            'purpose' => 'password_reset',
            'via_method' => $viaMethod,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), 
            'email' => $email
        ];

        
        $result = $this->crud->insert('verification_codes', $verificationData);

        if ($result) {
            return $code;
        }

        return null;
    }

    public function verifyResetPasswordCode(int $userId, string $code): bool
    {
    
        $verificationCode = $this->crud->selectWhere('verification_codes', '*', [
            'user_id' => $userId,
            'code' => $code,
            'purpose' => 'password_reset'
        ], 1);
    
        if ($verificationCode) {
            // حذف الرمز بمجرد العثور عليه
            //ريما ليس علي حذفه لتسجيل عدد المحاولات  
            $this->crud->delete('verification_codes', ['id' => $verificationCode->id]);
    
           
            $currentTime = date('Y-m-d H:i:s');
            if ($verificationCode->expires_at > $currentTime && $verificationCode->used_at === null) {
                //$this->errorMessages = 'time otp is expr';
                return true; 
            }
        }
    
        return false; 
    }


  public function resetPassword(int $userId, string $newPassword): bool
  {

      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $result = $this->crud->update('users', ['password' => $hashedPassword], ['uid' => $userId]);

      return $result;
  }







}


