<?php

namespace App\Models;

use App\Models\PDOSingleton;
use PDO;
use ReflectionClass;

abstract class ActiveRecord
{
    protected $pdoConnection;

    protected static $table;

    protected $id = null;

    public function __construct(array $data = [])
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }

        $this->pdoConnection = PDOSingleton::getInstance()->getConnection();
    }

    protected function executeQuery($sql, $params = [])
    {
        $stmt = $this->pdoConnection->prepare($sql);

        $stmt->execute($params);

        return $stmt;
    }

    // public function toString()
    // {
    //     $properties = get_object_vars($this); // Récupère les propriétés de l'objet courant
    //     $output = [];
    //     foreach ($properties as $name => $value) {
    //         if (is_object($value)) {
    //             continue; // Ignore les propriétés qui sont des objets
    //         }
    //         $output[] = "[$name : $value]";
    //     }
    //     return sprintf(
    //         "** %s ** : %s",
    //         get_class($this),
    //         implode(' ', $output)
    //     );
    // }

    abstract public function create();
    abstract public function update();

    public function save()
    {
        $reflection = new \ReflectionClass($this);
        if ($reflection->getProperty('id')->getValue() == null) {
            $this->create();
        } else {
            $this->update();
        }
    }

    public static function findAll()
    {
        $pdoInstance = PDOSingleton::getInstance();
        $stmt = $pdoInstance->getConnection()->query("SELECT * FROM " . static::$table);
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($datas as $row) {
            $result[] = new static($row);
        }
        return $result;
    }

    public static function findById($id)
    {
        // Construction de la requête SQL pour récupérer un enregistrement spécifique
        $sql = "SELECT * FROM " . static::$table . " WHERE id = :id";
        // Récupère une instance de PDO via le singleton
        $pdoInstance = PDOSingleton::getInstance();
        // Prépare la requête pour éviter les injections SQL
        $stmt = $pdoInstance->getConnection()->prepare($sql);
        // Exécute la requête en liant l'identifiant
        $stmt->execute(['id' => $id]);
        // Récupère l'enregistrement sous forme de tableau associatif
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($data) {
            // Si un enregistrement est trouvé, utilise un constructeur flexible pour créer l'instance
            return new static($data);
        }
        // Retourne null si aucun enregistrement n'est trouvé
        return null;
    }

    public function delete()
    {
        $reflection = new \ReflectionClass($this);
        $property = $reflection->getProperty('id');
        if (!$property->isInitialized($this) || $property->getValue($this) === null) {
            throw new \Exception("Impossible de supprimer : 'id' n'est pas défini.");
        }
        $this->beginTransaction();
        try {
            // Appel du hook : permet aux classes enfants d'intervenir
            $this->beforeDelete();
            // Suppression de l'enregistrement principal
            $sql = "DELETE FROM " . static::$table . " WHERE id = :id";
            $this->executeQuery($sql, ['id' => $property->getValue($this)]);
            $this->commit();
            return true;
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    public function beginTransaction()
    {
        if (!$this->pdoConnection->inTransaction()) {
            $this->pdoConnection->beginTransaction();
        }
    }

    public function commit()
    {
        if ($this->pdoConnection->inTransaction()) {
            $this->pdoConnection->commit();
        }
    }

    public function rollBack()
    {
        if ($this->pdoConnection->inTransaction()) {
            $this->pdoConnection->rollBack();
        }
    }

    // protected function hasOne($relatedClass, $foreignKey)
    // {
    //     if (!isset($this->$foreignKey))
    //     {
    //         return null;
    //     }
    //     return $relatedClass::find($this->$foreignKey);
    // }

    // protected function hasMany($relatedClass, $foreignKey)
    // {
    //     $relatedTable = $relatedClass::$table;
    //     $sql = "SELECT $relatedTable.* FROM $relatedTable WHERE $relatedTable.$foreignKey = :id";
    //     $reflection = new \ReflectionClass($this);
    //     $property = $reflection->getProperty('id');

    //     $stmt = $this->pdoConnection->prepare($sql);
    //     $this->executeQuery($sql, ['id' => $property->getValue($this)]);
    //     $results = [];
    //     while($row = $stmt->fetch())
    //     {
    //         $results[] = new $relatedClass($row);
    //     }
    //     return $results;
    // }

    protected function belongsToMany($relatedClass, $pivotTable, $pivotSelfKey, $pivotRelatedKey)
    {
        // Si l'objet courant n'est pas encore persisté, il ne peut pas avoir de relations n-m
        if ($this->id === null) {
            return [];
        }
        // Nom de la table liée (ex: motclefs)
        $relatedTable = $relatedClass::$table;
        // Construction de la requête SQL via la table pivot
        $sql = "SELECT r.* FROM $relatedTable r
        JOIN $pivotTable p ON r.id = p.$pivotRelatedKey
        WHERE p.$pivotSelfKey = :id;";
        // Exécution de la requête en utilisant l'id de l'objet courant
        $stmt = $this->executeQuery($sql, ['id' => $this->id]);
        $datas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        // Hydratation : chaque ligne devient un objet Active Record de la classe liée
        $result = [];
        foreach ($datas as $row) {
            $result[] = new $relatedClass($row);
        }
        return $result;
    }

    protected function syncPivot($pivotTable, $pivotSelfKey, $pivotRelatedKey, array $relatedIds)
    {
        if ($this->id === null) {
            throw new \Exception("syncPivot impossible : l'objet n'est pas persisté.");
        }
        $this->beginTransaction();
        try {
            // Supprimer les anciennes associations
            $sqlDelete = "DELETE FROM $pivotTable WHERE $pivotSelfKey = :id;";
            $this->executeQuery($sqlDelete, ['id' => $this->id]);
            // Dédupliquer les IDs avant insertion
            $uniqueIds = array_unique($relatedIds);
            // Réinsérer les nouvelles associations
            $sqlInsert = "INSERT INTO $pivotTable ($pivotSelfKey, $pivotRelatedKey) VALUES (:id, :rid);";
            foreach ($uniqueIds as $rid) {
                $this->executeQuery($sqlInsert, ['id' => $this->id, 'rid' => $rid]);
            }
            $this->commit();
        } catch (\Throwable $e) {
            $this->rollBack();
            throw $e;
        }
    }

    protected function beforeDelete() {}
}
