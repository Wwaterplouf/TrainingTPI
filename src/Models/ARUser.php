<?php

namespace App\Models;

use App\Models\PDOSingleton;
use PDO;

/**
 * Modèle représentant un utilisateur.
 */
class ARUser extends ActiveRecord
{
    /**
     * Nom de la table associée au modèle.
     *
     * @var string
     */
    protected static $table = 'users';

    /** @var int|null Identifiant unique de l'utilisateur (clé primaire). */
    public $id = null;

    /** @var string Nom d'utilisateur (login). */
    public $username = '';

    /** @var string */
    public $password = '';

    /**
     * Rôles de l'utilisateur pour RBAC via table pivot.
     * Changement : suppression de $role unique, utilisation de table pivot users_has_roles.
     *
     * @var ARRole[]|null
     */
    private $roles = null;

    /**
     * @param int|null $id
     * @param string $username
     * @param string $password
     */
    public function __construct(array $data = [])
    {
         parent::__construct($data);
    }

    /**
     * Récupère les rôles de l'utilisateur via la table pivot.
     *
     * @return ARRole[]
     */
    public function getRoles(): array
    {
        if ($this->roles !== null) {
            return $this->roles;
        }
        if ($this->id === null) {
            return [];
        }
        $pdo = PDOSingleton::getInstance()->getConnection();
        $sql = "SELECT roles.* FROM roles
                INNER JOIN users_has_roles ON roles.id = users_has_roles.roles_id 
                WHERE users_has_roles.users_id = :user_id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['user_id' => $this->id]);

        $datas = $stmt->fetchAll();

