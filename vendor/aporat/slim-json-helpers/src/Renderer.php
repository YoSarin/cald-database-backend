<?php
namespace JsonHelpers;

use \Psr\Http\Message\ResponseInterface;
use \Slim\Http\Response;

/**
 * JsonRenderer
 *
 * Render JSON view into a PSR-7 Response object
 */
class Renderer
{
  /**
   *
   * @param Response $response
   * @param array $data
   * @param int $status
   *
   * @return ResponseInterface
   */
  public function render(Response $response, array $data = [], $status = 200)
  {
    return $response->withJson($data, $status);
  }
}