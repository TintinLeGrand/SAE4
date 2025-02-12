<?php

namespace App\Models;

use Database\Factories\PersonneFactory;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\Access\Authorizable;

/** Stores a person.
 * @property int $PER_id
 * @property string $PER_nom
 * @property string $PER_prenom
 * @property string $PER_pass
 * @property string $PER_email
 * @property string $PER_remember_token
 * @property boolean $PER_active
 * @property Autorisations $autorisations
 * @property Adherent $adherent
 * @method static Personne find(int $id)
 * @method static int count($fields)
 * @method static Builder where($col, $op = null, $value = null)
 * @method static Builder whereHas($col, $op = null)
 * @method static PersonneFactory factory(...$parameters)
 */
class Personne extends Model implements Authenticatable
{
    use HasFactory;
    use Authorizable;
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'PLO_PERSONNES';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'PER_id';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function toArray(): array
    {
        $res = [
            "id"=>$this->PER_id,
            "nom"=>$this->PER_nom,
            "prenom"=>$this->PER_prenom,
            "email"=>$this->PER_email,
            'actif' =>$this->PER_active
        ];
        if ($this->relationLoaded("autorisations") && isset($this->autorisations))
            return array_merge($res, [
                "directeur_de_section" => $this->autorisations->AUT_directeur_section,
                "securite_de_surface" => $this->autorisations->AUT_securite_surface,
                "pilote" => $this->autorisations->AUT_pilote,
                "secretaire" => $this->autorisations->AUT_secretaire
            ]);
        return $res;
    }

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'PER_active' => 'boolean'
    ];

    // Relationships

    public function autorisations(): HasOne
    {
        return $this->hasOne(Autorisations::class, "AUT_personne", "PER_id");
    }

    public function adherent(): HasOne
    {
        return $this->hasOne(Adherent::class, "ADH_id", "PER_id");
    }

    // Part for implementing interface Authenticatable

    /** Get hashed password from database */
    public function getAuthPassword(): string
    {
        return $this->PER_pass;
    }

    public function getAuthIdentifierName(): string
    {
        return "PER_id";
    }

    public function getAuthIdentifier(): int
    {
        return $this->PER_id;
    }

    public function getRememberToken(): ?string
    {
        return $this->PER_remember_token;
    }

    public function setRememberToken($value)
    {
        $this->PER_remember_token = $value;
        $this->save();
    }

    public function getRememberTokenName(): string
    {
        return "PER_remember_token";
    }

    public function isDirector(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_directeur_section;
    }

    public function isSecretary(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_secretaire;
    }

    public function isSurfaceSecurity(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_securite_surface;
    }

    public function isPilot(): bool {
        return $this->autorisations()->exists() &&
            $this->autorisations->AUT_pilote;
    }

    public function isAdherent(): bool
    {
        return $this->adherent()->exists();
    }

    public function getId(): int {
        return $this->PER_id;
    }

    public function getText(): string {
        return "$this->PER_nom $this->PER_prenom";
    }

}
