<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

#[AsEventListener(
    event: 'kernel.exception',
    )]
class EnumMappingExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $e = $event->getThrowable();

        // Symfony serializer throws this if it can't map a scalar into a backed enum:
        if ($e instanceof   UnprocessableEntityHttpException)
        {
            $event->setResponse(new JsonResponse([
                'error' =>"{$e->getMessage()}",
            ], 400));
        }
    }
}