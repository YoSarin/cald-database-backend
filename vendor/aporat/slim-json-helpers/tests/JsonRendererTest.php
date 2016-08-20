<?php
use JsonHelpers\Renderer as Renderer;
use \Slim\Http\Response;


class JsonRendererTest extends \PHPUnit_Framework_TestCase
{
    public function testValidConstuctor()
    {
        $jsonRenderer = new Renderer();

        $this->assertInstanceOf('\JsonHelpers\Renderer', $jsonRenderer);
    }

    public function testValidResponse()
    {
        $jsonRenderer = new Renderer();

        $response = new Response();
        $response = $jsonRenderer->render($response, ['status' => 'ok'], 200);

        $this->assertTrue($response->getStatusCode() == 200);
        $this->assertTrue($response->getBody() == json_encode(['status' => 'ok']));
    }

}
