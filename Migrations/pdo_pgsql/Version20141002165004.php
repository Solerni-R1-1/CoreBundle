<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

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
            ADD h0 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h1 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h2 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h3 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h4 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h5 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h6 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h7 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h8 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h9 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h10 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h11 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h12 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h13 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h14 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h15 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h16 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h17 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h18 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h19 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h20 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h21 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h22 VARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            ADD h23 VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h0
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h1
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h2
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h3
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h4
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h5
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h6
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h7
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h8
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h9
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h10
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h11
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h12
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h13
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h14
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h15
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h16
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h17
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h18
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h19
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h20
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h21
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h22
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h23
        ");
    }
}