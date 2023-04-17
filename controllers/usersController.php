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
}