<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240708094002 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SEQUENCE room_id_seq INCREMENT BY 1 MINVALUE 1 START 1');
        $this->addSql('CREATE TABLE room (id INT NOT NULL, riad_id INT NOT NULL, name VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, nb_personne INT NOT NULL, price DOUBLE PRECISION NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_729F519B40766265 ON room (riad_id)');
        $this->addSql('ALTER TABLE room ADD CONSTRAINT FK_729F519B40766265 FOREIGN KEY (riad_id) REFERENCES riad (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP SEQUENCE room_id_seq CASCADE');
        $this->addSql('ALTER TABLE room DROP CONSTRAINT FK_729F519B40766265');
        $this->addSql('DROP TABLE room');
    }
}
