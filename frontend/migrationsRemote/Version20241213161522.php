<?php

declare(strict_types=1);

namespace DoctrineMigrationsRemote;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241213161522 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE Alias (
          id INT AUTO_INCREMENT NOT NULL,
          site_id INT NOT NULL,
          domain VARCHAR(255) NOT NULL,
          INDEX IDX_20AD4490F6BD1646 (site_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Profile (
          id INT AUTO_INCREMENT NOT NULL,
          platform_id INT NOT NULL,
          name VARCHAR(255) NOT NULL,
          INDEX IDX_4EEA9393FFE6496F (platform_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Site (
          id INT AUTO_INCREMENT NOT NULL,
          install_profile_id INT NOT NULL,
          platform_id INT NOT NULL,
          name VARCHAR(255) NOT NULL,
          domain VARCHAR(255) NOT NULL,
          language VARCHAR(255) NOT NULL,
          image VARCHAR(255) DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          INDEX IDX_C971A6DA5C697713 (install_profile_id),
          INDEX IDX_C971A6DAFFE6496F (platform_id),
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE Task (
          id INT AUTO_INCREMENT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          started_at DATETIME DEFAULT NULL,
          ended_at DATETIME DEFAULT NULL,
          source_entity SMALLINT DEFAULT NULL,
          action VARCHAR(255) NOT NULL,
          parameters JSON DEFAULT NULL,
          status VARCHAR(255) NOT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE TaskBuffer (
          id INT AUTO_INCREMENT NOT NULL,
          created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\',
          action VARCHAR(255) NOT NULL,
          parameters JSON DEFAULT NULL,
          PRIMARY KEY(id)
        ) DEFAULT CHARACTER
        SET
          utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE
          Alias
        ADD
          CONSTRAINT FK_20AD4490F6BD1646 FOREIGN KEY (site_id) REFERENCES Site (id)');
        $this->addSql('ALTER TABLE
          Profile
        ADD
          CONSTRAINT FK_4EEA9393FFE6496F FOREIGN KEY (platform_id) REFERENCES Platform (id)');
        $this->addSql('ALTER TABLE
          Site
        ADD
          CONSTRAINT FK_C971A6DA5C697713 FOREIGN KEY (install_profile_id) REFERENCES Profile (id)');
        $this->addSql('ALTER TABLE
          Site
        ADD
          CONSTRAINT FK_C971A6DAFFE6496F FOREIGN KEY (platform_id) REFERENCES Platform (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE Alias DROP FOREIGN KEY FK_20AD4490F6BD1646');
        $this->addSql('ALTER TABLE Profile DROP FOREIGN KEY FK_4EEA9393FFE6496F');
        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DA5C697713');
        $this->addSql('ALTER TABLE Site DROP FOREIGN KEY FK_C971A6DAFFE6496F');
        $this->addSql('DROP TABLE Alias');
        $this->addSql('DROP TABLE Profile');
        $this->addSql('DROP TABLE Site');
        $this->addSql('DROP TABLE Task');
        $this->addSql('DROP TABLE TaskBuffer');
    }
}