        $this->roles = [];
        foreach ($datas as $data) {
            $this->roles[] = new ARRole(
                $data
            );
        }
        return $this->roles;
    }

    /**
     * Vérifie si l'utilisateur a un rôle spécifique.
     *
     * @param string $roleName Nom du rôle.
     * @return bool
     */
    public function hasRole(string $roleName): bool
    {
        $roles = $this->getRoles();
        foreach ($roles as $role) {
            if ($role->name === $roleName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Assigne un rôle à l'utilisateur.
     *
     * @param ARRole $role
     * @return void
     */
    public function assignRole(ARRole $role): void
    {
        if ($this->id === null || $role->id === null) {
            return;
        }
        // Vérifier si déjà assigné
        if ($this->hasRole($role->name)) {
            return;
        }
        $pdo = PDOSingleton::getInstance()->getConnection();
        $sql = "INSERT INTO users_has_roles (users_id, roles_id) VALUES (:user_id, :role_id)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $this->id,
            'role_id' => $role->id,
        ]);

        // Invalider le cache des rôles
        $this->roles = null;
    }

    /**
     * Retire un rôle à l'utilisateur.
     *
     * @param ARRole $role
     * @return void
     */
    public function removeRole(ARRole $role): void
    {
        if ($this->id === null || $role->id === null) {
            return;
        }

        $pdo = PDOSingleton::getInstance()->getConnection();

        $sql = "DELETE FROM users_has_roles WHERE users_id = :user_id AND roles_id = :role_id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'user_id' => $this->id,
            'role_id' => $role->id,
        ]);

        // Invalider le cache des rôles
        $this->roles = null;
    }

    /**
     * Recherche un utilisateur par son ID.
     *
     * @param int $id Identifiant de l'utilisateur recherché.
     *
     * @return ARUser|null Instance de User si trouvée, null sinon.
     */
    public static function findById($id): ?ARUser
    {
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";

        $pdoInstance = PDOSingleton::getInstance();
        $stmt = $pdoInstance->getConnection()->prepare($sql);

        $stmt->execute(['id' => $id]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // On retourne un objet User initialisé avec les données de la base.
            // Le champ password contient ici le hash stocké.
            return new ARUser(
                $data
            );
        }

        // Aucun enregistrement trouvé.
        return null;
    }

    /**
     * Recherche un utilisateur par son username.
     *
     * Utilisé notamment pour :
     * - vérifier l'unicité du username,
     * - retrouver un utilisateur à partir de son login.
     *
     * @param string $username Nom d'utilisateur recherché.
     *
     * @return ARUser|null Instance de User si trouvée, null sinon.
     */
    public static function findByUsername(string $username): ?ARUser
    {
        // Connexion PDO via le singleton.
        $pdo = PDOSingleton::getInstance()->getConnection();

        // On cherche un seul utilisateur par username.
        $sql = "SELECT * FROM " . static::$table . " 
                WHERE username = :username
                LIMIT 1";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            'username' => $username,
        ]);

        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // On reconstruit un objet User.
            // Le password est ici le hash stocké en base.
            return new ARUser(
                $data
            );
        }

        // Aucun utilisateur correspondant au username.
        return null;
    }

    /**
     * Récupère l'ensemble des utilisateurs.
     *
     * @return ARUser[] Liste d'objets User.
     */
    public static function findAll(): array
    {
        $pdo = PDOSingleton::getInstance();

        $stmt = $pdo->getConnection()->query("SELECT * FROM " . static::$table . ";");
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $result = [];

        foreach ($datas as $data) {
            $result[] = new ARUser(
                $data
            );
        }

        return $result;
    }

    /**
     * Vérifie la validité d'un couple (username, password) pour l'authentification.
     *
     * Processus :
     * 1. Recherche de l'utilisateur correspondant au username donné.
     * 2. Vérification du mot de passe en clair avec password_verify()
     *    sur le hash stocké en base.
     * 3. En cas de succès, stockage en session des informations nécessaires
     *    à l'authentification (id, username, is_admin).
     *
     * @param string $username Nom d'utilisateur fourni (login).
     * @param string $password Mot de passe en clair fourni par l'utilisateur.
     *
     * @return bool true si les identifiants sont valides, false sinon.
     */
    public static function isValid($username, $password): bool
    {
        $pdoInstance = PDOSingleton::getInstance();
        $sql = "SELECT * FROM " . static::$table . " WHERE username = :username;";

        $stmt = $pdoInstance->getConnection()->prepare($sql);
        $stmt->execute(['username' => $username]);

        // $user contiendra les colonnes de la table sous forme de tableau associatif.
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        // Si un utilisateur existe et que le mot de passe en clair correspond au hash stocké.
        if ($user && password_verify($password, $user['password'])) {
            // Créer l'objet User pour récupérer les rôles
            $userObj = ARUser::findById($user['id']);            
            $roles = $userObj->getRoles();            
            $roleNames = array_map(fn($role) => $role->name, $roles);

            // On stocke en session les informations nécessaires pour l'authentification.
            // Ces données seront utilisées ensuite dans tout le reste de l'application.
            // RBAC: Stockage des rôles via pivot
            $_SESSION['user_connected'] = [
                'id' => (int) $user['id'],
                'username' => $user['username'],
                'roles' => $roleNames,
            ];

            return true;
        }

        // Soit l'utilisateur n'existe pas, soit le mot de passe est incorrect.
        return false;
    }

    /** AVEC LA GESTION DES TRANSACTIONS */

    /**
     * Insère l'utilisateur courant en base de données.
     *
     * - Le mot de passe stocké est systématiquement hashé avec password_hash().
     * - Après l'insertion, l'ID généré est affecté à la propriété $id.
     *
     * @return void
     *
     * @throws \Throwable En cas d'erreur SQL ; l'exception est relancée après
     *                    un éventuel rollBack().
     */
    public function create(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        try {
            // Début de transaction
            $pdo->beginTransaction();

            $sql = "INSERT INTO " . static::$table . " (username, password) 
                VALUES (:username, :password);";

            $stmt = $pdo->prepare($sql);

            // Hash du mot de passe avant stockage (bonne pratique de sécurité).
            $stmt->execute([
                'username' => $this->username,
                'password' => password_hash($this->password, PASSWORD_DEFAULT),
            ]);

            // Récupération de l'ID auto-incrémenté généré par l'insertion.
            $this->id = (int) $pdo->lastInsertId();

            // Validation de la transaction
            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Met à jour l'utilisateur courant dans la base de données SANS gérer de transaction.
     *
     * Méthode interne, utilisée par les méthodes publiques transactionnelles.
     * Elle suppose qu'une connexion PDO valide est fournie (éventuellement déjà
     * dans une transaction).
     *
     * Remarque : la propriété $password doit déjà contenir soit le hash existant,
     * soit un nouveau hash si le mot de passe a été modifié. Aucun hash n'est
     * appliqué ici.
     *
     * @param \PDO $pdo Connexion PDO à utiliser pour la mise à jour.
     *
     * @return void
     */
    protected function updateRaw(\PDO $pdo): void
    {
        $sql = "UPDATE " . static::$table . " 
            SET username = :username, 
                password = :password 
            WHERE id = :id;";

        $stmt = $pdo->prepare($sql);

        $stmt->execute([
            'username' => $this->username,
            'password' => $this->password,
            'id' => $this->id,
        ]);
    }

    /**
     * @return void
     * @throws \Throwable
     */
    public function update(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            $this->updateRaw($pdo);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $e;
        }
    }

    /**
     * Supprime l'utilisateur courant SANS gérer de transaction.
     * (Méthode interne, utilisée par les méthodes transactionnelles.)
     *
     * @param \PDO $pdo Connexion PDO déjà ouverte (éventuellement dans une transaction).
     *
     * @return void
     */
    protected function deleteRaw(\PDO $pdo): void
    {
        $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id' => $this->id]);
    }

    /**
     * Version simple : suppression d'un seul utilisateur
     * dans SA propre transaction.
     */
    public function delete(): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            $this->deleteRaw($pdo);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

    /**
     * Version “riche” : suppression d'un utilisateur + ses tâches,
     * dans UNE transaction globale.
     */
    public static function deleteAndTasks(int $userId): void
    {
        $pdo = PDOSingleton::getInstance()->getConnection();

        try {
            $pdo->beginTransaction();

            $user = self::findById($userId);
            if ($user === null) {
                throw new \RuntimeException('Utilisateur introuvable (id=' . $userId . ').');
            }

            // Vérif dernier admin
            // RBAC: Vérification via pivot si l'utilisateur a le rôle 'admin'
            if ($user->hasRole('admin')) {
                $sqlCountAdmins = "SELECT COUNT(*) FROM users_has_roles uhr 
                                   INNER JOIN roles r ON uhr.roles_id = r.id 
                                   WHERE r.name = 'admin'";
                $stmtCount = $pdo->query($sqlCountAdmins);
                $nbAdmins = (int) $stmtCount->fetchColumn();

                if ($nbAdmins <= 1) {
                    throw new \RuntimeException(
                        "Impossible de supprimer le dernier administrateur de l'application."
                    );
                }
            }

            // Suppression des tâches liées
            $sqlDeleteTasks = "DELETE FROM tasks WHERE users_id = :id";
            $stmtTasks = $pdo->prepare($sqlDeleteTasks);
            $stmtTasks->execute(['id' => $userId]);

            // Suppression de l'utilisateur lui-même (sans nouvelle transaction)
            $user->deleteRaw($pdo);

            $pdo->commit();
        } catch (\Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }

}