<?php

require __DIR__ . '/../helper/smart_Include.php';
smartInclude('vendor/autoload.php');
smartInclude('/config/getDB.php');
use function App\config\getDB;
use  App\helper\response;
use App\Controller\auth;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\YamlFileLoader;

$lang = 'ar';
$translator = new Translator($lang);
$translator->addLoader('yaml', new YamlFileLoader());
$translator->addResource('yaml', __DIR__ . '/../translations/auth/' . $lang . '.yaml', $lang);

$data = json_decode(file_get_contents('php://input'), true);
$pdo = getDB();

$auth = new auth($pdo, $translator);

if (isset($_GET['action'])) {
    $action = $_GET['action'];

    switch ($action) {

        case 'create':

            $result = $auth->create($data);

            break;

        case 'login':

            $result = $auth->login($data);

            break;

        case 'resetpassword':

            $result = $auth->resetPassword($data);

            break;

        case 'updatepassword':

            $result = $auth->updatePassword($data);
            
            break;

        default:

            $result = ['error', 'Invalid action'];
            
            break;
    }
} else {
    $result = ['error', 'Action parameter is required'];
}
$response = new response(...$result);
$response->send();
// in htmx or view use $jsonString = $response->getJson(); 

?>