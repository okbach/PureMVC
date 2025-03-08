<?php

require __DIR__ . '/../helper/smart_Include.php';
smartInclude('vendor/autoload.php');//for use namespece like use Valitron\Validator;
smartInclude('helper/smartbuglog.php');

smartInclude('config/env.php');
smartInclude('config/connect_db.php');//function getDB()
smartInclude('helper/curd_db.php');//DynamicCrud
smartInclude('helper/mailer.php');//send email
smartInclude('helper/response.php');// respons json 
smartInclude('helper/render_template.php');// respons json 
smartInclude('model/user.php');//User class databass



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

smartInclude('services/JwtService.php');
use App\Services\JwtService;
$jwtService = new JwtService(jwtKey);
//-------------------------------------------------------------------------
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$lang ='ar';
$translator = new Translator($lang); 
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addResource('yaml', __DIR__ . '/../translations/auth/'.$lang.'.yaml', $lang);

//----------------------------------------------------------------------------



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
                    response('error', null, $v->errors(),$userModel->errorMessages,[],$translator);              
                }
               
            break;
            case 'login':
             
                $v = $validators->validateLogin();

                if ($v->validate() && $result = $userModel->login($data) ) { 

                    
                    
                    $result->Token    =    $jwtService->generateToken((array) $result, 3600 * 24); // 1 day
                    $result->refreshToken =    $jwtService->generateToken((array) $result, 3600 * 30); // 30 day
                    //header('Authorization: Bearer ' . $Token);
                        response('success', $result);      
                } else {    
                    response('error', null, $v->errors(),$userModel->errorMessages,[],$translator);            
                        
                }
                   
            break;
            case 'resetpassword':
               // try {
                    $v = $validators->validateResetPassword();
                    if (!$v->validate()) {
                        //throw new Exception("خطأ في التحقق من البيانات: " . json_encode($v->errors()));
                        response('error', null, $v->errors(), null, [], null);
                    }
            
                    $user = $userModel->crud->selectWhere('users', 'uid, email', ['email' => $data['email']], 1);
                    if (!$user) {
                        //throw new Exception("البريد الإلكتروني غير مسجل.");
                        $message = $translator->trans('email_not_fond', [], null, $translator->getLocale());
                        $result = ["message" => $message, "email" => "user@example.com"];
                        response('error', $result, [], null, [], null);
                    }
            
                    $code = $userModel->createResetPasswordCode($user->uid, $user->email, 'email');
                    if (!$code) {
                        throw new Exception("فشل في إنشاء رمز التحقق.");
                    }
            
                    $url = "$code";
                    $datax = smartInclude("lang/$lang/email/resetpassword.php");
                    $datax['url'] = $url;
                    $datax['company_name'] = 'wadiea';
                    $datax['dir'] = 'rtl';
                    $datax['language'] = $lang;
                    $subject = $datax['subject'];
            
                    $loader = new FilesystemLoader(__DIR__ . '/../view/templates');
                    $twig = new Environment($loader, ['cache' => __DIR__ . '/cache', 'debug' => true]);
                    $body = $twig->render('email/resetpassword.twig', $datax);
            
                    $mailer = new Mailer();
                    $to = $user->email;
                    if (!$mailer->send($to, $subject, $body, true)) {
                        throw new Exception("فشل في إرسال البريد الإلكتروني.");
                    }
            
                    $message = $translator->trans('sent_verification_email', [], null, $translator->getLocale());
                    $result = ["message" => $message, "email" => "user@example.com", "redirect_to" => "/verify-otp"];
                    response('success', $result, $v->errors(), $userModel->errorMessages, [], $translator);
            

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
