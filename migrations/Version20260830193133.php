<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260830193133 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE event CHANGE title title VARCHAR(64) NOT NULL');
        $this->addSql('CREATE UNIQUE INDEX uq_event_title ON event (title)');
        $this->addSql('CREATE UNIQUE INDEX uq_tag_title ON tag (title)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX uq_event_title ON event');
        $this->addSql('ALTER TABLE event CHANGE title title VARCHAR(255) NOT NULL');
        $this->addSql('DROP INDEX uq_tag_title ON tag');
    }
}
