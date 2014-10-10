<?php

namespace Claroline\CoreBundle\Migrations\ibm_db2;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/08 09:32:36
 */
class Version20141008093234 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            ADD COLUMN nbSuccess INTEGER NOT NULL 
            ADD COLUMN nbFail INTEGER NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_badge_mooc_stats 
            DROP COLUMN nbSuccess 
            DROP COLUMN nbFail
        ");
    }
}