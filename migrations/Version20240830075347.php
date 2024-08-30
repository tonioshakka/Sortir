<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240830075347 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE image (id INT AUTO_INCREMENT NOT NULL, profil_pic_id INT DEFAULT NULL, image_name VARCHAR(255) NOT NULL, image_file VARCHAR(255) NOT NULL, updated_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX UNIQ_C53D045FEFAE74C3 (profil_pic_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE image ADD CONSTRAINT FK_C53D045FEFAE74C3 FOREIGN KEY (profil_pic_id) REFERENCES participant (id)');
        $this->addSql('ALTER TABLE participant DROP image_name, DROP image_file');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE image DROP FOREIGN KEY FK_C53D045FEFAE74C3');
        $this->addSql('DROP TABLE image');
        $this->addSql('ALTER TABLE participant ADD image_name VARCHAR(255) DEFAULT NULL, ADD image_file VARCHAR(255) DEFAULT NULL');
    }
}
