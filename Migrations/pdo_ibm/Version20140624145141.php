<?php

namespace Claroline\CoreBundle\Migrations\pdo_ibm;

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
            ALTER TABLE claro_mooc ALTER title title VARCHAR(255) DEFAULT NULL ALTER \"alias\" \"alias\" VARCHAR(255) DEFAULT NULL ALTER description description CLOB(1M) DEFAULT NULL ALTER desc_img desc_img VARCHAR(255) DEFAULT NULL ALTER post_end_action post_end_action INTEGER DEFAULT NULL ALTER is_public is_public SMALLINT DEFAULT NULL ALTER duration duration VARCHAR(255) DEFAULT NULL ALTER weekly_time weekly_time VARCHAR(255) DEFAULT NULL ALTER cost cost INTEGER DEFAULT NULL ALTER \"language\" \"language\" VARCHAR(10) DEFAULT NULL ALTER has_video has_video SMALLINT DEFAULT NULL ALTER has_subtitle has_subtitle SMALLINT DEFAULT NULL ALTER prerequisites prerequisites CLOB(1M) DEFAULT NULL ALTER team_description team_description CLOB(1M) DEFAULT NULL ALTER has_facebook_share has_facebook_share SMALLINT DEFAULT NULL ALTER has_tweeter_share has_tweeter_share SMALLINT DEFAULT NULL ALTER has_gplus_share has_gplus_share SMALLINT DEFAULT NULL ALTER has_linkin_share has_linkin_share SMALLINT DEFAULT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc ALTER title title VARCHAR(255) NOT NULL ALTER \"alias\" \"alias\" VARCHAR(255) NOT NULL ALTER description description CLOB(1M) NOT NULL ALTER desc_img desc_img VARCHAR(255) NOT NULL ALTER post_end_action post_end_action INTEGER NOT NULL ALTER is_public is_public SMALLINT NOT NULL ALTER duration duration VARCHAR(255) NOT NULL ALTER weekly_time weekly_time VARCHAR(255) NOT NULL ALTER cost cost INTEGER NOT NULL ALTER \"language\" \"language\" VARCHAR(10) NOT NULL ALTER has_video has_video SMALLINT NOT NULL ALTER has_subtitle has_subtitle SMALLINT NOT NULL ALTER prerequisites prerequisites CLOB(1M) NOT NULL ALTER team_description team_description CLOB(1M) NOT NULL ALTER has_facebook_share has_facebook_share SMALLINT NOT NULL ALTER has_tweeter_share has_tweeter_share SMALLINT NOT NULL ALTER has_gplus_share has_gplus_share SMALLINT NOT NULL ALTER has_linkin_share has_linkin_share SMALLINT NOT NULL
        ");
    }
}