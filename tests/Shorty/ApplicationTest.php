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

    public function testBasicRun()
    {
        $request = new Request('GET', '/posts');

        $application = new Application($request);
        $application->prepareResponse();
        $response = $application->getResponse();

        $this->assertEquals(404, $response->getStatusCode());
    }
}