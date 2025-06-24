<?php

namespace App\Controller;

use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function administration(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/clear-cache', name: 'app_clear_cache')]
    public function clearCache(): Response
    {
        $process = new Process(['php', 'bin/console', 'cache:clear']);
        $process->setWorkingDirectory($this->getParameter('kernel.project_dir'));
        $process->run();

        if (!$process->isSuccessful()) {
            $this->addFlash('error', $process->getErrorOutput());
            // return new JsonResponse(['status' => 'error', 'message' => $process->getErrorOutput()], 500);
        } else {
            $this->addFlash('success', 'Cache cleared successfully !');
        }

        return $this->redirectToRoute('app_admin');

        // return new JsonResponse([
        //     'status' => 'success',
        //     'message' => 'Cache cleared successfully'
        // ]);
    }
}
