<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/25 11:23:58
 */
class Version20140625112357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD illustration_name VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc RENAME COLUMN desc_img TO illustration_path
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD desc_img VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP illustration_path
        ");
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP illustration_name
        ");
    }
}