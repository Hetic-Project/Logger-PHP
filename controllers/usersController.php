<?php

//Inclusion du fichier pour la connexion a la BDD
require_once './debug.php';
require_once './database/client.php';



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
            $request = $connection->prepare("INSERT INTO user (username, password, role) VALUES ($userInfos['username'], $userInfos['password'], $userInfos['role'])");
            $request->execute();
        } else {
            header('HTTP/1.1 400 Bad Request');
        }
        // Requêtes SQL
        

        // Fermeture de la connection
        $connection = null;
    }
}