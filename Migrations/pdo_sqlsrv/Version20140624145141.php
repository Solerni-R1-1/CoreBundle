<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlsrv;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/24 02:51:44
 */
class Version20140624145141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN title NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN alias NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN desc_img NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN post_end_action INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN is_public BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN duration NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN weekly_time NVARCHAR(255)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN cost INT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN language NVARCHAR(10)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_video BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_subtitle BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN prerequisites VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN team_description VARCHAR(MAX)
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_facebook_share BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_tweeter_share BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_gplus_share BIT
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_linkin_share BIT
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN title NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN alias NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN description VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN desc_img NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN post_end_action INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN is_public BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN duration NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN weekly_time NVARCHAR(255) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN cost INT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN language NVARCHAR(10) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_video BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_subtitle BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN prerequisites VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN team_description VARCHAR(MAX) NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_facebook_share BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_tweeter_share BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_gplus_share BIT NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER COLUMN has_linkin_share BIT NOT NULL
        ");
    }
}