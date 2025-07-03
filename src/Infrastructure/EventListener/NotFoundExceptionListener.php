<?php

namespace App\Infrastructure\EventListener;

use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

#[AsEventListener(
    event: 'kernel.exception',
)]
class NotFoundExceptionListener
{
    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if ($exception instanceof NotFoundHttpException) {
            $data = [
                'error' => 'Not Found',
                'message' => $exception->getMessage(),
            ];

            $response = new JsonResponse($data, $exception->getStatusCode());
            $event->setResponse($response);
        }
    }
}
