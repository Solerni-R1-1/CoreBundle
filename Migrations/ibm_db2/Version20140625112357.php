<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/25 11:23:59
 */
class Version20140625112357 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN illustration_name VARCHAR(255) DEFAULT NULL RENAME desc_img TO illustration_path
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN desc_img VARCHAR(255) DEFAULT NULL 
            DROP COLUMN illustration_path 
            DROP COLUMN illustration_name
        ");
    }
}