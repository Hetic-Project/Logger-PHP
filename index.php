<?php
// Afficher les erreurs dans le navigateur
ini_set('display_errors', 1);
// Cors de l'API : Qui a le droit de requêter l'API
// header("Access-Control-Allow-Origin: http://localhost:3000");
// header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
// header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Touts les fichiers routes de l'application
require_once './routes/usersRoutes.php';