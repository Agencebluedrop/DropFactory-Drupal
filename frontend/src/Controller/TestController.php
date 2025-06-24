<?php

namespace App\Controller;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class TestController extends AbstractController
{
    #[Route('/test-icons', name: 'app_test_icons')]
    public function icons(): Response
    {
        // Path to your SVG directory
        $svgDir = $this->getParameter('kernel.project_dir') . '/templates/components/Atoms/svgs';

        // Get all SVG files
        $svgFiles = [];
        if (is_dir($svgDir)) {
            $svgFiles = array_diff(scandir($svgDir), ['.', '..']);
        }

        return $this->render('test/icons.html.twig', [
            'controller_name' => 'TestController',
            'svgFiles' => $svgFiles,
        ]);
    }

    #[Route('/test-flash', name: 'app_test_flash')]
    public function flashMessages(): Response
    {
        $this->addFlash('success', 'This is a success message');
        $this->addFlash('info', 'This is an info message');
        $this->addFlash('warning', 'This is a warning message');
        $this->addFlash('error', 'This is an error message');

        return $this->render('test/flash.html.twig');
    }
}
