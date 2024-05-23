<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240521123828 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'ajout des champs pour Articles';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles ADD url VARCHAR(255) NOT NULL, ADD image_url VARCHAR(255) DEFAULT NULL, ADD source VARCHAR(255) NOT NULL, ADD author VARCHAR(255) DEFAULT NULL, ADD published_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE articles DROP url, DROP image_url, DROP source, DROP author, DROP published_at');
    }
}
