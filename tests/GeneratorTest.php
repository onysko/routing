<?php
/**
 * Created by PhpStorm.
 * User: VITALYIEGOROV
 * Date: 28.10.15
 * Time: 11:28
 */
namespace samsonframework\routing\tests;

use samsonframework\routing\Generator;
use samsonframework\routing\generator\Structure;
use samsonframework\routing\Route;
use samsonframework\routing\RouteCollection;


class GeneratorTest extends \PHPUnit_Framework_TestCase
{
    protected $routerLogicFunction;

    /** Routing function wrapper */
    public function routerLogic($path, $method)
    {
        $path = rtrim(strtok(ltrim($path, '/'), '?'), '/');
        return call_user_func($this->routerLogicFunction, $path, $method);
    }
    
    public function testGeneration()
    {
        // Create routes descriptions with identifiers
        $routeArray = array(
            'main-page' => array('GET', '/', '/'),
            'inner-page' => array('GET', '/{page}', '/text-page', array('page' => 'text-page')),
            'user-home-without-slash' => array('GET', '/user', '/user'),
            'test-two-similar-fixed' => array('GET', '/userlist', '/userlist'),
            'test-two-params-at-end' => array('GET', '/userlist/{group}/{action}', '/userlist/123/kill', array('group'=>'123', 'action' => 'kill')),
            'user-winners-slash' => array('GET', '/user/winners/', '/user/winners/'),
            'user-by-id' => array('GET', '/user/{id}', '/user/123'),
            'user-by-gender-age' => array('GET', '/user/{gender:male|female}/{age}', '/user/male/19d', array('gender' => 'male', 'age' => '19d')),
            'user-by-gender-age-filtered' => array('GET', '/user/{gender:male|female}/{age:[0-9]+}', '/user/female/8', array('gender' => 'female', 'age' => '8')),
            'user-by-id-form' => array('GET', '/user/{id}/form', '/user/123/form', array('id' => '123')),
            'user-by-id-friends' => array('GET', '/user/{id}/friends', '/user/123/friends', array('id' => '123')),
            'user-by-id-friends-with-id' => array('GET', '/user/{id}/friends/{groupid}', '/user/123/friends/1', array('id' => '123', 'groupid' => 1)),
            'entity-by-id-form' => array('GET', '/{entity}/{id}/form', '/friend/123/form', array('entity' => 'friend', 'id' => '123')),
            'entity-by-id-form-test' => array('GET', '/{id}/test/{page:\d+}', '/123/test/1', array('id' => '123', 'page' => '1')),
            'two-params' => array('GET', '/{num}/{page:\d+}', '/123/23123', array('num' => '123', 'page' => '23123')),
            'user-by-id-node' => array('GET', '/user/{id}/n"$ode', '/user/123/n"$ode', array('id' => '123')),
            'user-by-id-node-with-id' => array('GET', '/user/{id}/n"$ode/{param}', '/user/123/n"$ode/321', array('id' => '123', 'param' => '321')),
            //'user-with-empty' => array('GET', '/user/{id}/get', '/user//get'),
            'user-post-by-id' => array('POST', '/user/{id}/save', '/user/123/save', array('id' => '123'))
        );

        // Create routes collection
        $routes = new RouteCollection();
        foreach ($routeArray as $identifier => $routeData) {
            $routes->add(new Route($routeData[1], array($this, 'baseCallback'), $identifier, $routeData[0]));
        }

        $generator = new Structure($routes, new \samsonphp\generator\Generator());
        $this->routerLogicFunction = '__router'.rand(0, 9999);
        $routerLogic = $generator->generate($this->routerLogicFunction);

        // Create real file for debugging
        file_put_contents(__DIR__.'/testLogic.php', '<?php '."\n".$routerLogic);
        require(__DIR__.'/testLogic.php');

        foreach ($routeArray as $identifier => $routeData) {
            $result = $this->routerLogic($routeData[2], $routeData[0]);
            $this->assertEquals($identifier, $result[0]);
            if (isset($routeData[3])) {
                foreach ($routeData[3] as $key => $value) {
                    $this->assertArrayHasKey($key, $result[1]);
                    $this->assertEquals($value, $result[1][$key]);
                }
            }
        }
    }
}
