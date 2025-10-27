<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027152136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE unit (code VARCHAR(20) NOT NULL, label VARCHAR(255) NOT NULL, kind VARCHAR(255) NOT NULL, PRIMARY KEY (code))');
        $this->addSql('ALTER TABLE recipe_ingredient ADD unit_id VARCHAR(20) DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe_ingredient ADD CONSTRAINT FK_22D1FE13F8BD700D FOREIGN KEY (unit_id) REFERENCES unit (code) NOT DEFERRABLE');
        $this->addSql('CREATE INDEX IDX_22D1FE13F8BD700D ON recipe_ingredient (unit_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_ingredient DROP CONSTRAINT FK_22D1FE13F8BD700D');
        $this->addSql('DROP INDEX IDX_22D1FE13F8BD700D');
        $this->addSql('ALTER TABLE recipe_ingredient DROP unit_id');
        $this->addSql('DROP TABLE unit');
    }
}
