<?php
/**
 * Created by PhpStorm.
 * User: heyden
 * Date: 25/10/17
 * Time: 21:06
 */

namespace Tests\Shorty;


use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use Shorty\Framework\Application;

class ApplicationTest extends TestCase
{

    /**
     * Test the basic application
     */
    public function testBasicRun()
    {
        $request = new Request('GET', '/posts');

        $application = new Application($request);

        $router = $application->getRouter();
        $router->get('posts', function () {
            return 'posts';
        });

        $response = $application->getResponse();

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('posts', (string) $response->getBody());
    }

    /**
     * Test for the NotFoundException
     */
    public function testNotFoundRun()
    {
        $request = new Request('GET', '/posts');

        $application = new Application($request);
        $response = $application->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}