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


smartInclude('model/user.php');//User class databass



//Valitron for Validat input
//use Valitron\Validator; //https://github.com/vlucas/valitron  $v->rule Validator
//use Firebase\JWT\Key;
use Firebase\JWT\JWT;
// Twig for templet 
use Twig\Loader\FilesystemLoader;
use Twig\Environment;

//--------------------------------------------------------------------
smartInclude('Validation/custom_valid.php');// Custom Validator
smartInclude('Validation/BaseValidator.php');// Custom Validator
smartInclude('Validation/UserValidator.php');// Custom Validator
use App\Validation\UserValidator;
$validators = new UserValidator();
//--------------------------------------------------------------------------

/*$language = $lang ='ar';//this global varibel used in trmplat file 
Validator::langDir(__DIR__.'/../lang');
Validator::lang($language);*/

$data = json_decode(file_get_contents('php://input'), true);//catch all input for procee 
//print_r($data);



$pdo = getDB(); // this to connect db


$userModel = new User($pdo);






    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        
        switch ($action) { 

            case 'create':
                 
                $v = $validators->validateRegistration();//Validators::validateRegistration($data);
               
                
                if ($v->validate() && $result = $userModel->create($data) ) { 
                    response('success', $result);
                } else {     
                    response('error', $userModel->errorMessages, $v->errors());              
                }
               
            break;
            case 'login':
             
                $v = $validators->validateLogin();

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
                
                $v = $validators->validateResetPassword();
                
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

                $v = $validators->validateUpdatePassword();
                
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
