<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2016/08/26 05:11:39
 */
class Version20160826171124 extends AbstractMigration
{
    public function up(Schema $schema)
    {
         $this->addSql("
            ALTER TABLE claro_mooc_session 
            ADD archived INT DEFAULT 0
        ");

    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc_session 
            DROP archived
        ");

    }
}