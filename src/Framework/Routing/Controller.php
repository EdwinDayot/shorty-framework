<?php
/**
 * Controller.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Routing;

/**
 * Class Controller.
 */
class Controller
{
    /**
     * Middlewares to add.
     *
     * @param string|string[] $name
     */
    public function middleware($name)
    {
        if (is_string($name)) {
            app()->addRouteMiddleware($name);
        } else if (is_array($name)) {
            foreach ($name as $middlewareName) {
                app()->addRouteMiddleware($middlewareName);
            }
        }
    }
}
