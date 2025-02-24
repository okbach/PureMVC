<?php
header('Content-Type: application/json');

require __DIR__ . '/../helper/smart_Include.php';
smartInclude('vendor/autoload.php');//for use namespece like use Valitron\Validator;
smartInclude('config/env.php');
smartInclude('config/connect_db.php');//function getDB()
smartInclude('helper/curd_db.php');//DynamicCrud
smartInclude('helper/mailer.php');//send email
smartInclude('helper/response.php');// respons json 
smartInclude('helper/render_template.php');// respons json 
smartInclude('helper/custom_valid.php');// Custom Validator
smartInclude('model/user.php');//User class databass



//Valitron for Validat input
use Valitron\Validator; //https://github.com/vlucas/valitron  $v->rule Validator
//use Firebase\JWT\Key;
use Firebase\JWT\JWT;
// Twig for templet 
use Twig\Loader\FilesystemLoader;
use Twig\Environment;



$language = $lang ='ar';//this global varibel used in trmplat file 
Validator::langDir(__DIR__.'/../lang');
Validator::lang($language);

$data = json_decode(file_get_contents('php://input'), true);//catch all input for procee 
//print_r($data);


$v = new Validator($data);// metho to put all input to be ready Validai later 

$pdo = getDB(); // this to connect db


$userModel = new User($pdo);







    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) { 

            case 'create':
                 
                $v->rule('required', 'name')->rule('alphaNum', 'name')->rule('lengthBetween', 'name', 4, 10);
                $v->rule('required', 'email')->rule('email', 'email');
                $v->rule('phone', 'phone_number')->rule('required', 'phone_number');

                $v->rule('lengthMin', 'password', 8)->rule('required', 'password');
                $v->rule('regex', 'password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
                    ->message('Make sure your password contains uppercase and lowercase letters, numbers and symbols.');  
                    
                //$v->rule('equals', 'password', 'confirmPassword');
                
                if ($v->validate() && $result = $userModel->create($data) ) { 
                    response('success', $result);
                } else {     
                    response('error', $userModel->errorMessages, $v->errors());              
                }
               
            break;
            case 'login':
             
                $v->rule('required', 'email')->rule('email', 'email');
                $v->rule('lengthMin', 'password', 8)->rule('required', 'password');
                $v->rule('regex', 'password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')->message('Make sure your password contains uppercase and lowercase letters, numbers and symbols.');  

                if ($v->validate() && $result = $userModel->login($data) ) { 

                    $payload = [
                        'iss' => 'auth', 
                        'aud' => 'wadiea.com', 
                        'iat' => time(), 
                        'exp' => time() + 3600*24, //1 day
                        'data' => $result
                    ];                     
                    $refreshPayload = [
                        'iss' => 'auth',
                        'aud' => 'wadiea.com',
                        'iat' => time(),
                        'exp' => time() + 3600 * 24 * 30, // 30 day
                        'data' => $result
                    ];

                    $Token = JWT::encode($payload, jwtKey, 'HS256');              
                    $refreshToken = JWT::encode($refreshPayload, jwtKey, 'HS256');
                    $result->Token    =   $Token;
                    $result->refreshToken =    $refreshToken;
                    //header('Authorization: Bearer ' . $Token);
                        response('success', $result);      
                } else {    
                         response('error', $userModel->errorMessages, $v->errors());               
                        
                }
                   
            break;
            case 'resetpassword':
                
                $v->rule('required', 'email')->rule('email', 'email');
                
                if ($v->validate()) {
                    
                    $user = $userModel->crud->selectWhere('users', 'uid, email', ['email' => $data['email']], 1);
                    
                    if ($user) {
                        
                        $code = $userModel->createResetPasswordCode($user->uid, $user->email, 'email');
                        
                        if ($code) {
                            
                            
                            
                            $url = "127.0.0.1/mymvc/api/auth.php?action=updatepassword&code=$code"; 

                            $datax = smartInclude("lang/$lang/email/resetpassword.php");
                            $datax['url'] = $url ;
                            $datax['company_name'] = 'wadiea';
                            $datax['dir'] = 'rtl';
                            $datax['language'] = $language;
                            $subject = $datax['subject'];

                                //Twig engin templet
                                $loader = new FilesystemLoader(__DIR__ . '/../view/templates');
                                $twig = new Environment($loader, [
                                    'cache' => __DIR__ . '/cache', 
                                    'debug' => true, 
                                ]);
                                

                                $body = $twig->render('email/resetpassword.twig', $datax );
                                //$twig->$GLOBALS
                                $mailer = new Mailer();
                                $to = $user->email;


                            if ($mailer->send($to, $subject,  $body, true)) {
                               
                                response('success', 'تم إرسال رمز التحقق إلى بريدك الإلكتروني.', $v->errors());   
                            } else {
                                response('success', 'فشل في إرسال البريد الإلكتروني.', $v->errors()); 
                            }
                        } else {
                            response('error', 'فشل في إنشاء رمز التحقق.', $v->errors()); 
                        }
                    } else {
                        
                        response('error', 'البريد الإلكتروني غير مسجل.', $v->errors()); 
                    }
                } else {
                    response('error', '', $v->errors()); 

                }
            break;     
            case 'updatepassword':

                $v->rule('required', 'email')->rule('email', 'email');
                $v->rule('required', 'code')->rule('length', 'code', 32); 
                $v->rule('required', 'new_password')->rule('lengthMin', 'new_password', 8);
                $v->rule('regex', 'new_password', '/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/')
                    ->message('يجب أن تحتوي كلمة المرور على أحرف كبيرة وصغيرة وأرقام ورموز.');  
                
                if (!$v->validate()) {
                    response('error', 'بيانات غير صالحة.', $v->errors());
                }
            
                $user = $userModel->crud->selectWhere('users', 'uid, email', ['email' => $data['email']], 1);
                
                if (!$user) {
                    response('error', 'البريد الإلكتروني غير مسجل.');
                }
            
                if (!$userModel->verifyResetPasswordCode($user->uid, $data['code'])) {
                    response('error', 'الرمز غير صحيح أو منتهي الصلاحية.');
                }
            
                if (!$userModel->resetPassword($user->uid, $data['new_password'])) {
                    response('error', 'فشل في تحديث كلمة المرور.');
                }
            
                response('success', 'تم تحديث كلمة المرور بنجاح.');
            break;

            
            default:
                response('error', 'Invalid action'); 
            break;

        }
    } else {

        response('error', 'Action parameter is required'); 
    }

?>
