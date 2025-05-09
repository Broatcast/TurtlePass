<?php

namespace Main\AppBundle\Controller;

use JMS\Serializer\SerializationContext;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportController extends Controller
{
    /**
     * @Route("/csv-export")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     *
     * @return \Symfony\Component\HttpFoundation\Response|StreamedResponse
     */
    public function indexAction(Request $request)
    {
        $form = $this->createFormBuilder()->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $passwordManager = $this->get('main_password.services.password_manager');
            $encryptor = $this->get('ambta_doctrine_encrypt.encryptor');
            $serializer = $this->get('jms_serializer');

            $start = 0;
            $response = new StreamedResponse();
            $response->setCallback(function () use ($start, $encryptor, $passwordManager, $serializer) {

                do {
                    $passwords = $passwordManager->getAllByPasswords($start, 1000);

                    if ($start === 0) {
                        echo '"' . strtolower(implode('";"', array_keys($passwords[0]))) . '"' . PHP_EOL;
                    }

                    foreach ($passwords as $password) {
                        foreach ($password as $key => $value) {
                            if (is_array($value)) {
                                $password[$key] = json_encode($value);
                            } else if (!empty($value) && is_string($value) && strpos($value, '<ENC>') !== false) {
                                $password[$key] = $encryptor->decrypt($password[$key]);
                            }
                        }

                        $context = new SerializationContext();
                        $context->setSerializeNull(true);

                        $data = json_decode($serializer->serialize($password, 'json', $context), true);
                        echo '"' . implode('";"', $data) . '"' . PHP_EOL;
                    }
                    $start += 1000;

                } while (!empty($passwords));
            });

            $filename = sprintf('turtlepass_export_%s.csv', date('Y-m-d H:i:s'));
            $contentDisposition = $response->headers->makeDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, $filename);
            $response->headers->set('Content-Type', 'text/csv');
            $response->headers->set('Content-Disposition', $contentDisposition);

            return $response;
        }

        return $this->render('AppBundle::index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
