<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserEditType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Process\Process;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'app_admin')]
    public function administration(): Response
    {
        return $this->render('admin/index.html.twig');
    }

    #[Route('/admin/users', name: 'app_admin_users')]
    public function users(UserRepository $userRepository): Response
    {
        $usersData = [];
        foreach ($userRepository->findBy([], ['email' => 'ASC']) as $user) {
            $usersData[] = [
                $user->getEmail(),
                implode(', ', $user->getRoles()),
                $user->isVerified() ? 'Yes' : 'No',
                [
                    'editUrl' => $this->generateUrl('app_admin_user_edit', ['id' => $user->getId()]),
                    'deleteUrl' => $this->generateUrl('app_admin_user_delete', ['id' => $user->getId()]),
                    'deleteTokenId' => 'delete_user_' . $user->getId(),
                ],
            ];
        }

        return $this->render('admin/users.html.twig', [
            'tableHeader' => ['Email', 'Roles', 'Verified', '', ''],
            'tablesCells' => $usersData,
        ]);
    }

    #[Route('/admin/users/{id}/edit', name: 'app_admin_user_edit')]
    public function editUser(User $user, Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(UserEditType::class, $user);
        $form->get('roles')->setData(in_array('ROLE_ADMIN', $user->getRoles(), true) ? 'ROLE_ADMIN' : 'ROLE_USER');
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roles = $form->get('roles')->getData();
            $user->setRoles([$roles]);

            /** @var ?string $plainPassword */
            $plainPassword = $form->get('plainPassword')->getData();
            if ($plainPassword !== null && $plainPassword !== '') {
                $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            }

            $entityManager->flush();

            $this->addFlash('success', sprintf('User %s updated successfully!', $user->getEmail()));

            return $this->redirectToRoute('app_admin_users');
        }

        return $this->render('admin/user_edit.html.twig', [
            'userEditForm' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/admin/users/{id}/delete', name: 'app_admin_user_delete', methods: ['POST'])]
    public function deleteUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        if (!$this->isCsrfTokenValid('delete_user_' . $user->getId(), $request->getPayload()->getString('_token'))) {
            $this->addFlash('error', 'Invalid CSRF token.');
            return $this->redirectToRoute('app_admin_users');
        }

        if ($this->getUser()?->getUserIdentifier() === $user->getUserIdentifier()) {
            $this->addFlash('error', 'You cannot delete your own account.');
            return $this->redirectToRoute('app_admin_users');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        $this->addFlash('success', sprintf('User %s deleted successfully!', $user->getEmail()));

        return $this->redirectToRoute('app_admin_users');
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
