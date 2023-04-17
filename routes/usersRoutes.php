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
    default:
        // si aucune route ne correspond j'envoi une erreur
        header('HTTP/1.1 404 Not Found');
        break;    
}