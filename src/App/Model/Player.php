<?php
namespace App\Model;

use \App\Exception\Database\Duplicate;
use App\Model\PlayerAtTeam;

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

    protected static $fields = ["id", "first_name", "last_name", "birth_date", "created_at", "email", "phone", "sex", "state", "nationality_id", "gdpr_consent", "personal_identification_number"];

    public function getFullName()
    {
        return $this->getFirstName() . ' ' . $this->getLastName();
    }

    public function getHomeTeam()
    {
    }

    public static function create($firstName, $lastName, $sex, $email = null, $birthDate = null, $phone = null, $state = self::STATE_ACTIVE, $nationalityID = null, $gdprConsent = false, $personalIdentificationNumber = null)
    {
        $i = new self();
        $i->setFirstName($firstName);
        $i->setLastName($lastName);
        $i->setSex($sex);
        $i->setState($state);
        $i->setEmail($email);
        $i->setPhone($phone);
        $i->setBirthDate($birthDate);
        $i->setNationalityId($nationalityID);
        $i->setGdprConsent($gdprConsent);
        $i->setCreatedAt(date("Y-m-d H:i:s", time()));
        $i->setPersonalIdentificationNumber($personalIdentificationNumber);
        return $i;
    }

    public static function playersAtTournament($tournamentId)
    {
    }

    private static function mastersMaleAge($category)
    {
        switch ($category) {
            case "m": return 33;
            case "gm": return 40;
            case "ggm": return 48;
            default: return null;
        }
    }

    private static function mastersFemaleAge($category)
    {
        return self::mastersMaleAge($category) - 3;
    }

    private static function juniorMaleAge($category)
    {
        return (int)str_replace("u", "", $category);
    }

    private static function juniorFemaleAge($category)
    {
        return self::juniorMaleAge($category);
    }

    public static function categoryToBirthDateCondition($category, $year)
    {
        $comparison = ">=";
        $femaleAge = $maleAge = null;
        
        if (\stripos($category, "u") === 0) {
            $comparison = ">=";
            $femaleAge = $maleAge = self::juniorMaleAge($category);
        } else {
            $comparison = "<=";
            $maleAge = self::mastersMaleAge($category);
            $femaleAge = self::mastersFemaleAge($category);
        }

        $maleBirthYear = $year - $maleAge;
        $femaleBirthYear = $year - $femaleAge;

        return [
            "OR" => [
                "AND # male" => ["sex" => "male", "birth_date[$comparison]" => "$maleBirthYear-01-01 00:00:00"],
                "AND # female" => ["sex" => "female", "birth_date[$comparison]" => "$femaleBirthYear-01-01 00:00:00"]
            ]
        ];
    }

    public static function listPlayersInCategory($category, $year, $gender, $mayInactiveSeasons)
    {
        $lastActiveSeason = $year - $mayInactiveSeasons;

        $condition = ["AND" => []];
        $condition["AND"] = self::categoryToBirthDateCondition($category, $year);
        $condition["AND"]["tournament.deleted"] = false;
        $condition["AND"]["season.start[>=]"] = "${lastActiveSeason}-01-01 00:00:00";
        $condition["AND"]["birth_date[<>]"] = "0000-00-00 00:00:00";
        $condition["AND"]["birth_date[<>]"] = null;
        if ($gender != null && in_array($gender, self::$sexList)) {
            $condition["AND"]["sex"] = $gender;
        }

        // return $condition;

        $data = self::load(
            $condition, null, 0, [
                "[><]player_at_roster(player_at_roster)" => ["player.id" => "player_id"],
                "[><]roster(roster)" => ["player_at_roster.roster_id" => "id"],
                "[><]tournament_belongs_to_league_and_division(tournament_belongs_to_league_and_division)" => ["roster.tournament_belongs_to_league_and_division_id" => "id"],
                "[><]tournament(tournament)" => ["tournament_belongs_to_league_and_division.tournament_id" => "id"],
                "[><]season(season)" => ["tournament.season_id" => "id"]
            ]
        );

        $existingIds = [];

        return array_filter(
            $data,
            function ($player) use (&$existingIds) {
                if (in_array($player->getId(), $existingIds)) {
                    return false;
                }
                $existingIds[] = $player->getId();
                return true;
            }
        );
    }

    public static function extendedJoins(&$joins = [], $alias = "") {
        $joins["[>]player_at_team(player_at_team___player)"] = ["id" => "player_id"];
        PlayerAtTeam::extendedJoins($joins, "player_at_team___player");
        parent::extendedJoins($joins, $alias);
    }

    public function getExtendedData(&$loaded = array()) {
        return parent::getExtendedData($loaded);
    }

}
