<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/07/21 10:19:02
 */
class Version20140721101901 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc MODIFY (
                duration NUMBER(10) DEFAULT NULL, 
                weekly_time NUMBER(10) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc MODIFY (
                duration VARCHAR2(255) DEFAULT NULL, 
                weekly_time VARCHAR2(255) DEFAULT NULL
            )
        ");
    }
}