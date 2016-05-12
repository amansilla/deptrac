<?php

namespace SensioLabs\Deptrac\Tests\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use SensioLabs\Deptrac\Command\SelfUpdateCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SelfUpdateCommandTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $httpClient;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $filesystem;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $input;

    /**
     * @var \PHPUnit_Framework_MockObject_MockObject
     */
    private $output;

    protected function setUp()
    {
        $this->httpClient = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $this->filesystem = $this->getMock(Filesystem::class);
        $this->input = $this->getMock(InputInterface::class);
        $this->output = $this->getMock(OutputInterface::class);
        parent::setUp();
    }

    public function testGetPhar()
    {
        $command = new SelfUpdateCommand($this->filesystem, $this->httpClient);

        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willThrowException(new RequestException('', $this->getMock(RequestInterface::class)))
        ;

        $res = $command->run($this->input, $this->output);
        $this->assertSame(1, $res);
    }
}
