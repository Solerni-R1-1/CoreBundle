<?php

namespace Claroline\CoreBundle\Migrations\pdo_sqlite;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/11/24 11:06:26
 */
class Version20141124110621 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN gender INTEGER NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN country VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN city VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN birthdate DATE DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN website VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN facebook VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN twitter VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN linkedIn VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user 
            ADD COLUMN googlePlus VARCHAR(255) DEFAULT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD COLUMN displayOptionalInformation BOOLEAN NOT NULL
        ");
        $this->addSql("
            ALTER TABLE claro_user_public_profile_preferences 
            ADD COLUMN displayBaseInformation BOOLEAN NOT NULL
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852F85E0677
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D28525126AC48
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D2852181F3A64
        ");
        $this->addSql("
            DROP INDEX UNIQ_EB8D285282D40A1F
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user AS 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            has_accepted_com_terms, 
            public_url, 
            has_tuned_public_url, 
            is_first_visit, 
            is_validate, 
            key_validate, 
            is_facebook_account 
            FROM claro_user
        ");
        $this->addSql("
            DROP TABLE claro_user
        ");
        $this->addSql("
            CREATE TABLE claro_user (
                id INTEGER NOT NULL, 
                workspace_id INTEGER DEFAULT NULL, 
                first_name VARCHAR(50) NOT NULL, 
                last_name VARCHAR(50) NOT NULL, 
                username VARCHAR(255) NOT NULL, 
                password VARCHAR(255) NOT NULL, 
                locale VARCHAR(255) DEFAULT NULL, 
                salt VARCHAR(255) NOT NULL, 
                phone VARCHAR(255) DEFAULT NULL, 
                mail VARCHAR(255) NOT NULL, 
                administrative_code VARCHAR(255) DEFAULT NULL, 
                creation_date DATETIME NOT NULL, 
                reset_password VARCHAR(255) DEFAULT NULL, 
                hash_time INTEGER DEFAULT NULL, 
                picture VARCHAR(255) DEFAULT NULL, 
                description CLOB DEFAULT NULL, 
                hasAcceptedTerms BOOLEAN DEFAULT NULL, 
                is_enabled BOOLEAN NOT NULL, 
                is_mail_notified BOOLEAN NOT NULL, 
                last_uri VARCHAR(255) DEFAULT NULL, 
                has_accepted_com_terms BOOLEAN DEFAULT NULL, 
                public_url VARCHAR(255) DEFAULT NULL, 
                has_tuned_public_url BOOLEAN NOT NULL, 
                is_first_visit BOOLEAN NOT NULL, 
                is_validate BOOLEAN NOT NULL, 
                key_validate VARCHAR(255) DEFAULT NULL, 
                is_facebook_account BOOLEAN DEFAULT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_EB8D285282D40A1F FOREIGN KEY (workspace_id) 
                REFERENCES claro_workspace (id) 
                ON DELETE SET NULL NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user (
                id, workspace_id, first_name, last_name, 
                username, password, locale, salt, 
                phone, mail, administrative_code, 
                creation_date, reset_password, hash_time, 
                picture, description, hasAcceptedTerms, 
                is_enabled, is_mail_notified, last_uri, 
                has_accepted_com_terms, public_url, 
                has_tuned_public_url, is_first_visit, 
                is_validate, key_validate, is_facebook_account
            ) 
            SELECT id, 
            workspace_id, 
            first_name, 
            last_name, 
            username, 
            password, 
            locale, 
            salt, 
            phone, 
            mail, 
            administrative_code, 
            creation_date, 
            reset_password, 
            hash_time, 
            picture, 
            description, 
            hasAcceptedTerms, 
            is_enabled, 
            is_mail_notified, 
            last_uri, 
            has_accepted_com_terms, 
            public_url, 
            has_tuned_public_url, 
            is_first_visit, 
            is_validate, 
            key_validate, 
            is_facebook_account 
            FROM __temp__claro_user
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852F85E0677 ON claro_user (username)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D28525126AC48 ON claro_user (mail)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D2852181F3A64 ON claro_user (public_url)
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_EB8D285282D40A1F ON claro_user (workspace_id)
        ");
        $this->addSql("
            DROP INDEX UNIQ_5CF2A583A76ED395
        ");
        $this->addSql("
            CREATE TEMPORARY TABLE __temp__claro_user_public_profile_preferences AS 
            SELECT id, 
            user_id, 
            share_policy, 
            display_phone_number, 
            display_email, 
            allow_mail_sending, 
            allow_message_sending 
            FROM claro_user_public_profile_preferences
        ");
        $this->addSql("
            DROP TABLE claro_user_public_profile_preferences
        ");
        $this->addSql("
            CREATE TABLE claro_user_public_profile_preferences (
                id INTEGER NOT NULL, 
                user_id INTEGER DEFAULT NULL, 
                share_policy INTEGER NOT NULL, 
                display_phone_number BOOLEAN NOT NULL, 
                display_email BOOLEAN NOT NULL, 
                allow_mail_sending BOOLEAN NOT NULL, 
                allow_message_sending BOOLEAN NOT NULL, 
                PRIMARY KEY(id), 
                CONSTRAINT FK_5CF2A583A76ED395 FOREIGN KEY (user_id) 
                REFERENCES claro_user (id) NOT DEFERRABLE INITIALLY IMMEDIATE
            )
        ");
        $this->addSql("
            INSERT INTO claro_user_public_profile_preferences (
                id, user_id, share_policy, display_phone_number, 
                display_email, allow_mail_sending, 
                allow_message_sending
            ) 
            SELECT id, 
            user_id, 
            share_policy, 
            display_phone_number, 
            display_email, 
            allow_mail_sending, 
            allow_message_sending 
            FROM __temp__claro_user_public_profile_preferences
        ");
        $this->addSql("
            DROP TABLE __temp__claro_user_public_profile_preferences
        ");
        $this->addSql("
            CREATE UNIQUE INDEX UNIQ_5CF2A583A76ED395 ON claro_user_public_profile_preferences (user_id)
        ");
    }
}