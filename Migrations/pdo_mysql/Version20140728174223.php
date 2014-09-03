<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/28 05:42:24
 */
class Version20140728174223 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD is_validate TINYINT(1) NOT NULL, 
            ADD key_validate VARCHAR(255) DEFAULT NULL
        ");

        $this->addSql("
            UPDATE claro_user 
            set is_validate = true
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            DROP is_validate, 
            DROP key_validate
        ");
    }
}