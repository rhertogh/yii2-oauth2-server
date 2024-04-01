<?php

use yii\db\Migration;

/**
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 */
class m210101_000000_create_user_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{user}}', [
            'id' => $this->primaryKey(),
            'username' => $this->string()->notNull(),
            'password_hash' => $this->string()->notNull(),
            'email_address' => $this->string()->notNull(),
            'latest_authenticated_at' => $this->integer(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        if ($this->db->getDriverName() === 'sqlite') {

            $this->createTable('{{user_identity_link}}', [
                'user_id' => $this->integer()->notNull(),
                'linked_user_id' => $this->integer()->notNull(),
                'PRIMARY KEY (user_id, linked_user_id)',
                'FOREIGN KEY (user_id) REFERENCES user(id)',
                'FOREIGN KEY (linked_user_id) REFERENCES user(id)',
            ]);

        } else {

            $this->createTable('{{user_identity_link}}', [
                'user_id' => $this->integer()->notNull(),
                'linked_user_id' => $this->integer()->notNull(),
                'PRIMARY KEY (user_id, linked_user_id)',
            ]);

            $this->addForeignKey(
                'linked_identity_user',
                '{{user_identity_link}}',
                'user_id',
                'user',
                'id',
                'CASCADE',
                'CASCADE',
            );
            $this->addForeignKey(
                'linked_identity_linked_user',
                '{{user_identity_link}}',
                'linked_user_id',
                'user',
                'id',
                'CASCADE',
                'CASCADE',
            );
        }
    }

    public function safeDown()
    {
        $this->dropTable('{{user_identity_link}}');
        $this->dropTable('{{user}}');
    }
}
