<?php

namespace SensioLabs\Deptrac\OutputFormatter;

use SensioLabs\Deptrac\DependencyContext;
use Symfony\Component\Console\Output\OutputInterface;

class JunitOutputFormatter implements OutputFormatterInterface
{
    const FORMATTER_NAME = 'junit';

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return self::FORMATTER_NAME;
    }

    /**
     * @inheritdoc
     */
    public function configureOptions()
    {
        return [

        ];
    }

    /**
     * Renders the final result.
     *
     * @param DependencyContext    $dependencyContext
     * @param OutputInterface      $output
     * @param OutputFormatterInput $outputFormatterInput
     *
     * @return mixed
     */
    public function finish(
        DependencyContext $dependencyContext,
        OutputInterface $output,
        OutputFormatterInput $outputFormatterInput
    ) {
        $xml = new \SimpleXmlElement('<testsuites></testsuites>');
        foreach ($dependencyContext->getViolations() as $violations) {
            $xml->
        }

        return ;
    }
}
