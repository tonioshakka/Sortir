<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240829124857 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE UNIQUE INDEX lieu ON lieu (nom, longitude, latitude)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3C3FD3F26C6E55B5D709040AD936B2FA ON sortie (nom, date_heure_debut, organisateur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX lieu ON lieu');
        $this->addSql('DROP INDEX UNIQ_3C3FD3F26C6E55B5D709040AD936B2FA ON sortie');
    }
}
