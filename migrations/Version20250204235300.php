<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250204235300 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE matche (id INT AUTO_INCREMENT NOT NULL, team1_id INT NOT NULL, team2_id INT NOT NULL, score1 INT DEFAULT NULL, score2 INT DEFAULT NULL, date DATETIME NOT NULL, stadium VARCHAR(255) NOT NULL, phase VARCHAR(255) NOT NULL, INDEX IDX_9FCAD510E72BCFA4 (team1_id), INDEX IDX_9FCAD510F59E604A (team2_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE matche ADD CONSTRAINT FK_9FCAD510E72BCFA4 FOREIGN KEY (team1_id) REFERENCES team (id)');
        $this->addSql('ALTER TABLE matche ADD CONSTRAINT FK_9FCAD510F59E604A FOREIGN KEY (team2_id) REFERENCES team (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE matche DROP FOREIGN KEY FK_9FCAD510E72BCFA4');
        $this->addSql('ALTER TABLE matche DROP FOREIGN KEY FK_9FCAD510F59E604A');
        $this->addSql('DROP TABLE matche');
    }
}
