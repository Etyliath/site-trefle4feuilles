<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240919210251 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE creation ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE creation ADD CONSTRAINT FK_57EE857412469DE2 FOREIGN KEY (category_id) REFERENCES category (id)');
        $this->addSql('CREATE INDEX IDX_57EE857412469DE2 ON creation (category_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE creation DROP FOREIGN KEY FK_57EE857412469DE2');
        $this->addSql('DROP INDEX IDX_57EE857412469DE2 ON creation');
        $this->addSql('ALTER TABLE creation DROP category_id');
    }
}
