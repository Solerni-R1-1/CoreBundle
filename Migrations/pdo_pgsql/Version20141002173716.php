<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/10/02 05:37:17
 */
class Version20141002173716 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_analytics_hourly_mooc_stats (
                id SERIAL NOT NULL, 
                workspace_id INT DEFAULT NULL, 
                action VARCHAR(255) NOT NULL, 
                h0 VARCHAR(255) NOT NULL, 
                h1 VARCHAR(255) NOT NULL, 
                h2 VARCHAR(255) NOT NULL, 
                h3 VARCHAR(255) NOT NULL, 
                h4 VARCHAR(255) NOT NULL, 
                h5 VARCHAR(255) NOT NULL, 
                h6 VARCHAR(255) NOT NULL, 
                h7 VARCHAR(255) NOT NULL, 
                h8 VARCHAR(255) NOT NULL, 
                h9 VARCHAR(255) NOT NULL, 
                h10 VARCHAR(255) NOT NULL, 
                h11 VARCHAR(255) NOT NULL, 
                h12 VARCHAR(255) NOT NULL, 
                h13 VARCHAR(255) NOT NULL, 
                h14 VARCHAR(255) NOT NULL, 
                h15 VARCHAR(255) NOT NULL, 
                h16 VARCHAR(255) NOT NULL, 
                h17 VARCHAR(255) NOT NULL, 
                h18 VARCHAR(255) NOT NULL, 
                h19 VARCHAR(255) NOT NULL, 
                h20 VARCHAR(255) NOT NULL, 
                h21 VARCHAR(255) NOT NULL, 
                h22 VARCHAR(255) NOT NULL, 
                h23 VARCHAR(255) NOT NULL, 
                date DATE NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_C560845B82D40A1F ON claro_analytics_hourly_mooc_stats (workspace_id)
        ");
        $this->addSql("
            ALTER TABLE claro_analytics_hourly_mooc_stats 
            ADD CONSTRAINT FK_C560845B82D40A1F FOREIGN KEY (workspace_id) 
            REFERENCES claro_workspace (id) NOT DEFERRABLE INITIALLY IMMEDIATE
        ");
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

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP TABLE claro_analytics_hourly_mooc_stats
        ");
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
}