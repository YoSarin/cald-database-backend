<?php
namespace Tests\Functional\Model;

use App\Model\Token;

class ModelTest extends \Tests\Functional\BaseTestCase
{
    public function testEnrichSelectWorks()
    {
        $m = \App\Model\Token::enrichSelect(["token" => "token"]);

        $this->assertEquals(["AND" => ["token.valid_until[>]" => date("Y-m-d H:i:s", time()), "token" => "token"]], $m);
    }
}
