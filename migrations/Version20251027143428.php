<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251027143428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE recipe_category_recipe (recipe_id INT NOT NULL, category_recipe_id INT NOT NULL, PRIMARY KEY (recipe_id, category_recipe_id))');
        $this->addSql('CREATE INDEX IDX_BC142E2059D8A214 ON recipe_category_recipe (recipe_id)');
        $this->addSql('CREATE INDEX IDX_BC142E209EB87024 ON recipe_category_recipe (category_recipe_id)');
        $this->addSql('ALTER TABLE recipe_category_recipe ADD CONSTRAINT FK_BC142E2059D8A214 FOREIGN KEY (recipe_id) REFERENCES recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_category_recipe ADD CONSTRAINT FK_BC142E209EB87024 FOREIGN KEY (category_recipe_id) REFERENCES category_recipe (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe DROP CONSTRAINT fk_da88b13712469de2');
        $this->addSql('DROP INDEX idx_da88b13712469de2');
        $this->addSql('ALTER TABLE recipe DROP category_id');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE recipe_category_recipe DROP CONSTRAINT FK_BC142E2059D8A214');
        $this->addSql('ALTER TABLE recipe_category_recipe DROP CONSTRAINT FK_BC142E209EB87024');
        $this->addSql('DROP TABLE recipe_category_recipe');
        $this->addSql('ALTER TABLE recipe ADD category_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipe ADD CONSTRAINT fk_da88b13712469de2 FOREIGN KEY (category_id) REFERENCES category_recipe (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('CREATE INDEX idx_da88b13712469de2 ON recipe (category_id)');
    }
}
