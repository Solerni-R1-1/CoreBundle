<?php

namespace Claroline\CoreBundle\Migrations\pdo_mysql;

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
            ALTER TABLE claro_mooc CHANGE title title VARCHAR(255) DEFAULT NULL, 
            CHANGE alias alias VARCHAR(255) DEFAULT NULL, 
            CHANGE description description LONGTEXT DEFAULT NULL, 
            CHANGE desc_img desc_img VARCHAR(255) DEFAULT NULL, 
            CHANGE post_end_action post_end_action INT DEFAULT NULL, 
            CHANGE is_public is_public TINYINT(1) DEFAULT NULL, 
            CHANGE duration duration VARCHAR(255) DEFAULT NULL, 
            CHANGE weekly_time weekly_time VARCHAR(255) DEFAULT NULL, 
            CHANGE cost cost INT DEFAULT NULL, 
            CHANGE language language VARCHAR(10) DEFAULT NULL, 
            CHANGE has_video has_video TINYINT(1) DEFAULT NULL, 
            CHANGE has_subtitle has_subtitle TINYINT(1) DEFAULT NULL, 
            CHANGE prerequisites prerequisites LONGTEXT DEFAULT NULL, 
            CHANGE team_description team_description LONGTEXT DEFAULT NULL, 
            CHANGE has_facebook_share has_facebook_share TINYINT(1) DEFAULT NULL, 
            CHANGE has_tweeter_share has_tweeter_share TINYINT(1) DEFAULT NULL, 
            CHANGE has_gplus_share has_gplus_share TINYINT(1) DEFAULT NULL, 
            CHANGE has_linkin_share has_linkin_share TINYINT(1) DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc CHANGE title title VARCHAR(255) NOT NULL, 
            CHANGE alias alias VARCHAR(255) NOT NULL, 
            CHANGE description description LONGTEXT NOT NULL, 
            CHANGE desc_img desc_img VARCHAR(255) NOT NULL, 
            CHANGE post_end_action post_end_action INT NOT NULL, 
            CHANGE is_public is_public TINYINT(1) NOT NULL, 
            CHANGE duration duration VARCHAR(255) NOT NULL, 
            CHANGE weekly_time weekly_time VARCHAR(255) NOT NULL, 
            CHANGE cost cost INT NOT NULL, 
            CHANGE language language VARCHAR(10) NOT NULL, 
            CHANGE has_video has_video TINYINT(1) NOT NULL, 
            CHANGE has_subtitle has_subtitle TINYINT(1) NOT NULL, 
            CHANGE prerequisites prerequisites LONGTEXT NOT NULL, 
            CHANGE team_description team_description LONGTEXT NOT NULL, 
            CHANGE has_facebook_share has_facebook_share TINYINT(1) NOT NULL, 
            CHANGE has_tweeter_share has_tweeter_share TINYINT(1) NOT NULL, 
            CHANGE has_gplus_share has_gplus_share TINYINT(1) NOT NULL, 
            CHANGE has_linkin_share has_linkin_share TINYINT(1) NOT NULL
        ");
    }
}