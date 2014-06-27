<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/27 03:47:35
 */
class Version20140627154734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_category (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE TABLE claro_moocs_to_categories (
                mooccategory_id INTEGER NOT NULL, 
                mooc_id INTEGER NOT NULL, 
                PRIMARY KEY(mooccategory_id, mooc_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7BC24924A ON claro_moocs_to_categories (mooccategory_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7255EEB87 ON claro_moocs_to_categories (mooc_id)
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_mooc_category
        ");
        $this->addSql("
            DROP TABLE claro_moocs_to_categories
        ");
    }
}