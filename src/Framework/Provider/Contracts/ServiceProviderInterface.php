<?php
/**
 * ServiceProvider.php.
 *
 * @author   Edwin Dayot
 */

namespace Shorty\Framework\Provider\Contracts;

/**
 * Class ServiceProvider.
 */
interface ServiceProviderInterface
{
    /**
     * Boots the service provider.
     *
     * @return mixed
     */
    public function boot();
}
