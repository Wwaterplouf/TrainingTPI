<?php

namespace App\Models;

use App\Models\PDOSingleton;
use PDO;

/**
 * Modèle représentant un rôle pour RBAC.
 */
class ARRole extends ActiveRecord
{
    /**
     * Nom de la table associée au modèle.
     *
     * @var string
     */
    protected static $table = 'roles';

    /** @var int|null Identifiant unique du rôle (clé primaire). */
    public $id = null;

    /** @var string Nom du rôle. */
    public $name = '';

    /** @var string Description du rôle. */
    public $description = '';

    /**
     * @param int|null $id
     * @param string $name
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    /**
     * Recherche un rôle par son ID.
     *
     * @param int $id Identifiant du rôle recherché.
     *
     * @return ARRole|null Instance de Role si trouvée, null sinon.
     */
    public static function findById($id): ?ARRole
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";

        $pdoInstance = PDOSingleton::getInstance();
        $stmt = $pdoInstance->getConnection()->prepare($sql);

        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new ARRole(
                $data
            );
        }

        return null;
    }

    /**
     * Recherche un rôle par son nom.
     *
     * @param string $name Nom du rôle recherché.
     *
     * @return Role|null Instance de Role si trouvée, null sinon.
     */
    public static function findByName(string $name): ?ARRole
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        $sql = "SELECT * FROM " . static::$table . " 
                WHERE name = :name
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'name' => $name,
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            return new ARRole(
                $data
            );
        }

        return null;
    }

    /**
     * Récupère l'ensemble des rôles.
     *
     * @return Role[] Liste d'objets Role.
     */
    public static function findAll(): array
    {
        $pdo = PDOSingleton::getInstance();

        $stmt = $pdo->getConnection()->query("SELECT * FROM " . static::$table . ";");
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];

        foreach ($datas as $data) {
            $result[] = new ARRole(
                $data
            );
        }

        return $result;
    }

    /**
     * Insère le rôle courant en base de données.
     *
     * @return void
     *
     * @throws \Throwable En cas d'erreur SQL.
     */
    public function create(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        $sql = "INSERT INTO " . static::$table . " (name, description) 
                VALUES (:name, :description);";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'name' => $this->name,
            'description' => $this->description,
        ]);

        $this->id = (int) $pdo->lastInsertId();
    }

    /**
     * Met à jour le rôle courant dans la base de données.
     *
     * @return void
     */
    public function update(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        $sql = "UPDATE " . static::$table . " 
                SET name = :name, 
                    description = :description 
                WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'name' => $this->name,
            'description' => $this->description,
            'id' => $this->id,
        ]);
    }

    /**
     * Supprime le rôle courant.
     *
     * @return void
     */
    public function delete(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $this->id]);
    }
}