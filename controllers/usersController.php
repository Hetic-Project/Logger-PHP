<?php

//Inclusion du fichier pour la connexion a la BDD
require_once './debug.php';
require_once './database/client.php';



//fonction de génération de token
function generateToken() {
    $characters = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $characterNumber = strlen($characters);
    $token = "";
    for ($i=0; $i <  14; $i++) { 
        $token .= $characters[rand(0,$characterNumber - 1)];
    }
    return $token;
}

// Création du controller users

class Users {
    // une fonction qui récupère tous les utilisateurs 
    function getUsers(){
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();
        // Requêtes SQL
        $request = $connection->prepare("SELECT * FROM user");
        $request->execute();
        $users = $request->fetchAll(PDO::FETCH_ASSOC);

        // Fermeture de la connection
        $connection = null;

        // Envoi des données au format JSON
        header('Content-Type: application/json');
        echo json_encode($users);
    }

    function createUser() {
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');
        $role = filter_input(INPUT_POST, 'role');
        $mail = filter_input(INPUT_POST, 'mail');

        if($username && $password && $role && $mail) {
            // création d'un hash du mot de passe
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            header('HTTP/1.1 200 OK');
            // Requête SQL pour vérifier que l'utilisateur n'existe pas déjà
            $request = $connection->prepare("SELECT username FROM user WHERE username = :username");
            $request->execute([":username" => $username]);
            $usernameExists = $request->fetchAll(PDO::FETCH_ASSOC);

            if ($usernameExists) {
                header('HTTP/1.1 406 USER ALREADY EXISTS');
                return ;
            }
            // Requêtes SQL pour ajouter l'utilisateur à la base de données
            $request = $connection->prepare("INSERT INTO user (username, password, role, mail) VALUES (:username, :password, :role, :mail)");
            $request->execute([":username" => $username, ":password" => $hashed_password, ":role" => $role, ":mail" => $mail]);
            header('Location: http://localhost:3000/pages/login.php');
        } else {
            header('HTTP/1.1 400 Bad Request');
            header('Location: http://localhost:3000/pages/signIn.php');
        }
        
        // Fermeture de la connection
        $connection = null;
    }

    function connectUser() {
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        $username = filter_input(INPUT_POST, 'username');
        $password = filter_input(INPUT_POST, 'password');

        if($username && $password) {
            // Requêtes SQL
            $request = $connection->prepare("SELECT id, password, role, mail FROM user WHERE username = :username");
            $request->execute([":username" => $username]);
            $userInfos = $request->fetchAll(PDO::FETCH_ASSOC);
            if ($userInfos) {
                if(password_verify($password, $userInfos[0]['password'])) {
                    $newToken = generateToken();
                    $request = $connection->prepare("INSERT INTO session (user_id, token) VALUES (:currentUserID, :token)");
                    $request->execute([":currentUserID" => $userInfos[0]['id'], ":token" => $newToken]);
                    session_start();
                    $_SESSION['username'] = $username;
                    $_SESSION['id'] = $userInfos[0]['id'];
                    $_SESSION['role'] = $userInfos[0]['role'];
                    $_SESSION['mail'] = $userInfos[0]['mail'];
                    $_SESSION['token'] = $newToken;
                    header('HTTP/1.1 200 OK');
                    header('Location: http://localhost:3000/');
                } else {
                    header('HTTP/1.1 401 Mot de passe incorrect');
                    header('Location: http://localhost:3000/pages/login.php');
                }
            } else {
                header("HTTP/1.1 402 Ce nom d'utilisateur n'existe pas");
                header('Location: http://localhost:3000/pages/login.php');
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
            header('Location: http://localhost:3000/pages/login.php');
        }
        
        // Fermeture de la connection
        $connection = null;
    }

    function disconnectUser(){

        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        $sessionToken = $_GET['token'];

        if ($sessionToken) {

            // requête pour vérifier si le token correspond à l'utilisateur
            $request->$connection->prepare("SELECT id FROM user WHERE  username = :username");
            $request->execute([":username" => $_SESSION['username']]);
            $currentUserID = $request->fetchAll(PDO::FETCH_ASSOC);
            // requête pour vérifier si le token correspond à l'utilisateur
            $request->prepare("SELECT token FROM session WHERE user_id = :currentUserID");
            $request->execute([":currentUserID" => $currentUserID]);
            $currentToken = $request->fetchAll(PDO::FETCH_ASSOC);
            if ($currentToken[0]['token'] == $sessionToken) {
            // Supprime toutes les variables de session
                $_SESSION = array();
                // Détruire la session
                session_destroy();

                // requêtes SQL pour supprimer la session de la db
                $request = $connection->prepare(" DELETE FROM session WHERE token = :sessionToken");
                $request->execute([":sessionToken" => $sessionToken]);
                header('HTTP/1.1 200 OK');
            } else {
                header('HTTP/1.1 401 Unauthorized');
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        header('Location: http://localhost:3000/');

        // Fermeture de la connection
        $connection = null;
    }
}

function verifyUser() {
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        $token = filter_input(INPUT_POST, 'token');

        if(!($token)) {
            header('HTTP/1.1 400 Bad Request');
            return;
        }
        
        // requête pour récupérer l'id de l'utilisateur qui correspond au token
        $request->$connection->prepare("SELECT id FROM session WHERE  token = :token");
        $request->execute([":token" => $token]);
        $correspondingUser = $request->fetch(PDO::FETCH_ASSOC);

        if (!($correspondingUser)) {
            header('HTTP/1.1 401 Unauthorized');
            return;
        }

        // requête pour récupérer le role et l'username de l'utilisateur correspondant
        $request->$connection->prepare("SELECT role, username FROM user WHERE  id = :id");
        $request->execute([":id" => $correspondingUser['id']]);
        $currentUser = $request->fetch(PDO::FETCH_ASSOC);

        $currentUserID = $correspondingUser['id'];
        $currentUserRole = $currentUser['role'];
        $currentUserUsername = $currentUser['username'];


        header('HTTP/1.1 200 Ok');

        // rédaction de l'objet renvoyé en json
        $datasToSend = [];
        $datasToSend['status'] = "success";
        $datasToSend['message'] = "";
        $datasToSend['user'] = [];
        $datasToSend['user']['id'] = $currentUserID ;
        $datasToSend['user']['username'] = $currentUserUsername ;
        $datasToSend['user']['role'] = $currentUserRole ;

        header('Content-Type: application/json');
        echo json_encode($datasToSend);
}
?>