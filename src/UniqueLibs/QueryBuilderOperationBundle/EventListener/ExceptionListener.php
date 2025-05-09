<?php

namespace UniqueLibs\QueryBuilderOperationBundle\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\Response;
use UniqueLibs\QueryBuilderOperationBundle\Exception\InvalidSearchFilterSyntaxException;

class ExceptionListener
{
    /**
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        $exception = $event->getException();

        if ($exception instanceof InvalidSearchFilterSyntaxException) {
            $response = new JsonResponse(array(
                'code' => Response::HTTP_BAD_REQUEST,
                'message' => $exception->getMessage(),
            ));
            $response->setStatusCode(Response::HTTP_BAD_REQUEST);

            $event->setResponse($response);
        }
    }
}
