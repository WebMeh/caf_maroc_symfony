<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250205155339 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billet ADD acheteur_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE billet ADD CONSTRAINT FK_1F034AF696A7BB5F FOREIGN KEY (acheteur_id) REFERENCES `user` (id)');
        $this->addSql('CREATE INDEX IDX_1F034AF696A7BB5F ON billet (acheteur_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE billet DROP FOREIGN KEY FK_1F034AF696A7BB5F');
        $this->addSql('DROP INDEX IDX_1F034AF696A7BB5F ON billet');
        $this->addSql('ALTER TABLE billet DROP acheteur_id');
    }
}
