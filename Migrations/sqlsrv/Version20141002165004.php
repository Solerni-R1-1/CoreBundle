<?php

namespace Claroline\CoreBundle\Migrations\sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 04:50:05
 */
class Version20141002165004 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h0 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h1 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h2 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h3 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h4 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h5 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h6 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h7 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h8 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h9 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h10 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h11 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h12 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h13 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h14 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h15 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h16 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h17 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h18 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h19 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h20 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h21 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h22 NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h23 NVARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h0
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h1
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h2
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h3
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h4
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h5
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h6
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h7
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h8
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h9
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h10
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h11
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h12
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h13
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h14
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h15
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h16
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h17
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h18
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h19
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h20
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h21
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h22
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP COLUMN h23
        ");
    }
}