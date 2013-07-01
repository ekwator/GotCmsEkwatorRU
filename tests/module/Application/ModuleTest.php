<?php
/**
 * This source file is part of GotCms.
 *
 * GotCms is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * GotCms is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License along
 * with GotCms. If not, see <http://www.gnu.org/licenses/lgpl-3.0.html>.
 *
 * PHP Version >=5.3
 *
 * @category Gc_Tests
 * @package  ZfModules
 * @author   Pierre Rambaud (GoT) <pierre.rambaud86@gmail.com>
 * @license  GNU/LGPL http://www.gnu.org/licenses/lgpl-3.0.html
 * @link     http://www.got-cms.com
 */

namespace Application;

use Gc\Registry;
use Gc\Core\Config as CoreConfig;
use Gc\Layout\Model as LayoutModel;
use Gc\View\Stream;
use Zend\Db\TableGateway\Feature\GlobalAdapterFeature;
use Zend\Mvc\Router\RouteMatch;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-03-15 at 23:51:32.
 *
 * @group    ZfModules
 * @category Gc_Tests
 * @package  ZfModules
 */
class ModuleTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Install
     */
    protected $object;

    /**
     * @var Zend\Uri\Http
     */
    protected $uri;

    /**
     * @var Zend\Mvc\MvcEvent
     */
    protected $mvcEvent;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @return void
     */
    protected function setUp()
    {
        $this->object = new Module;
        $this->uri      = Registry::get('Application')->getRequest()->getUri();
        $this->mvcEvent = Registry::get('Application')->getMvcEvent();
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     *
     * @return void
     */
    protected function tearDown()
    {
        CoreConfig::setValue('force_frontend_ssl', 0);
        CoreConfig::setValue('force_backend_ssl', 0);
        unset($this->object);
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testOnBootstrap()
    {
        $oldAdapter       = GlobalAdapterFeature::getStaticAdapter();
        CoreConfig::setValue('debug_is_active', 1);
        CoreConfig::setValue('session_lifetime', 3600);
        CoreConfig::setValue('cookie_domain', 'got-cms.com');
        CoreConfig::setValue('session_handler', CoreConfig::SESSION_DATABASE);

        $this->assertNull($this->object->onBootstrap(Registry::get('Application')->getMvcEvent()));

        GlobalAdapterFeature::setStaticAdapter($oldAdapter);
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testPrepareException()
    {
        Stream::register();
        $layoutModel = LayoutModel::fromArray(
            array(
                'name' => 'Layout Name',
                'identifier' => 'Layout identifier',
                'description' => 'Layout Description',
                'content' => 'Layout Content'
            )
        );

        $layoutModel->save();
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('cms');
        $this->mvcEvent->setRouteMatch($routeMatch);
        CoreConfig::setValue('site_exception_layout', $layoutModel->getId());
        $this->assertNull($this->object->prepareException(Registry::get('Application')->getMvcEvent()));
        $layoutModel->delete();
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testCheckSslWithFrontendRoute()
    {
        CoreConfig::setValue('force_frontend_ssl', 1);
        CoreConfig::setValue('secure_frontend_base_path', 'https://got-cms.com');
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('cms');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $result    = $this->object->checkSsl($this->mvcEvent);
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->uri->setScheme($oldScheme);
        CoreConfig::setValue('secure_frontend_base_path', '');
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testCheckSslWithFrontendRouteAndAlreadyHttps()
    {
        CoreConfig::setValue('force_frontend_ssl', 1);
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('cms');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $this->uri->setScheme('https');
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
        $this->uri->setScheme($oldScheme);
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testCheckSslWithoutForceRoute()
    {
        CoreConfig::setValue('force_frontend_ssl', 0);
        $routeMatch = new RouteMatch(array());
        $routeMatch->setMatchedRouteName('cms');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $this->uri->setScheme('http');
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
        $this->uri->setScheme($oldScheme);
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testCheckSslWithithForceBackendRoute()
    {
        CoreConfig::setValue('force_backend_ssl', 0);
        $routeMatch = new RouteMatch(
            array(
                'module' => 'Config',
                'controller' => 'UserController',
                'action' => 'login',
            )
        );
        $routeMatch->setMatchedRouteName('config/user/login');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $this->assertNull($this->object->checkSsl($this->mvcEvent));
    }

    /**
     * Test
     *
     * @covers Application\Module
     *
     * @return void
     */
    public function testCheckSslWithBackendRoute()
    {
        CoreConfig::setValue('force_backend_ssl', 1);
        CoreConfig::setValue('secure_backend_base_path', 'https://got-cms.com');
        $routeMatch = new RouteMatch(
            array(
                'module' => 'Config',
                'controller' => 'UserController',
                'action' => 'login',
            )
        );
        $routeMatch->setMatchedRouteName('config/user/login');
        $this->mvcEvent->setRouteMatch($routeMatch);
        $oldScheme = $this->uri->getScheme();
        $result    = $this->object->checkSsl($this->mvcEvent);
        $this->assertInstanceOf('Zend\Http\PhpEnvironment\Response', $result);
        $this->uri->setScheme($oldScheme);
        CoreConfig::setValue('secure_backend_base_path', '');
    }
}
