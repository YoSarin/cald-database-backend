<?php
namespace App\Model;

class Token extends \App\Model
{
    const TYPE_EMAIL_VERIFICATION = 'email_verification';
    const TYPE_LOGIN              = 'login';

    protected static $types = [
        self::TYPE_EMAIL_VERIFICATION,
        self::TYPE_LOGIN,
    ];

    protected static $defaultDurations = [
        self::TYPE_EMAIL_VERIFICATION => 60,
        self::TYPE_LOGIN => 60,
    ];

    protected static $table = "token";
    protected static $fields = ["id", "user_id", "token", "valid_until", "type"];

    public static function create($type, $userId, $duration = null)
    {
        $i = new self();
        $i->setType($type);
        $i->setUserId($userId);
        if ($duration === null) {
            $duration = self::$defaultDurations[$type];
        }
        $i->expireAfterMinutes($duration);

        return $i;
    }

    public function save()
    {
        \Respect\Validation\Validator::intVal()->notEmpty()->assert($this->getUserId());
        \Respect\Validation\Validator::regex('/^[\d]{4}-[\d]{2}-[\d]{2} [\d]{2}:[\d]{2}:[\d]{2}$/')
            ->assert($this->getValidUntil());
        \Respect\Validation\Validator::contains($this->getType())->assert(self::$types);

        if (empty($this->getToken())) {
            $this->setToken(self::generateToken($this->getUserId()));
        }

        $filter = [
            "AND" => [
                "user_id" => $this->getUserId(),
                "type" => $this->getType()
            ]
        ];
        if ($this->isNew() && self::exists($filter)) {
            var_dump(self::exists($filter));
            var_dump(self::load($filter));
            $other = self::load($filter)[0];
            $this->setId($other->getId());
            $this->setToken($other->getToken());
        }
        parent::save();
    }

    public static function generateToken($userId)
    {
        return hash("sha256", hash("sha256", hash("sha256", rand()) . time()) . $userId);
    }

    public function expireAfterMinutes($minutes)
    {
        $this->setValidUntil(date('Y-m-d H:i:s', strtotime('+ ' . (int)$minutes . ' minute')));
    }

    protected static function getExplicitCondtions()
    {
        return ["valid_until[>]" => date("Y-m-d H:i:s", time())];
    }
}
