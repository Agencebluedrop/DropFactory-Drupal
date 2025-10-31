<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241203212831 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE User (
          id INT AUTO_INCREMENT NOT NULL,
          email VARCHAR(180) NOT NULL,
          roles JSON NOT NULL,
          password VARCHAR(255) NOT NULL,
          isVerified TINYINT(1) NOT NULL,
          UNIQUE INDEX UNIQ_IDENTIFIER_EMAIL (email),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');

        $this->connection->insert('User', [
            'id' => 1,
            'email' => 'factoryadmin@bluedrop.fr',
            'roles' => ['ROLE_ADMIN'],
            'password' => '$2y$13$jK.1xN8TzhefK.NL.Hbs4.LLZ/D9nDIl2GsUImjyAU6ZT5A8KLxKe',
            'isVerified' => true,
        ]);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE User');
    }
}
