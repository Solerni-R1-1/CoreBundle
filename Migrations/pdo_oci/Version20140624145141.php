<?php

namespace Claroline\CoreBundle\Migrations\pdo_oci;

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
            ALTER TABLE claro_mooc MODIFY (
                title VARCHAR2(255) DEFAULT NULL, 
                alias VARCHAR2(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                desc_img VARCHAR2(255) DEFAULT NULL, 
                post_end_action NUMBER(10) DEFAULT NULL, 
                is_public NUMBER(1) DEFAULT NULL, 
                duration VARCHAR2(255) DEFAULT NULL, 
                weekly_time VARCHAR2(255) DEFAULT NULL, 
                cost NUMBER(10) DEFAULT NULL, 
                language VARCHAR2(10) DEFAULT NULL, 
                has_video NUMBER(1) DEFAULT NULL, 
                has_subtitle NUMBER(1) DEFAULT NULL, 
                prerequisites CLOB DEFAULT NULL, 
                team_description CLOB DEFAULT NULL, 
                has_facebook_share NUMBER(1) DEFAULT NULL, 
                has_tweeter_share NUMBER(1) DEFAULT NULL, 
                has_gplus_share NUMBER(1) DEFAULT NULL, 
                has_linkin_share NUMBER(1) DEFAULT NULL
            )
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_mooc MODIFY (
                title VARCHAR2(255) NOT NULL, 
                alias VARCHAR2(255) NOT NULL, 
                description CLOB NOT NULL, 
                desc_img VARCHAR2(255) NOT NULL, 
                post_end_action NUMBER(10) NOT NULL, 
                is_public NUMBER(1) NOT NULL, 
                duration VARCHAR2(255) NOT NULL, 
                weekly_time VARCHAR2(255) NOT NULL, 
                cost NUMBER(10) NOT NULL, 
                language VARCHAR2(10) NOT NULL, 
                has_video NUMBER(1) NOT NULL, 
                has_subtitle NUMBER(1) NOT NULL, 
                prerequisites CLOB NOT NULL, 
                team_description CLOB NOT NULL, 
                has_facebook_share NUMBER(1) NOT NULL, 
                has_tweeter_share NUMBER(1) NOT NULL, 
                has_gplus_share NUMBER(1) NOT NULL, 
                has_linkin_share NUMBER(1) NOT NULL
            )
        ");
    }
}