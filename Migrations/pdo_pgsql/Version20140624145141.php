<?php

namespace Claroline\CoreBundle\Migrations\pdo_pgsql;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/24 02:51:43
 */
class Version20140624145141 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER title 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER alias 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER description 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER desc_img 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER post_end_action 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER is_public 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER duration 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER weekly_time 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER cost 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER language 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_video 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_subtitle 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER prerequisites 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER team_description 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_facebook_share 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_tweeter_share 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_gplus_share 
            DROP NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_linkin_share 
            DROP NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER title 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER alias 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER description 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER desc_img 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER post_end_action 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER is_public 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER duration 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER weekly_time 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER cost 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER language 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_video 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_subtitle 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER prerequisites 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER team_description 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_facebook_share 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_tweeter_share 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_gplus_share 
            SET 
                NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_mooc ALTER has_linkin_share 
            SET 
                NOT NULL
        ");
    }
}