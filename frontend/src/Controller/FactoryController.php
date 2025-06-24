<?php

namespace App\Controller;

use App\Form\SiteType;
use App\Form\PlatformType;
use App\Entity\Remote\Site;
use App\Entity\Remote\Task;
use App\Entity\Remote\Platform;
use App\Entity\Remote\TaskBuffer;
use App\Service\TaskBufferManager;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[IsGranted('ROLE_USER')]
class FactoryController extends AbstractController
{
    #[Route('/platforms', name: 'app_platforms')]
    public function platforms(ManagerRegistry $doctrine): Response
    {
        // Load platforms from the Remote database
        $remoteEntityManager = $doctrine->getManager('remote');

        /** @var Platform[] $platforms */
        $platforms = $remoteEntityManager->getRepository(Platform::class)->findAll();

        $platformsData = [];

        foreach ($platforms as $platform) {
            $availableTasks = [];
            
            if ($platform->getStatus() === Platform::STATUS_ENABLED) {
                $availableTasks[] = [
                    'label' => 'Vérifier la plateforme', // 'PLATFORM_VERIFY',
                    'url' => '/new_task/PLATFORM_VERIFY/' . $platform->getId(),
                    'icon' => 'verify'
                ];
                $availableTasks[] = [
                    'label' => 'Git pull', // 'PLATFORM_PULL',
                    'url' => '/new_task/PLATFORM_PULL/' . $platform->getId(),
                    'icon' => 'pull'
                ];
                $availableTasks[] = [
                    'label' => 'Désactiver la plateforme', // 'PLATFORM_DISABLE',
                    'url' => '/new_task/PLATFORM_DISABLE/' . $platform->getId(),
                    'icon' => 'desactivate'
                ];
            } else {
                $availableTasks[] = [
                    'label' => 'Activer la plateforme', // 'PLATFORM_ENABLE',
                    'url' => '/new_task/PLATFORM_ENABLE/' . $platform->getId(),
                    'icon' => 'empty-cache'
                ];
            }

            $platformsData[] = [[
                'id' => $platform->getId(),
                'online' => $platform->getStatus() === Platform::STATUS_ENABLED ? true : false,
                'name' => $platform->getName(),
                'url' => $this->generateUrl('app_platform', [
                    'id' => $platform->getId(),
                ]),
                'git' => $platform->getGitRepositoryBranch(),
                'availableTasks' => $availableTasks,
            ]];
        }

        return $this->render('factory/platforms.html.twig', [
            'tablesCells' => $platformsData,
        ]);
    }

    #[Route('/platform/{id}', name: 'app_platform')]
    public function platform(ManagerRegistry $doctrine, int $id): Response
    {
        // Load platforms from the Remote database
        $remoteEntityManager = $doctrine->getManager('remote');

        /** @var Platform $platform */
        $platform = $remoteEntityManager->getRepository(Platform::class)->find($id);

        if ($platform === null) {
            $this->addFlash('error', 'Platform ID ' . $id . ' not found!');
            return $this->redirectToRoute('app_platforms');
        }



        return $this->render('factory/platform.html.twig', [
            'platform' => $platform,
        ]);
    }

    #[Route('/add_platform', name: 'app_new_platform')]
    public function addPlatform(Request $request, TaskBufferManager $taskBufferManager): Response
    {
        $platform = new Platform();

        $form = $this->createForm(PlatformType::class, $platform);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            $platform = $form->getData();

            $taskBufferManager->newPlatform(
                $platform->getName(),
                $platform->getGitRepositoryUrl(),
                $platform->getGitRepositoryBranch()
            );

            $this->addFlash('success', 'New Platform Task created successfully!');

            return $this->redirectToRoute('app_platforms');
        }

