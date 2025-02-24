<?php

//namespace App\Models;



//require_once __DIR__ . '/../config/database.php'; // استيراد الاتصال بقاعدة البيانات
//require_once __DIR__ . '/../helpers/DynamicCrud.php'; // استيراد كود CRUD


class User {
    public DynamicCrud $crud;
    
    public string $errorMessages = '';

    public function __construct(PDO $db)
    {
        $this->crud = new DynamicCrud($db);
    }

    // إنشاء مستخدم جديد
    public function create(array $userData): bool
    {
    
        $userData['password'] = password_hash($userData['password'], PASSWORD_DEFAULT);
        
        $userData['api_token'] =  bin2hex(random_bytes(32));// 64 CHAR
        $userId = $this->crud->insert('users', $userData);

        if (!$userId) {
            $this->errorMessages = 'حدث خطأ أثناء إنشاء الحساب. يرجى المحاولة مرة أخرى.';
            return false;//تحتاج تفكير ربما ارجع  $userId 
        }
        
        return true;//تحتاج تفكير ربما ارجع  $userId 
       
    }

    public function login(array $userData)//: ?array 
    {
        
        
        $email = $userData['email'];
        $password =  $userData['password'] ;

        $data =  $this->crud->selectWhere('users', 'uid,email,password', ['email' => $email], 1);// نحتاج الباسورد في رد  من اجل تححق من انه مطابق 

        if (!$data) {

            $this->errorMessages = 'لا يوجد هذا الحساب';
            return false;
        }

        $storedHashedPassword = $data->password;
        if  ( password_verify($password, $storedHashedPassword) ) {
                unset($data->password);// ازالة الباسورد من الرد                 
                return $data;

        }else{

            $this->errorMessages = 'تحقق من كلمة سر مرة ثانية';
            return false; 
        }
        
    }

    // استرجاع مستخدم بواسطة البريد الإلكتروني
    public function getByEmail(string $email): ?array
    {
        return $this->crud->selectWhere('users', '*', ['email' => $email], 1);
    }

    // تحديث بيانات المستخدم
    public function update(int $userId, array $userData): bool
    {
        return $this->crud->update('users', $userData, ['id' => $userId]);
    }

    // حذف مستخدم
    public function delete(int $userId): bool
    {
        return $this->crud->delete('users', ['id' => $userId]);
    }

    // استرجاع جميع المستخدمين
    public function getAll(int $page = 1): array
    {
        return $this->crud->select_search('users', '*', [], '', $page);
    }

    // تحقق من صحة بيانات الدخول
    public function validateLogin(string $email, string $password): ?array
    {
        // يمكنك إضافة عملية تشفير كلمة المرور هنا
        $user = $this->getByEmail($email);
        
        if ($user && password_verify($password, $user['password'])) {
            return $user; // إذا كانت البيانات صحيحة، نرجع معلومات المستخدم
        }
        
        return null; // إذا كانت البيانات غير صحيحة
    }

    // تحديث كلمة المرور
    public function updatePassword(int $userId, string $newPassword): bool
    {
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
        return $this->crud->update('users', ['password' => $hashedPassword], ['id' => $userId]);
    }

    // توليد وتحديث مفتاح API للمستخدم
    public function generateApiToken(int $userId): string
    {
        $apiToken = bin2hex(random_bytes(40)); // توليد مفتاح عشوائي
        $this->crud->update('users', ['api_token' => $apiToken], ['id' => $userId]);
        return $apiToken;
    }



    public function createResetPasswordCode(int $userId, string $email, string $viaMethod = 'email'): ?string
    {
        // إنشاء رمز عشوائي
        $code = bin2hex(random_bytes(16));

        // بيانات الرمز
        $verificationData = [
            'user_id' => $userId,
            'code' => $code,
            'purpose' => 'password_reset',
            'via_method' => $viaMethod,
            'expires_at' => date('Y-m-d H:i:s', strtotime('+1 hour')), // صلاحية الرمز لمدة ساعة
            'email' => $email
        ];

        // إدخال الرمز في جدول verification_codes
        $result = $this->crud->insert('verification_codes', $verificationData);

        if ($result) {
            return $code;
        }

        return null;
    }

    public function verifyResetPasswordCode(int $userId, string $code): bool
    {
        // البحث عن الرمز في جدول verification_codes
        $verificationCode = $this->crud->selectWhere('verification_codes', '*', [
            'user_id' => $userId,
            'code' => $code,
            'purpose' => 'password_reset'
        ], 1);
    
        if ($verificationCode) {
            // حذف الرمز بمجرد العثور عليه
            //ريما ليس علي حذفه لتسجيل عدد المحاولات  
            $this->crud->delete('verification_codes', ['id' => $verificationCode->id]);
    
            // التحقق من أن الرمز لم ينتهِ صلاحيته ولم يتم استخدامه
            $currentTime = date('Y-m-d H:i:s');
            if ($verificationCode->expires_at > $currentTime && $verificationCode->used_at === null) {
                return true; // الرمز صالح
            }
        }
    
        return false; // الرمز غير صالح أو غير موجود
    }


  public function resetPassword(int $userId, string $newPassword): bool
  {

      $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
      $result = $this->crud->update('users', ['password' => $hashedPassword], ['uid' => $userId]);

      return $result;
  }







}


