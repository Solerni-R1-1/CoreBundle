<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/30 10:42:08
 */
class Version20140630104206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_moocs_to_categories (
                mooc_id INTEGER NOT NULL, 
                mooccategory_id INTEGER NOT NULL, 
                PRIMARY KEY(mooc_id, mooccategory_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7255EEB87 ON claro_moocs_to_categories (mooc_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7BC24924A ON claro_moocs_to_categories (mooccategory_id)
        ");
        $this->addSql("
            CREATE TABLE claro_mooc_category (
                id INTEGER NOT NULL, 
                name VARCHAR(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_moocs_to_categories
        ");
        $this->addSql("
            DROP TABLE claro_mooc_category
        ");
    }
}