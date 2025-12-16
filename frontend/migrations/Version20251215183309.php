<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251215183309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        $this->connection->insert('User', [
            'id' => 1,
            'email' => 'factoryadmin@bluedrop.fr',
            'roles' => json_encode(['ROLE_ADMIN']),
            'password' => '$2y$13$jK.1xN8TzhefK.NL.Hbs4.LLZ/D9nDIl2GsUImjyAU6ZT5A8KLxKe',
            'isVerified' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
    }
}
