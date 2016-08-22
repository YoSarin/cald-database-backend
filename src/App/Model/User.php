<?php
namespace App\Model;

use App\Exception\User\Duplicate;
use App\Model\User;

class User extends \App\Model
{
    const STATE_WAITING        = 'waiting_for_confirmation';
    const STATE_CONFIRMED      = 'confirmed';
    const STATE_BLOCKED        = 'blocked';
    const STATE_PASSWORD_RESET = 'password_reset';

    protected static $table = "user";
    protected static $fields = ["id", "email", "password", "state", "salt"];

    public static function create($email, $password)
    {
        $i = new self();
        $i->setEmail($email);
        $i->setPassword($password);
        $i->setState(self::STATE_WAITING);

        return $i;
    }

    public function save()
    {
        if ($this->isNew() && self::exists(["email" => $this->getEmail()])) {
            throw new Duplicate("User already exists");
        }
        parent::save();
    }

    public function canLogin()
    {
        return $this->getState() == self::STATE_CONFIRMED;
    }

    public function setPassword($password)
    {
        if (empty($this->getSalt())) {
            $this->generateSalt();
        }
        parent::setPassword($this->hash($password));
    }

    public function verifyPassword($password)
    {
        return $this->getPassword() == $this->hash($password);
    }

    private function hash($password)
    {
        return hash("sha256", hash("sha256", hash("sha256", $password) . $this->getSalt()) . $this->getEmail());
    }

    private function generateSalt()
    {
        $this->setSalt(md5(md5(md5(rand()) . time()) . $this->getEmail()));
    }
}
