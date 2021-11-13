<?php

use rhertogh\Yii2Oauth2Server\models\Oauth2Client;
use rhertogh\Yii2Oauth2Server\models\Oauth2Scope;
use yii\db\Migration;
use yii\db\Schema;

/**
 * phpcs:disable Squiz.Classes.ValidClassName.NotCamelCaps
 * phpcs:disable PSR1.Classes.ClassDeclaration.MissingNamespace
 * phpcs:disable Generic.Files.LineLength.TooLong
 */
class m210103_000000_oauth2_sample extends Migration
{
    public function safeUp()
    {
        $this->insert('{{user}}', [
            'username' => 'sample.user',
            'password_hash' => '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', # "password"
            'email_address' => 'test.user@test.test',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $sampleUserId = $this->db->lastInsertID;

        $this->insert('{{user}}', [
            'username' => 'sample.client_user',
            'password_hash' => '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', # "password"
            'email_address' => 'sample.client_user@test.test',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $sampleClientUserId = $this->db->lastInsertID;

        $this->insert('{{user}}', [
            'username' => 'sample.user.linked_identity',
            'password_hash' => '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', # "password"
            'email_address' => 'sample.user.linked_identity@test.test',
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $sampleUserLinkedIdentityId = $this->db->lastInsertID;

        $this->insert('{{user_identity_link}}', [
            'user_id' => $sampleUserId,
            'linked_user_id' => $sampleUserLinkedIdentityId,
        ]);

        $this->insert('{{oauth2_client}}', [
            'identifier' => 'sample-auth-code-client',
            'type' => 1, # Confidential
            'secret' => '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
            'name' => 'Valid client with Grant Type Auth Code',
            'redirect_uris' => '["http://localhost/redirect_uri/", "https://oauth.pstmn.io/v1/callback"]',
            'token_types' => 1, # Bearer
            'grant_types' => 5, # AUTH_CODE | REFRESH_TOKEN
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $authCodeClientId = $this->db->lastInsertID;

        $this->insert('{{oauth2_client}}', [
            'identifier' => 'sample-client-credentials-client',
            'type' => 1, # Confidential
            'secret' => '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
            'name' => 'Valid client with Grant Type client credentials',
            'redirect_uris' => '["http://localhost/redirect_uri/", "https://oauth.pstmn.io/v1/callback"]',
            'token_types' => 1, # Bearer
            'grant_types' => 2, # CLIENT_CREDENTIALS
            'client_credentials_grant_user_id' => $sampleClientUserId,
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $clientCredentialsClientId = $this->db->lastInsertID;

        $this->insert('{{oauth2_client}}', [
            'identifier' => 'sample-password-public-client',
            'type' => 2, # Public
            'name' => 'Valid client with Grant Type password',
            'redirect_uris' => '["http://localhost/redirect_uri/", "https://oauth.pstmn.io/v1/callback"]',
            'token_types' => 1, # Bearer
            'grant_types' => 1024, # PASSWORD
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);
        $this->insert('{{oauth2_client}}', [
            'identifier' => 'sample-implicit-client',
            'type' => 1, # Confidential
            'secret' => '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
            'name' => 'Valid client with Grant Type implicit',
            'redirect_uris' => '["http://localhost/redirect_uri/", "https://oauth.pstmn.io/v1/callback"]',
            'token_types' => 1, # Bearer
            'grant_types' => 2048, # IMPLICIT
            'enabled' => 1,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        $this->batchInsert(
            '{{oauth2_scope}}',
            [
                'identifier',
                'description',
                'authorization_message',
                'created_at',
                'updated_at',
            ],
            [
                [
                    'user.username.read',
                    'Read username',
                    'See your username',
                    time(),
                    time(),
                ],
                [
                    'user.email_address.read',
                    'Read email address',
                    'See your email address',
                    time(),
                    time(),
                ],
            ]
        );

        $scopes = Oauth2Scope::findAll(['identifier' => [
            'user.username.read',
            'user.email_address.read',
        ]]);

        foreach ([$authCodeClientId, $clientCredentialsClientId] as $clientId) {
            foreach ($scopes as $scope) {
                $this->insert(
                    '{{oauth2_client_scope}}',
                    [
                        'client_id' => $clientId,
                        'scope_id' => $scope->id,
                        'created_at' => time(),
                        'updated_at' => time(),
                    ]
                );
            }
        }

        $this->insert('{{oauth2_user_client}}', [
            'user_id' => $sampleClientUserId,
            'client_id' => $clientCredentialsClientId,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        foreach ($scopes as $scope) {
            $this->insert('{{oauth2_user_client_scope}}', [
                'user_id' => $sampleClientUserId,
                'client_id' => $clientCredentialsClientId,
                'scope_id' => $scope->id,
                'created_at' => time(),
                'updated_at' => time(),
            ]);
        }
    }

    public function safeDown()
    {
    }
}
