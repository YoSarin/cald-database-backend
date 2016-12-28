<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;

class Player extends \App\Model
{
    const STATE_ACTIVE = 'active';
    const STATE_INACTIVE = 'inactive';
    const STATE_DELETED = 'deleted';

    const SEX_MALE = 'male';
    const SEX_FEMALE = 'female';

    protected static $stateList = [
        self::STATE_ACTIVE,
        self::STATE_INACTIVE,
        self::STATE_DELETED,
    ];

    protected static $sexList = [
        self::SEX_MALE,
        self::SEX_FEMALE,
    ];

    protected static $fields = ["id", "first_name", "last_name", "birth_date", "created_at", "email", "phone", "sex", "state"];

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public static function create($firstName, $lastName, $sex, $email = null, $birthDate = null, $phone = null, $state = self::STATE_ACTIVE)
    {
        $i = new self();
        $i->setFirstName($firstName);
        $i->setLastName($lastName);
        $i->setSex($sex);
        $i->setState($state);
        $i->setEmail($email);
        $i->setPhone($phone);
        $i->setBirthDate($birthDate);
        $i->setCreatedAt(date("Y-m-d H:i:s", time()));

        return $i;
    }

    public static function loadPlayersParticipatingAtTLD($tlds)
    {
        return static::load(["tournament_belongs_to_league_and_division.id" => $tlds], null, 0, [
            "[><]player_at_roster" => ["player.id" => "player_id"],
            "[><]roster" => ["player_at_roster.roster_id" => "id"],
            "[><]tournament_belongs_to_league_and_division" => ["roster.tournament_belongs_to_league_and_division_id" => "id"]
        ]);
    }
}
