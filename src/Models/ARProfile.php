<?php

namespace Models;

use Config\PDOSingleton;
use Exception;
use App\Models\ActiveRecord;
use PDO;
use Slim\Exception\HttpNotImplementedException;

class ARProfile extends ActiveRecord
{

    protected static $table = "profiles";

    public $id = null;
    public $first_name = '';
    public $last_name = '';
    public $email = '';
    public $bio = '';
    public $created_at;
    public $updated_at;
    public array $skills = [];
    public array $skillsId = [];
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function create()
    {
        $infosOk = $this->validateInfos();
        if ($infosOk === true) {
            try {
                $this->beginTransaction();
                $sql = "INSERT INTO " . self::$table . "(first_name, last_name, email, bio) VALUES (:first_name, :last_name, :email, :bio);";
                $this->executeQuery($sql, [
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'bio' => $this->bio
                ]);
                $this->id = $this->pdoConnection->lastInsertId();

                $this->updateSkills();
                $this->commit();
                return true;
            } catch (Exception $e) {
                $this->rollBack();
                throw ($e);
            }
        } else {
            return $infosOk; // diagnostique des erreurs
        }
    }
    public function update()
    {
        $infosOk = $this->validateInfos();
        if ($infosOk === true) {
            try {
                $this->beginTransaction();
                $sql = "UPDATE " . self::$table . " SET first_name = :first_name, last_name = :last_name, email = :email, bio = :bio WHERE id = :id;";
                $this->executeQuery($sql, [
                    'id' => $this->id,
                    'first_name' => $this->first_name,
                    'last_name' => $this->last_name,
                    'email' => $this->email,
                    'bio' => $this->bio
                ]);
                $this->updateSkills();
                $this->commit();
                return true;
            } catch (Exception $e) {
                $this->rollBack();
                throw ($e);
            }
        } else {
            return $infosOk; // diagnostique des erreurs
        }
    }

    protected function beforeDelete()
    {
        $sqlDelete = "DELETE FROM profiles_skills WHERE profile_id = :id";
        $this->executeQuery($sqlDelete, ["id" => $this->id]);
    }

    // public function getCommandes()
    // {
    //     $sql = "SELECT * FROM commandes WHERE utilisateur_id = :utilisateur_id;";
    //     $stmt = $this->executeQuery($sql, ['utilisateurs_id' => $this->id]);
    //     return $stmt->fetchAll(PDO::FETCH_ASSOC);
    // }

    public function validateInfos()
    {
        $diagnostic = [];

        $this->first_name = filter_var($this->first_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        $this->last_name = filter_var($this->last_name, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
        if (!$this->first_name || !$this->last_name || strlen($this->first_name) < 2 || strlen($this->last_name) < 2) {
            $diagnostic["ErrorName"] = "Nom/Prenom incorrects";
        }

        $this->email = filter_var($this->email, FILTER_VALIDATE_EMAIL);
        if (!$this->email || $this->executeQuery("SELECT id FROM profiles WHERE email = :email", ["email" => $this->email])->fetch()) {
            $diagnostic["ErrorEmail"] = "Email invalide ou déjà pris";
        }

        $this->bio = filter_var($this->bio, FILTER_SANITIZE_FULL_SPECIAL_CHARS);

        $skills = [];
        foreach ($this->skills as $skill) {
            $skill = filter_var($skill, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
            if (strlen($skill) < 2) {
                $diagnostic["ErrorSkills"] = "Erreur dans le tableau des skills (2 char min par skill)";
            } else {
                $skills[] = $skill;
            }
        }
        $this->skills = $skills;

        if ($diagnostic == []) {
            return true;
        }
        return $diagnostic;
    }

    protected function updateSkills()
    {
        try {
            foreach ($this->skills as $skill) {
                $idSkill = $this->executeQuery("SELECT id FROM skills WHERE `name` = :name;", ["name" => $skill])->fetch()["id"];
                if (!$idSkill) {
                    $skill = new ARSkill(["name" => $skill]);
                    $skill->create();
                    $idSkill = (int) $this->pdoConnection->lastInsertId();
                }
                $this->skillsId[] = $idSkill;

                // $sql = "INSERT INTO profiles_skills(profile_id, skill_id) VALUES (:profile_id, :skill_id)";
                // $this->executeQuery($sql, ["profile_id" => $this->id, "skill_id" => $idSkill]);

            }
            $this->syncPivot(
                'profiles_skills',
                'profile_id',
                'skill_id',
                $this->skillsId
            );
        } catch (Exception $e) {
            $this->rollBack();
            throw ($e);
        }
    }

    public function getSkills()
    {
        return $this->belongsToMany(ARSkill::class, "profiles_skills", "profile_id", "skill_id");
    }
}
