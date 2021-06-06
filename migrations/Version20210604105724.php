<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210604105724 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE aids ADD origin VARCHAR(255) NOT NULL DEFAULT \'import-excel\'');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE aids DROP origin');
    }
}
