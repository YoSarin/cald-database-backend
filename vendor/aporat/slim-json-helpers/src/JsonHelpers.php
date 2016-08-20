<?php
namespace JsonHelpers;

use Interop\Container\ContainerInterface;
use JsonHelpers\Renderer as JsonRenderer;
use InvalidArgumentException;

class JsonHelpers {

    /**
     * Container
     *
     * @var ContainerInterface
     */
    private $container;

    /********************************************************************************
     * Constructor
     *******************************************************************************/

    /**
     * Create new application
     *
     * @param ContainerInterface|array $container Either a ContainerInterface or an associative array of application settings
     * @throws InvalidArgumentException when no container is provided that implements ContainerInterface
     */
    public function __construct($container = null)
    {
        if (!$container instanceof ContainerInterface) {
            throw new InvalidArgumentException('Expected a ContainerInterface');
        }
        $this->container = $container;
    }

    /**
     * register json response view
     */
    function registerResponseView()
    {

        $this->container['view'] = function ($c) {
            $view = new JsonRenderer();

            return $view;
        };
    }

    /**
     * register all error handler (not found, not allowed, and generic error handler)
     */
    function registerErrorHandlers() {

        $this->container['notAllowedHandler'] = function ($c) {
            return function ($request, $response, $methods) use ($c) {

                $view = new JsonRenderer();
                return $view->render($response,
                    ['error_code' => 'not_allowed', 'error_message' => 'Method must be one of: ' . implode(', ', $methods), 405]
                );
            };
        };

        $this->container['notFoundHandler'] = function ($c) {
            return function ($request, $response) use ($c) {
                $view = new JsonRenderer();

                return $view->render($response, ['error_code' => 'not_found', 'error_message' => 'Not Found'], 404);
            };
        };

        $this->container['errorHandler'] = function ($c) {
            return function ($request, $response, $exception) use ($c) {

                $settings = $c->settings;
                $view = new JsonRenderer();

                $error_code = 500;
                if (is_numeric($exception->getCode()) && $exception->getCode() > 300  && $exception->getCode() < 600) {
                    $error_code = $exception->getCode();
                }

                if ($settings['displayErrorDetails'] == true) {
                    $data = [
                        'error_code' => $error_code,
                        'error_message' => $exception->getMessage(),
                        'file' => $exception->getFile(),
                        'line' => $exception->getLine(),
                        'trace' => explode("\n", $exception->getTraceAsString()),
                    ];
                } else {
                    $data = [
                        'error_code' => $error_code,
                        'error_message' => $exception->getMessage()
                    ];
                }

                return $view->render($response, $data, $error_code);
            };
        };
    }
}