<?php
/**
 * Handler.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Exceptions;

use GuzzleHttp\Psr7\Response;
use Shorty\Framework\Routing\Exceptions\HTTPException;

/**
 * Class Handler.
 */
class Handler
{
    /**
     * Renders the exception.
     *
     * @param $exception
     *
     * @return \GuzzleHttp\Psr7\Response|null
     */
    public function render($exception)
    {
        if ($exception instanceof HTTPException) {
            return new Response($exception->getCode(), [], $exception->getMessage());
        }

        return null;
    }
}
