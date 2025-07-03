<?php

namespace App\Tests\Integration\Application\Service;

use App\Application\Service\ProduceService;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ProduceImporterTest extends  KernelTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        self::bootKernel();
    }

    public function testServiceIsAvailable(): void
    {
        $service = static ::getContainer()->get(ProduceService::class);
        $this->assertNotNull($service);
    }
}