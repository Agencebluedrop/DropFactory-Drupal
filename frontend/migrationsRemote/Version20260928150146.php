<?php

declare(strict_types=1);

namespace DoctrineMigrationsRemote;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260928150146 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Site ADD htpasswd_username VARCHAR(255) DEFAULT NULL, ADD htpasswd_password VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE Task CHANGE parameters parameters JSON DEFAULT NULL, CHANGE results results JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE TaskBuffer CHANGE parameters parameters JSON DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Site DROP htpasswd_username, DROP htpasswd_password');
        $this->addSql('ALTER TABLE Task CHANGE parameters parameters LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`, CHANGE results results LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
        $this->addSql('ALTER TABLE TaskBuffer CHANGE parameters parameters LONGTEXT DEFAULT NULL COLLATE `utf8mb4_bin`');
    }
}
