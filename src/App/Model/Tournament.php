<?php
namespace App\Model;

class Tournament extends \App\Model
{
    protected static $fields = ["id", "name", "date", "location", "duration", "season_id"];
}
