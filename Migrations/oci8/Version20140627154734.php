<?php

namespace Claroline\CoreBundle\Migrations\oci8;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated migration based on mapping information: modify it with caution
 *
 * Generation date: 2014/06/27 03:47:36
 */
class Version20140627154734 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql("
            CREATE TABLE claro_mooc_category (
                id NUMBER(10) NOT NULL, 
                name VARCHAR2(255) NOT NULL, 
                PRIMARY KEY(id)
            )
        ");
        $this->addSql("
            DECLARE constraints_Count NUMBER; BEGIN 
            SELECT COUNT(CONSTRAINT_NAME) INTO constraints_Count 
            FROM USER_CONSTRAINTS 
            WHERE TABLE_NAME = 'CLARO_MOOC_CATEGORY' 
            AND CONSTRAINT_TYPE = 'P'; IF constraints_Count = 0 
            OR constraints_Count = '' THEN EXECUTE IMMEDIATE 'ALTER TABLE CLARO_MOOC_CATEGORY ADD CONSTRAINT CLARO_MOOC_CATEGORY_AI_PK PRIMARY KEY (ID)'; END IF; END;
        ");
        $this->addSql("
            CREATE SEQUENCE CLARO_MOOC_CATEGORY_ID_SEQ START WITH 1 MINVALUE 1 INCREMENT BY 1
        ");
        $this->addSql("
            CREATE TRIGGER CLARO_MOOC_CATEGORY_AI_PK BEFORE INSERT ON CLARO_MOOC_CATEGORY FOR EACH ROW DECLARE last_Sequence NUMBER; last_InsertID NUMBER; BEGIN 
            SELECT CLARO_MOOC_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; IF (
                : NEW.ID IS NULL 
                OR : NEW.ID = 0
            ) THEN 
            SELECT CLARO_MOOC_CATEGORY_ID_SEQ.NEXTVAL INTO : NEW.ID 
            FROM DUAL; ELSE 
            SELECT NVL(Last_Number, 0) INTO last_Sequence 
            FROM User_Sequences 
            WHERE Sequence_Name = 'CLARO_MOOC_CATEGORY_ID_SEQ'; 
            SELECT : NEW.ID INTO last_InsertID 
            FROM DUAL; WHILE (last_InsertID > last_Sequence) LOOP 
            SELECT CLARO_MOOC_CATEGORY_ID_SEQ.NEXTVAL INTO last_Sequence 
            FROM DUAL; END LOOP; END IF; END;
        ");
        $this->addSql("
            CREATE TABLE claro_moocs_to_categories (
                mooccategory_id NUMBER(10) NOT NULL, 
                mooc_id NUMBER(10) NOT NULL, 
                PRIMARY KEY(mooccategory_id, mooc_id)
            )
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7BC24924A ON claro_moocs_to_categories (mooccategory_id)
        ");
        $this->addSql("
            CREATE INDEX IDX_F7608CC7255EEB87 ON claro_moocs_to_categories (mooc_id)
        ");
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            ADD CONSTRAINT FK_F7608CC7BC24924A FOREIGN KEY (mooccategory_id) 
            REFERENCES claro_mooc_category (id) 
            ON DELETE CASCADE
        ");
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            ADD CONSTRAINT FK_F7608CC7255EEB87 FOREIGN KEY (mooc_id) 
            REFERENCES claro_mooc (id) 
            ON DELETE CASCADE
        ");
    }

    public function down(Schema $schema)
    {
        $this->addSql("
            ALTER TABLE claro_moocs_to_categories 
            DROP CONSTRAINT FK_F7608CC7BC24924A
        ");
        $this->addSql("
            DROP TABLE claro_mooc_category
        ");
        $this->addSql("
            DROP TABLE claro_moocs_to_categories
        ");
    }
}