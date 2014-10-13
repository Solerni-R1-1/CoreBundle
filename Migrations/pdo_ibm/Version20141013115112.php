<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/13 11:51:17
 */
class Version20141013115112 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            ADD COLUMN knowledgeBadgesUrl CLOB(1M) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc 
            DROP COLUMN knowledgeBadgesUrl
        ");
    }
}