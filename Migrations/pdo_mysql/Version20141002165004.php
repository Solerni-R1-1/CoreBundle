<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
            ADD h0 VARCHAR(255) NOT NULL, 
            ADD h1 VARCHAR(255) NOT NULL, 
            ADD h2 VARCHAR(255) NOT NULL, 
            ADD h3 VARCHAR(255) NOT NULL, 
            ADD h4 VARCHAR(255) NOT NULL, 
            ADD h5 VARCHAR(255) NOT NULL, 
            ADD h6 VARCHAR(255) NOT NULL, 
            ADD h7 VARCHAR(255) NOT NULL, 
            ADD h8 VARCHAR(255) NOT NULL, 
            ADD h9 VARCHAR(255) NOT NULL, 
            ADD h10 VARCHAR(255) NOT NULL, 
            ADD h11 VARCHAR(255) NOT NULL, 
            ADD h12 VARCHAR(255) NOT NULL, 
            ADD h13 VARCHAR(255) NOT NULL, 
            ADD h14 VARCHAR(255) NOT NULL, 
            ADD h15 VARCHAR(255) NOT NULL, 
            ADD h16 VARCHAR(255) NOT NULL, 
            ADD h17 VARCHAR(255) NOT NULL, 
            ADD h18 VARCHAR(255) NOT NULL, 
            ADD h19 VARCHAR(255) NOT NULL, 
            ADD h20 VARCHAR(255) NOT NULL, 
            ADD h21 VARCHAR(255) NOT NULL, 
            ADD h22 VARCHAR(255) NOT NULL, 
            ADD h23 VARCHAR(255) NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_analytics_mooc_stats 
            DROP h0, 
            DROP h1, 
            DROP h2, 
            DROP h3, 
            DROP h4, 
            DROP h5, 
            DROP h6, 
            DROP h7, 
            DROP h8, 
            DROP h9, 
            DROP h10, 
            DROP h11, 
            DROP h12, 
            DROP h13, 
            DROP h14, 
            DROP h15, 
            DROP h16, 
            DROP h17, 
            DROP h18, 
            DROP h19, 
            DROP h20, 
            DROP h21, 
            DROP h22, 
            DROP h23
        ");
    }
}