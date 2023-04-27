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

        $body = file_get_contents('php://input');
        $userInfos = json_decode($body); 
        // je récupère le json qui contient les infos de mon nouvel utilisateur
        //$userInfos_json = filter_input(INPUT_POST, 'userInfos');
        // j'unpack le json
        //$userInfos = json_decode($userInfos_json);
        if($userInfos) {
            header('HTTP/1.1 200 OK');
            // Requêtes SQL
            $request = $connection->prepare("INSERT INTO user (username, password, role, mail) VALUES (:username, :password, :role, :mail)");
            $request->execute([":username" => $userInfos->username, ":password" => $userInfos->password, ":role" => $userInfos->role, ":mail" => $userInfos->mail]);
            
            $request = $connection->prepare("SELECT * FROM user WHERE username = :username");
            $request->execute([":username" => $userInfos->username]);
            $newUser = $request->fetchAll(PDO::FETCH_ASSOC);

            // Envoi des données au format JSON
            header('Content-Type: application/json');
            echo json_encode($newUser);

        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        
        // Fermeture de la connection
        $connection = null;
    }

    function connectUser() {
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        $userInfos_json = filter_input(INPUT_POST, 'userInfos');

        // j'unpack le json
        $userInfos = json_decode($userInfos_json);
        if($userInfos) {
            // Requêtes SQL
            $request = $connection->prepare("SELECT * FROM user WHERE password = :password");
            $request->execute([":password" => $userinfos['password']]);
            $currentUser = $request->fetchAll(PDO::FETCH_ASSOC);
            if($currentUser) {
                $newToken = generateToken();
                $request = $connection->prepare("INSERT INTO session (user_id, token) VALUES (:currentUserID, :token)");
                $request->execute([":currentUserID" => $currentUser['id'], ":token" => $newToken]);
                session_start();
                $_SESSION['username'] = $userInfos['username'];
                header('HTTP/1.1 200 OK');
            } else {
                header('HTTP/1.1 401 Identifiants incorrects');
            }
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        
        // Fermeture de la connection
        $connection = null;
    }

    function disconnectUser(){

        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();
        // je récupère le token de la session
        $sessionToken_json = filter_input(INPUT_POST, 'sessionToken');
        // j'unpack le json
        $sessionToken = json_decode($sessionToken_json);
        // $session token passe de tableau associatif contenant le token au token
        $sessionToken = $sessionToken['token'];
        if ($sessionToken) {
            // requête pour vérifier si le token correspond à l'utilisateur
            $request->prepare("SELECT id FROM user WHERE  username = :username");
            $request->execute([":username" => $_SESSION['username']]);
            $currentUserID = $request->fetchAll(PDO::FETCH_ASSOC);
            // requête pour vérifier si le token correspond à l'utilisateur
            $request->prepare("SELECT token FROM session WHERE user_id = :currentUserID");
            $request->execute([":currentUserID" => $currentUserID]);
            $currentToken = $request->fetchAll(PDO::FETCH_ASSOC);
            if ($currentToken == $sessionToken) {
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

        // Fermeture de la connection
        $connection = null;
    }
}
?>