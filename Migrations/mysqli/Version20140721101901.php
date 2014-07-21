<?php

namespace Claroline\CoreBundle\Migrations\mysqli;

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
            ALTER TABLE claro_mooc CHANGE duration duration INT DEFAULT NULL, 
            CHANGE weekly_time weekly_time INT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc CHANGE duration duration VARCHAR(255) DEFAULT NULL, 
            CHANGE weekly_time weekly_time VARCHAR(255) DEFAULT NULL
        ");
    }
}