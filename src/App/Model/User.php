<?php
namespace App\Model;

use App\Exception\Database\Duplicate;
use App\Model\User;

class User extends \App\Model
{
    const STATE_WAITING        = 'waiting_for_confirmation';
    const STATE_CONFIRMED      = 'confirmed';
    const STATE_BLOCKED        = 'blocked';
    const STATE_PASSWORD_RESET = 'password_reset';

    protected static $table = "user";
    protected static $fields = ["id", "email", "password", "state", "salt", "login"];

    public static function create($login, $password, $email)
    {
        $i = new self();
        $i->setEmail($email);
        $i->setLogin($login);
        $i->setPassword($password);
        $i->setState(self::STATE_WAITING);

        return $i;
    }

    protected function onSaveValidation()
    {
        if ($this->isNew() && self::exists(["login" => $this->getLogin()])) {
            throw new Duplicate("User already exists");
        }
    }

    public function canLogin()
    {
        return $this->getState() == self::STATE_CONFIRMED;
    }

    public function setPassword($password)
    {
        $this->generateSalt();
        parent::setPassword($this->hash($password));
    }

    public function verifyPassword($password)
    {
        return $this->getPassword() == $this->hash($password);
    }

    public static function loggedUser($token)
    {
        $t = Token::load(['token' => $token, 'type' => Token::TYPE_LOGIN]);
        if (count($t) != 1) {
            return null;
        }
        $user = static::load(['id' => $t[0]->getUserId()]);
        if (count($user) < 1 || !$user[0]->canLogin()) {
            return null;
        }

        return $user[0];
    }

    private function hash($password)
    {
        return hash("sha256", hash("sha256", hash("sha256", strtoupper(md5($password))) . $this->getSalt()) . $this->getLogin());
    }

    private function generateSalt()
    {
        $this->setSalt(md5(md5(md5(rand()) . time()) . $this->getLogin()));
    }

    public function getData()
    {
        $d = $this->data;
        unset($d['password']);
        unset($d['salt']);
        return $d;
    }
}
