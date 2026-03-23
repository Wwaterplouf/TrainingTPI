<?php

namespace App\Models;

use PDO;

class ARAnime extends ActiveRecord
{
    /**
     * Nom de la table associée au modèle.
     *
     * @var string
     */
    protected static $table = 'animes';

    /** @var int|null Identifiant unique du rôle (clé primaire). */
    public $id = null;

    public $anilist_id = '';

    public $englishTitle = '';

    public $originalTitle = '';

    public $siteUrl = '';

    public $description = '';

    public $largeCover = '';

    public $status = '';

    public $startDate = ''; 

    public $endDate = '';

    public $season = '';

    public $seasonYear = '';

    public $episodes = '';

    public $genres = [];

    /**
     * @param int|null $id
     * @param string $name
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
    }

    public function create()
    {

    }

    public function update()
    {

    }
}