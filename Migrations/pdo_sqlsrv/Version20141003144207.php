<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/03 02:42:08
 */
class Version20141003144207 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_analytics_user_mooc_stats.last_action_date', 
            'date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats ALTER COLUMN date DATE NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            sp_RENAME 'claro_analytics_user_mooc_stats.date', 
            'last_action_date', 
            'COLUMN'
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_user_mooc_stats ALTER COLUMN last_action_date DATE NOT NULL
        ");
    }
}