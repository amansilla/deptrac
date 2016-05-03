<?php

namespace SensioLabs\Deptrac\Updater;

use Humbug\SelfUpdate\Strategy\ShaStrategy;

class DeptracStrategy extends ShaStrategy
{
    const PHAR_URL = 'http://get.sensiolabs.de/deptrac.phar';

    const VERSION_URL = 'http://localhost:8080/deptrac.version';

    public function __construct()
    {
        $this->setPharUrl(self::PHAR_URL);
        $this->setVersionUrl(self::VERSION_URL);
    }
}
