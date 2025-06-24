<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class BluedropController extends AbstractController
{
    #[Route('/', name: 'app_homepage')]
    public function index(): Response
    {
        return $this->render('bluedrop/index.html.twig');
    }

    #[Route('/legal-notices', name: 'app_legal_notices')]
    public function legalNotices(): Response
    {
        return $this->render('bluedrop/legal_notices.html.twig');
    }
}
