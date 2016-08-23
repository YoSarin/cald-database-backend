<?php
namespace Tests\Functional;

use App\Model\Token;

class ModelTest extends BaseTestCase
{
    /**
     * Test that the index route returns a rendered response containing the text 'SlimFramework' but not a greeting
     */
    public function testModelWorks()
    {
        $m = \App\Model\Token::enrichSelect(["token" => "token"]);

        $this->assertEquals(["AND" => ["valid_until[>]" => date("Y-m-d H:i:s", time()), "token" => "token"]], $m);
    }
}
