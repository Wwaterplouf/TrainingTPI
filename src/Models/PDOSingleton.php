<?php

namespace App\Models;

use PDO;
use PDOException;

class PDOSingleton
{
    private static $instance = null;
    private $pdo;

    // Informations de connexion à la base de données (généralement déportées)
    private $host = 'db';
    private $db = 'accounts_images_db'; // Remplacez par le nom de votre base de données
    private $user = 'accounts_images_user'; // Remplacez par votre nom d' utilisateur MySQL
    private $pass; // Remplacez par votre mot de passe MySQL
    private $charset = 'utf8mb4';

    private function __construct()
    {
        $this->pass = getenv("MARIADB_USER_PASSWORD");
        $dsn = "mysql:host=$this->host;dbname=$this->db;charset=$this->charset";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Gérer les erreurs avec des exceptions
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Résultats sous forme de tableaux associatifs
            PDO::ATTR_EMULATE_PREPARES => false, // Désactiver l'émulation des requêtes préparées
        ];
        try {
            $this->pdo = new PDO($dsn, $this->user, $this->pass, $options);
        } catch (PDOException $e) {
            throw new PDOException($e->getMessage(), (int)$e->getCode());
        }
    }

    private function __clone() {} 

    public static function getInstance()
    {
        if (self::$instance === null)
        {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }
}
