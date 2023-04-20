<?php
ini_set('display_errors', 1);
// inclure les controllers nécessaires
require_once './controllers/usersController.php';

// récupérer la méthode et l'URL de la requête
$method = $_SERVER['REQUEST_METHOD'];
$url = $_SERVER['REQUEST_URI'];

//Router
switch($url){
    // Route utilisateur de l'API
    case '/users/getAllUsers':
        // j'utillise la class Users
        $controller = new Users();
        if($method == 'GET'){
            // J'utilise la methode getUsers() de la class Users
            $controller->getUsers();
        }else{
            // en cas de méthode uri inconnue j'envoi une erreur
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: GET');
        };
        break;
    //Route inscription de l'API
    case '/register':
        // j'utilise la class Users
        $controller = new Users();
        if($method == 'POST') {
            // je récupère le json qui est envoyé
            $userInfos_json = filter_input(INPUT_POST, 'userInfos');
            // je décode le json
            $userInfos = json_decode($userInfos_json);
            // j'utilise la méthode createUser() de la class Users
            try {
                $controller->createUser($userInfos['username'], $userInfos['password'], $userInfos['role']);
                header('HTTP/1.1 200 OK');
            } catch {
                header('HTTP/1.1 400 Bad Request');
            }
        } else {
            //  en cas de méthode url inconnue j'envoie une erreur
            header('HTTP/1.1 405 Method Not Allowed');
            header('Allow: POST')
        }
        break;
    case '/login':
        break;
        case '/logout':
        break;
    default:
        // si aucune route ne correspond j'envoi une erreur
        header('HTTP/1.1 404 Not Found');
        break;    
}