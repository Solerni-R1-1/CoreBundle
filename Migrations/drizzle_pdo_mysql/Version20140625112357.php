<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ADD illustration_name VARCHAR(255) DEFAULT NULL, 
            CHANGE desc_img illustration_path VARCHAR(255) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD desc_img VARCHAR(255) DEFAULT NULL, 
            DROP illustration_path, 
            DROP illustration_name
        ");
    }
}