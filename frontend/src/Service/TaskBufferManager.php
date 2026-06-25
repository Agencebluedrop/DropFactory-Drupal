<?php

namespace App\Service;

use App\Entity\Remote\TaskBuffer;
use Doctrine\Persistence\ManagerRegistry;

class TaskBufferManager
{
    public function __construct(
        private ManagerRegistry $doctrine,
    ) {
    }

    public function newPlatform(string $name, string $gitUrl, string $gitBranch)
    {
        // Create a new task in the buffer
        $taskBuffer = new TaskBuffer();
        $taskBuffer->setAction("PLATFORM_ADD");
        $taskBuffer->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone("Europe/Paris")));
        $taskBuffer->setParameters([
            'name' => $name,
            'gitUrl' => $gitUrl,
            'gitBranch' => $gitBranch,
        ]);

        $remoteEntityManager = $this->doctrine->getManager('remote');
        $remoteEntityManager->persist($taskBuffer);
        $remoteEntityManager->flush();
    }

    public function newSite(int $platformId, string $name, string $domain, int $installProfileId, string $language, array $aliases = [])
    {
        // Create a new task in the buffer
        $taskBuffer = new TaskBuffer();
        $taskBuffer->setAction("SITE_ADD");
        $taskBuffer->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone("Europe/Paris")));
        $taskBuffer->setParameters([
            'platformId' => $platformId,
            'name' => $name,
            'domain' => $domain,
            'installProfileId' => $installProfileId,
            'language' => $language,
            'aliases' => $aliases,
        ]);

        $remoteEntityManager = $this->doctrine->getManager('remote');
        $remoteEntityManager->persist($taskBuffer);
        $remoteEntityManager->flush();
    }

    public function editSite(int $siteId, string $name, array $aliases)
    {
        // Create a new task in the buffer
        $taskBuffer = new TaskBuffer();
        $taskBuffer->setAction("SITE_EDIT");
        $taskBuffer->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone("Europe/Paris")));
        $taskBuffer->setParameters([
            'resourceId' => $siteId,
            'name' => $name,
            'aliases' => $aliases,
        ]);

        $remoteEntityManager = $this->doctrine->getManager('remote');
        $remoteEntityManager->persist($taskBuffer);
        $remoteEntityManager->flush();
    }

    public function newTask(string $taskName, int $resourceId)
    {
        // Create a new task in the buffer
        $taskBuffer = new TaskBuffer();
        $taskBuffer->setAction($taskName);
        $taskBuffer->setCreatedAt(new \DateTimeImmutable('now', new \DateTimeZone("Europe/Paris")));
        $taskBuffer->setParameters([
            'resourceId' => $resourceId,
        ]);

        $remoteEntityManager = $this->doctrine->getManager('remote');
        $remoteEntityManager->persist($taskBuffer);
        $remoteEntityManager->flush();
    }
}
