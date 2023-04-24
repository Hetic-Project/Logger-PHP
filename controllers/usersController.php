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
        $request = $connection->prepare("SELECT * FROM login");
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
        // je récupère le json qui contient les infos de mon nouvel utilisateur
        $userInfos_json = filter_input(INPUT_POST, 'userInfos');
        // j'unpack le json
        $userInfos = json_decode($userInfos_json);
        if($userInfos) {
            header('HTTP/1.1 200 OK');
            // Requêtes SQL
            $request = $connection->prepare("INSERT INTO user (username, password, role) VALUES ($userInfos['username'], $userInfos['password'], $userInfos['role'])");
            $request->execute();
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
            $request = $connection->prepare("SELECT * FROM user WHERE password = $userinfos['password']");
            $request->execute();
            $currentUser = $request->fetchAll(PDO::FETCH_ASSOC);
            if($currentUser) {
                $newToken = generateToken();
                $request = $connection->prepare("INSERT INTO session (user_id, token) VALUES ($currentUser['id'], $newToken)");
                $request->execute();
                session_start();
                $_SESSION['username'] = $userInfos['username'];
                header('HTTP/1.1 200 OK');
            } else {
                header('HTTP/1.1 401 Identifiants incorrects')}
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        
        // Fermeture de la connection
        $connection = null;
    }

    }

    function deconnectUser(){
        //Connecter la BDD
        $db = new Database();
        // Ouverture de la connection
        $connection = $db->getConnection();

        session_start();
        // Supprime toutes les variables de session
        $_SESSION = array();
        // Détruire la session
        session_destroy();

        $request = $connection->prepare(" DELETE FROM session WHERE token =  ")

        // Fermeture de la connection
        $connection = null;
    }
}