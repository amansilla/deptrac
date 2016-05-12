<?php

namespace SensioLabs\Deptrac\Tests\Command;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use SensioLabs\Deptrac\Command\SelfUpdateCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Exception\IOException;
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

    /**
     * @var SelfUpdateCommand
     */
    private $command;

    protected function setUp()
    {
        $this->httpClient = $this->getMockBuilder(Client::class)->setMethods(['get'])->getMock();
        $this->filesystem = $this->getMock(Filesystem::class);
        $this->input = $this->getMock(InputInterface::class);
        $this->output = $this->getMock(OutputInterface::class);
        $this->command = new SelfUpdateCommand($this->filesystem, $this->httpClient);
        parent::setUp();
    }

    public function testGetPharFail()
    {
        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willThrowException(new RequestException('', $this->getMock(RequestInterface::class)))
        ;

        $res = $this->command->run($this->input, $this->output);
        $this->assertSame(1, $res);
    }

    public function testWriteTmpPharFail()
    {
        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willReturn(new Response());

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($tmpFilename)
            ->willReturn(false);

        $res = $this->command->run($this->input, $this->output);
        $this->assertSame(1, $res);
    }

    public function testRenamePharFail()
    {
        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willReturn(new Response());

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($tmpFilename)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($tmpFilename)
            ->willThrowException(new IOException(''));

        $res = $this->command->run($this->input, $this->output);
        $this->assertSame(1, $res);
    }

    public function testChmodPharFail()
    {
        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willReturn(new Response());

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($tmpFilename)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($tmpFilename);

        $this->filesystem->expects($this->once())
            ->method('chmod')
            ->willThrowException(new IOException(''));

        $res = $this->command->run($this->input, $this->output);
        $this->assertSame(1, $res);
    }

    public function testUpdateSuccess()
    {
        $command = new SelfUpdateCommand($this->filesystem, $this->httpClient);

        $tmpFilename = sys_get_temp_dir() . '/deptrac.phar';

        $this->httpClient->expects($this->once())
            ->method('get')
            ->with('http://get.sensiolabs.de/deptrac.phar', [RequestOptions::SINK => $tmpFilename])
            ->willReturn(new Response('200', [], 'foo'));

        $this->filesystem->expects($this->once())
            ->method('exists')
            ->with($tmpFilename)
            ->willReturn(true);

        $this->filesystem->expects($this->once())
            ->method('rename')
            ->with($tmpFilename);

        $this->filesystem->expects($this->once())
            ->method('chmod');

        $res = $command->run($this->input, $this->output);
        $this->assertSame(0, $res);
        $this->assertFileEquals('foo', $tmpFilename);
    }
}
