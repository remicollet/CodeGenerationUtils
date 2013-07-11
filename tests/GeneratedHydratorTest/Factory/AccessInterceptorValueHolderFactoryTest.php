<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license.
 */

namespace GeneratedHydratorTest\Factory;

use PHPUnit_Framework_TestCase;
use GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory;
use GeneratedHydrator\Generator\ClassGenerator;
use GeneratedHydrator\Generator\Util\UniqueIdentifierGenerator;
use stdClass;

/**
 * Tests for {@see \GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory}
 *
 * @author Marco Pivetta <ocramius@gmail.com>
 * @license MIT
 */
class AccessInterceptorValueHolderFactoryTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    protected $inflector;

    /**
     * @var \GeneratedHydrator\Configuration|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $config;

    /**
     * {@inheritDoc}
     */
    public function setUp()
    {
        $this->config    = $this->getMock('GeneratedHydrator\\Configuration');
        $this->inflector = $this->getMock('GeneratedHydrator\\Inflector\\ClassNameInflectorInterface');
        $this
            ->config
            ->expects($this->any())
            ->method('getClassNameInflector')
            ->will($this->returnValue($this->inflector));
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory::__construct
     * @covers \GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory::createProxy
     */
    public function testWillSkipAutoGeneration()
    {
        $instance = new stdClass();

        $this->config->expects($this->any())->method('doesAutoGenerateProxies')->will($this->returnValue(false));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue('GeneratedHydratorTestAsset\\AccessInterceptorValueHolderMock'));

        $factory     = new AccessInterceptorValueHolderFactory($this->config);
        /* @var $proxy \GeneratedHydratorTestAsset\AccessInterceptorValueHolderMock */
        $proxy       = $factory->createProxy($instance, array('foo'), array('bar'));

        $this->assertInstanceOf('GeneratedHydratorTestAsset\\AccessInterceptorValueHolderMock', $proxy);
        $this->assertSame($instance, $proxy->instance);
        $this->assertSame(array('foo'), $proxy->prefixInterceptors);
        $this->assertSame(array('bar'), $proxy->suffixInterceptors);
    }

    /**
     * {@inheritDoc}
     *
     * @covers \GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory::__construct
     * @covers \GeneratedHydrator\Factory\AccessInterceptorValueHolderFactory::createProxy
     *
     * NOTE: serious mocking going on in here (a class is generated on-the-fly) - careful
     */
    public function testWillTryAutoGeneration()
    {
        $instance       = new stdClass();
        $proxyClassName = UniqueIdentifierGenerator::getIdentifier('bar');
        $generator      = $this->getMock('GeneratedHydrator\GeneratorStrategy\\GeneratorStrategyInterface');
        $autoloader     = $this->getMock('GeneratedHydrator\\Autoloader\\AutoloaderInterface');

        $this->config->expects($this->any())->method('doesAutoGenerateProxies')->will($this->returnValue(true));
        $this->config->expects($this->any())->method('getGeneratorStrategy')->will($this->returnValue($generator));
        $this->config->expects($this->any())->method('getProxyAutoloader')->will($this->returnValue($autoloader));

        $generator
            ->expects($this->once())
            ->method('generate')
            ->with(
                $this->callback(
                    function (ClassGenerator $targetClass) use ($proxyClassName) {
                        return $targetClass->getName() === $proxyClassName;
                    }
                )
            );

        // simulate autoloading
        $autoloader
            ->expects($this->once())
            ->method('__invoke')
            ->with($proxyClassName)
            ->will(
                $this->returnCallback(
                    function () use ($proxyClassName) {
                        eval(
                            'class ' . $proxyClassName
                            . ' extends \\GeneratedHydratorTestAsset\\AccessInterceptorValueHolderMock {}'
                        );
                    }
                )
            );

        $this
            ->inflector
            ->expects($this->once())
            ->method('getProxyClassName')
            ->with('stdClass')
            ->will($this->returnValue($proxyClassName));

        $this
            ->inflector
            ->expects($this->once())
            ->method('getUserClassName')
            ->with('stdClass')
            ->will($this->returnValue('GeneratedHydratorTestAsset\\LazyLoadingMock'));

        $factory     = new AccessInterceptorValueHolderFactory($this->config);
        /* @var $proxy \GeneratedHydratorTestAsset\AccessInterceptorValueHolderMock */
        $proxy       = $factory->createProxy($instance, array('foo'), array('bar'));

        $this->assertInstanceOf($proxyClassName, $proxy);
        $this->assertSame($instance, $proxy->instance);
        $this->assertSame(array('foo'), $proxy->prefixInterceptors);
        $this->assertSame(array('bar'), $proxy->suffixInterceptors);
    }
}