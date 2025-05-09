<?php

namespace Main\PasswordBundle\Controller;

use Main\PasswordBundle\Entity\PasswordShareLink;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class ShareController extends Controller
{
    /**
     * @Route("/password-share", methods={"GET"})
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        return $this->render('MainPasswordBundle:Share:index.html.twig', [
            'version' => $this->getParameter('app_version'),
        ]);
    }
}