<?php

namespace Claroline\CoreBundle\Migrations\drizzle_pdo_mysql;

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
            ALTER TABLE claro_mooc CHANGE title title VARCHAR(255) DEFAULT NULL, 
            CHANGE alias alias VARCHAR(255) DEFAULT NULL, 
            CHANGE description description TEXT DEFAULT NULL, 
            CHANGE desc_img desc_img VARCHAR(255) DEFAULT NULL, 
            CHANGE post_end_action post_end_action INT DEFAULT NULL, 
            CHANGE is_public is_public BOOLEAN DEFAULT NULL, 
            CHANGE duration duration VARCHAR(255) DEFAULT NULL, 
            CHANGE weekly_time weekly_time VARCHAR(255) DEFAULT NULL, 
            CHANGE cost cost INT DEFAULT NULL, 
            CHANGE `language` `language` VARCHAR(10) DEFAULT NULL, 
            CHANGE has_video has_video BOOLEAN DEFAULT NULL, 
            CHANGE has_subtitle has_subtitle BOOLEAN DEFAULT NULL, 
            CHANGE prerequisites prerequisites TEXT DEFAULT NULL, 
            CHANGE team_description team_description TEXT DEFAULT NULL, 
            CHANGE has_facebook_share has_facebook_share BOOLEAN DEFAULT NULL, 
            CHANGE has_tweeter_share has_tweeter_share BOOLEAN DEFAULT NULL, 
            CHANGE has_gplus_share has_gplus_share BOOLEAN DEFAULT NULL, 
            CHANGE has_linkin_share has_linkin_share BOOLEAN DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc CHANGE title title VARCHAR(255) NOT NULL, 
            CHANGE alias alias VARCHAR(255) NOT NULL, 
            CHANGE description description TEXT NOT NULL, 
            CHANGE desc_img desc_img VARCHAR(255) NOT NULL, 
            CHANGE post_end_action post_end_action INT NOT NULL, 
            CHANGE is_public is_public BOOLEAN NOT NULL, 
            CHANGE duration duration VARCHAR(255) NOT NULL, 
            CHANGE weekly_time weekly_time VARCHAR(255) NOT NULL, 
            CHANGE cost cost INT NOT NULL, 
            CHANGE `language` `language` VARCHAR(10) NOT NULL, 
            CHANGE has_video has_video BOOLEAN NOT NULL, 
            CHANGE has_subtitle has_subtitle BOOLEAN NOT NULL, 
            CHANGE prerequisites prerequisites TEXT NOT NULL, 
            CHANGE team_description team_description TEXT NOT NULL, 
            CHANGE has_facebook_share has_facebook_share BOOLEAN NOT NULL, 
            CHANGE has_tweeter_share has_tweeter_share BOOLEAN NOT NULL, 
            CHANGE has_gplus_share has_gplus_share BOOLEAN NOT NULL, 
            CHANGE has_linkin_share has_linkin_share BOOLEAN NOT NULL
        ");
    }
}