        return $this->render('factory/platform_add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/sites', name: 'app_sites')]
    public function sites(Request $request, ManagerRegistry $doctrine): Response
    {
        $viewMode = $request->query->get('view', 'list');

        // Load sites from the Remote database
        $remoteEntityManager = $doctrine->getManager('remote');

        /** @var Site[] $sites */
        $sites = $remoteEntityManager->getRepository(Site::class)->findAll();

        $sitesData = [];
        $cardsData = [];
        foreach ($sites as $site) {
            $availableTasks = [];

            if ($site->getStatus() === Site::STATUS_ENABLED) {
                $availableTasks[] = [
                    'label' => 'Vérifier le site', // 'SITE_VERIFY',
                    'url' => '/new_task/SITE_VERIFY/' . $site->getId(),
                    'icon' => 'verify'
                ];
                $availableTasks[] = [
                    'label' => 'Vider le cache Drupal', // 'SITE_CLEAR_CACHE',
                    'url' => '/new_task/SITE_CLEAR_CACHE/' . $site->getId(),
                    'icon' => 'empty-cache'
                ];
                $availableTasks[] = [
                    'label' => 'Exécuter le Cron', // 'SITE_RUN_CRON',
                    'url' => '/new_task/SITE_RUN_CRON/' . $site->getId(),
                    'icon' => 'cron'
                ];
                $availableTasks[] = [
                    'label' => 'Mettre à jour la base de données', // 'SITE_DB_UPDATES',
                    'url' => '/new_task/SITE_DB_UPDATES/' . $site->getId(),
                    'icon' => 'update'
                ];
                $availableTasks[] = [
                    'label' => 'Backup', // 'SITE_BACKUP',
                    'url' => '/new_task/SITE_BACKUP/' . $site->getId(),
                    'icon' => 'backup'
                ];
                $availableTasks[] = [
                    'label' => 'Cloner', // 'SITE_CLONE',
                    'url' => '/new_task/SITE_CLONE/' . $site->getId(),
                    'icon' => 'clone'
                ];
                $availableTasks[] = [
                    'label' => 'Reset password', // 'SITE_RESET_PASSWORD',
                    'url' => '/new_task/SITE_RESET_PASSWORD/' . $site->getId(),
                    'icon' => 'reinitialise'
                ];
                $availableTasks[] = [
                    'label' => 'Désactiver le site', // 'SITE_DISABLE',
                    'url' => '/new_task/SITE_DISABLE/' . $site->getId(),
                    'icon' => 'desactivate'
                ];
            } else {
                $availableTasks[] = [
                    'label' => 'Activer le site', // 'SITE_ENABLE',
                    'url' => '/new_task/SITE_ENABLE/' . $site->getId(),
                    'icon' => 'activate'
                ];
                $availableTasks[] = [
                    'label' => 'Supprimer le site', // 'SITE_DELETE',
                    'url' => '/new_task/SITE_DELETE/' . $site->getId(),
                    'icon' => 'trash'
                ];
            }

            $sitesData[] = [[
                'id' => $site->getId(),
                'online' => $site->getStatus() === Site::STATUS_ENABLED ? true : false,
                'siteName' => $site->getName(),
                'url' => $this->generateUrl('app_site', [
                    'id' => $site->getId(),
                ]),
                'siteUrl' => $site->getDomain(),
                'installProfile' => $site->getInstallProfile()->getName(),
                'availableTasks' => $availableTasks,
            ]];

            $cardsData[] = [
                'id' => $site->getId(),
                'online' => $site->getStatus() === Site::STATUS_ENABLED ? true : false,
                'siteName' => $site->getName(),
                'url' => $this->generateUrl('app_site', [
                    'id' => $site->getId(),
                ]),
                'siteUrl' => $site->getDomain(),
                'image' => $site->getImage() ?? 'default.jpg',
                'installProfile' => $site->getInstallProfile()->getName(),
                'availableTasks' => $availableTasks,
            ];
        }

        return $this->render('factory/sites.html.twig', [
            'viewMode' => $viewMode,
            'tablesCells' => $sitesData,
            'cardsData' => $cardsData,
        ]);
    }

    #[Route('/site/{id}', name: 'app_site')]
    public function site(ManagerRegistry $doctrine, int $id): Response
    {
        // Load site from the Remote database
        $remoteEntityManager = $doctrine->getManager('remote');

        /** @var Site $site */
        $site = $remoteEntityManager->getRepository(Site::class)->find($id);

        if ($site === null) {
            $this->addFlash('error', 'Site ID ' . $id . ' not found!');
            return $this->redirectToRoute('app_sites');
        }

        return $this->render('factory/site.html.twig', [
            'site' => $site,
        ]);
    }

    #[Route('/add_site', name: 'app_new_site', methods: ['GET', 'POST'])]
    public function addSite(Request $request, TaskBufferManager $taskBufferManager): Response
    {
        $site = new Site();

        $form = $this->createForm(SiteType::class, $site, [
            'action' => $this->generateUrl('app_new_site'),
        ]);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $form->getData() holds the submitted values
            $site = $form->getData();

            $taskBufferManager->newSite(
                $site->getPlatform()->getId(),
                $site->getName(),
                $site->getDomain(),
                $site->getInstallProfile()->getId(),
                $site->getLanguage()
            );

            $this->addFlash('success', 'New Site Task created successfully!');

            return $this->redirectToRoute('app_sites');
        }

        return $this->render('factory/site_add.html.twig', [
            'form' => $form,
        ]);
    }

    #[Route('/tasks', name: 'app_tasks')]
    public function tasks(ManagerRegistry $doctrine): Response
    {
        $tasksData = [];

        // Load tasks from the Remote database
        $remoteEntityManager = $doctrine->getManager('remote');

        /** @var Task[] $tasks */
        $tasks = $remoteEntityManager->getRepository(Task::class)->findAll();

        // Load also tasks from the queue
        /** @var TaskBuffer[] $tasksQueued */
        $tasksQueued = $remoteEntityManager->getRepository(TaskBuffer::class)->findAll();

        foreach ($tasksQueued as $task) {
            $taskAction = $task->getAction();
            $taskLabel = Task::getActionLabel($taskAction);

            $resourceName = 'Unknown';
            $resourcePath = '#';

            if ($taskAction === 'PLATFORM_ADD' || $taskAction === 'SITE_ADD') {
                $resourceName = $task->getParameters()['name'];
            } else {
                $resourceId = $task->getParameters()['resourceId'];

                if ($resourceId) {
                    $resourceType = strpos($taskAction, 'SITE') !== false ? 'Site' : 'Platform';

                    // if $taskLabel contains 'SITE'
                    if (strpos($taskAction, 'SITE') !== false) {
                        $resourceType = 'Site';
                    } elseif (strpos($taskAction, 'PLATFORM') !== false) {
                        $resourceType = 'Platform';
                    } else {
                        $resourceType = 'Unknown';
                    }

                    if ($resourceType === 'Site') {
                        $resource = $remoteEntityManager->getRepository(Site::class)->find($resourceId);
                    } elseif ($resourceType === 'Platform') {
                        $resource = $remoteEntityManager->getRepository(Platform::class)->find($resourceId);
                    } else {
                        $resource = null;
                    }

                    $resourceName = $resource ? $resource->getName() : 'Unknown resource ' . $resourceId;
                    $resourcePath = $resource ? $this->generateUrl('app_' . strtolower($resourceType), [
                        'id' => $resourceId,
                    ]) : '#';
                }
            }
            

            $tasksData[] = [[
                'status' => 'PENDING',
                'taskLabel' => $taskLabel,
                'resourceName' => $resourceName,
                'resourcePath' => $resourcePath,
                'createdAt' => $task->getCreatedAtFormatted(),
                'startedAt' => '-',
                'duration' => '-',
                'parameters' => $task->getParameters(),
            ]];
        }

        foreach ($tasks as $task) {
            $duration = $task->getEndedAt() && $task->getStartedAt() ? $task->getEndedAt()->getTimestamp() - $task->getStartedAt()->getTimestamp() : '-';
            if ($duration === '-') {
                $duration = '-';
            } else {
                // duration in human readable format
                $duration = gmdate("i's''", $duration);
            }

            $taskAction = $task->getAction();
            $taskLabel = Task::getActionLabel($taskAction);

            $resource = null;
            $resourceName = 'Unknown';
            $resourcePath = '#';
            $resourceId = $task->getSourceEntity();
            if ($resourceId) {
                $resourceType = strpos($taskAction, 'SITE') !== false ? 'Site' : 'Platform';
                if ($resourceType === 'Site') {
                    $resource = $remoteEntityManager->getRepository(Site::class)->find($resourceId);
                } elseif ($resourceType === 'Platform') {
                    $resource = $remoteEntityManager->getRepository(Platform::class)->find($resourceId);
                } else {
                    $resource = null;
                }

                $resourceName = $resource ? $resource->getName() : 'Unknown resource ' . $resourceId;
                $resourcePath = $resource ? $this->generateUrl('app_' . strtolower($resourceType), [
                    'id' => $resourceId,
                ]) : '#';
            }

            $tasksData[] = [[
                'status' => $task->getStatus(),
                'taskLabel' => $taskLabel,
                'resourceName' => $resourceName,
                'resourcePath' => $resourcePath,
                'createdAt' => $task->getCreatedAtFormatted(),
                'startedAt' => $task->getStartedAtFormatted(),
                'duration' => $duration,
                'parameters' => $task->getParameters(),
            ]];
        }

        // Sort $tasksData by createdAt DESC
        usort($tasksData, function ($a, $b) {
            return strtotime($b[0]['createdAt']) - strtotime($a[0]['createdAt']);
        });

        return $this->render('factory/tasks.html.twig', [
            'tablesCells' => $tasksData,
        ]);
    }

    #[Route('/new_task/{taskName}/{resourceId}', name: 'app_new_task',)]
    public function newTask(TaskBufferManager $taskBufferManager, string $taskName, int $resourceId): Response
    {
        if (!in_array($taskName, Task::ACTIONS)) {
            $this->addFlash('error', 'Invalid task name!');
        } else {
            $taskBufferManager->newTask($taskName, $resourceId);

            $this->addFlash('success', 'Task created successfully!');
        }

        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/add_group_task/{taskName}/{ids}', name: 'app_new_group_task')]
    public function addGroupTask(ManagerRegistry $doctrine, TaskBufferManager $taskBufferManager, string $taskName, string $ids): Response
    {
        if (!in_array($taskName, Task::ACTIONS)) {
            $this->addFlash('error', 'Invalid task name!');
        } else {
            $ids = explode('+', $ids);

            $remoteEntityManager = $doctrine->getManager('remote');

            $type = "Platform";
            $repo = $remoteEntityManager->getRepository(Platform::class);
            
            if (strpos($ids[0], 'SITE') !== false) {
                $type = "Site";
                $repo = $remoteEntityManager->getRepository(Site::class);
            }

            foreach ($ids as $id) {
                $entity = $repo->find($id);
                if ($entity === null) {
                    $this->addFlash('error', $type . ' ID ' . $id . ' not found!');
                } else {
                    $taskBufferManager->newTask($taskName, $id);

                    $message = 'New Task ' . $taskName . ' created successfully for ' . $entity->getName() . '!';
                    $this->addFlash('success', $message);
                }
            }
        }

        return $this->redirectToRoute('app_tasks');
    }

    #[Route('/backups', name: 'app_backups')]
    public function backups(): Response
    {
        return $this->render('factory/backups.html.twig', []);
    }
}
