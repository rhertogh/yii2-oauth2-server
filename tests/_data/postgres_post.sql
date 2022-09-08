/**
 * This is the database schema for testing PostgreSQL support.
 * The database setup in config.php is required to perform then relevant tests:
 */
ALTER SEQUENCE oauth2_access_token_id_seq RESTART WITH 1000; -- oauth2_access_token fixture range: 1001000
ALTER SEQUENCE oauth2_auth_code_id_seq RESTART WITH 2000; -- oauth2_auth_code fixture range: 1002000
ALTER SEQUENCE oauth2_client_id_seq RESTART WITH 3000; -- oauth2_client fixture range: 1003000
ALTER SEQUENCE oauth2_refresh_token_id_seq RESTART WITH 4000; -- oauth2_refresh_token fixture range: 1004000
ALTER SEQUENCE oauth2_scope_id_seq RESTART WITH 5000; -- oauth2_scope fixture range: 1005000

SET SESSION Yii2Oauth2Server.PostMigration.oidcScopeId = 1;
SET SESSION Yii2Oauth2Server.PostMigration.oidcProfileScopeId = 2;
SET SESSION Yii2Oauth2Server.PostMigration.oidcEmailScopeId = 3;
SET SESSION Yii2Oauth2Server.PostMigration.oidcAddressScopeId = 4;
SET SESSION Yii2Oauth2Server.PostMigration.oidcPhoneScopeId = 5;
SET SESSION Yii2Oauth2Server.PostMigration.oidcOfflineAccessScopeId = 6;

INSERT INTO "oauth2_client"
    (
        "id",
        "identifier",
        "type",
        "secret",
        "name",
        "redirect_uris",
        "token_types",
        "grant_types",
        "skip_authorization_if_scope_is_allowed",
        "client_credentials_grant_user_id",
        "enabled",
        "created_at",
        "updated_at"
    )
    VALUES
    (
        1003000,
        'test-client-type-auth-code-valid',
        1, -- Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', -- "secret"
        'Valid client with Grant Type Auth Code',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        5, -- AUTH_CODE | REFRESH_TOKEN
        false,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003001,
        'test-client-type-client-credentials-valid',
        1, -- Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', -- "secret"
        'Valid client with Grant Type client credentials',
        null,
        1, -- Bearer
        2, -- CLIENT_CREDENTIALS // refresh token SHOULD NOT be included: https://datatracker.ietf.org/doc/html/rfc6749--section-4.4.3
        false,
        123,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002,
        'test-client-type-password-public-valid',
        2, -- Public
        NULL,
        'Valid client with Grant Type password',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        1028, -- PASSWORD | REFRESH_TOKEN
        false,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003003,
        'test-client-type-implicit-valid',
        1, -- Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', -- "secret",
        'Valid client with Grant Type Implicit',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        2052, -- IMPLICIT // The authorization server MUST NOT issue a refresh token: https://datatracker.ietf.org/doc/html/rfc6749--section-4.2.2
        false,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003004,
        'test-client-type-auth-code-disabled',
        1, -- Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', -- "secret"
        'Disabled client with Grant Type Auth Code',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        5, -- AUTH_CODE | REFRESH_TOKEN
        false,
        null,
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003005,
        'test-client-type-auth-code-no-scopes',
        2, -- Public
        null,
        'Valid public client with Grant Type Auth Code without scopes',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        5, -- AUTH_CODE | REFRESH_TOKEN
        false,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006,
        'test-client-type-auth-code-open-id-connect',
        2, -- Public
        null,
        'Valid client with Grant Type Auth Code and OpenID connect',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        5, -- AUTH_CODE | REFRESH_TOKEN
        false,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        -- Note: for testing the offline access scope is not auto applied
        1003007,
        'test-client-type-auth-code-open-id-connect-skip-authorization',
        2, -- Public
        null,
        'Valid client with Grant Type Auth Code and OpenID connect skip authorization if scope allowed',
        '["http://localhost/redirect_uri/"]',
        1, -- Bearer
        5, -- AUTH_CODE | REFRESH_TOKEN
        true,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO "oauth2_scope" VALUES
    (
        1005000,
        'user.id.read',
        'Read user id',
        'See your user id.',
        1,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005001,
        'user.username.read',
        'Read username',
        'See your username.',
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005002,
        'user.email_address.read',
        'Read user email address',
        'See your email address.',
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005003,
        'user.enabled.read',
        'Read enabled status',
        'See if your account is enabled.',
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005004,
        'user.created_at.read',
        'Read created_at timestamp',
        'See when your account was created.',
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005005,
        'user.updated_at.read',
        'Read updated_at timestamp',
        'See when your account was last changed.',
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005006,
        'disabled-scope',
        'disabled scope',
        null,
        0,
        true,
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005007,
        'disabled-scope-for-client',
        'disabled scope for client (enabled itself, but disabled in oauth2_client_scope)',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005008,
        'defined-but-not-assigned',
        'defined, but not assigned to any client',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005009,
        'applied-by-default',
        'applied by default based on client.scope_access',
        null,
        1,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005010,
        'applied-by-default-for-client',
        'applied by default based for client (not applied itself, but applied in oauth2_client_scope)',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005011,
        'applied-automatically-by-default',
        'applied automatically (no user confirmation) by default based on client',
        null,
        2,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005012,
        'applied-automatically-by-default-for-client',
        'applied automatically (no user confirmation) by default for client (not applied itself, but applied in oauth2_client_scope)',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005013,
        'applied-automatically-by-default-not-assigned',
        'applied automatically (no user confirmation) by default not assigned to client',
        null,
        2,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005014,
        'applied-automatically-by-default-not-assigned-not-required',
        'applied automatically (no user confirmation) by default not assigned to client and not required',
        null,
        2,
        false,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005015,
        'applied-by-default-by-client-not-required-for-client',
        'required by default, but not required for client',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005016,
        'pre-assigned-for-user-test',
        'previously assigned for user test',
        null,
        0,
        true,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005017,
        'not-required',
        'not required',
        null,
        0,
        false,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1005018,
        'not-required-has-been-rejected-before',
        'not required, rejected before for some test users',
        null,
        0,
        false,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );
;

INSERT INTO "oauth2_client_scope" VALUES
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005000, -- 'user.id.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005001, -- 'user.username.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005002, -- 'user.email_address.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005003, -- 'user.enabled.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005004, -- 'user.created_at.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005005, -- 'user.updated_at.read'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005006, -- 'disabled-scope'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005007, -- 'disabled-scope-for-client'
       null,
       null,
       false,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005009, -- 'applied-by-default'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005010, -- 'applied-by-default-for-client'
       1,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005011, -- 'applied-automatically-by-default'
       null,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005012, -- 'applied-automatically-by-default-for-client'
       2,
       null,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
       1003000, -- 'test-client-type-auth-code-valid'
       1005015, -- 'applied-automatically-by-client-not-required-for-client'
       1,
       false,
       true,
       EXTRACT(EPOCH FROM NOW()),
       EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003000, -- 'test-client-type-auth-code-valid'
        1005016, -- 'pre-assigned-for-user-test'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003000, -- 'test-client-type-auth-code-valid'
        1005017, -- 'not-required',
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    -- test-client-type-client-credentials-valid
    (
        1003001, -- 'test-client-type-client-credentials-valid'
        1005000, -- 'user.id.read'
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003001, -- 'test-client-type-client-credentials-valid'
        1005001, -- 'user.username.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003001, -- 'test-client-type-client-credentials-valid'
        1005002, -- 'user.email_address.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003001, -- 'test-client-type-client-credentials-valid'
        1005003, -- 'user.enabled.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    -- test-client-type-password-public-valid
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005000, -- 'user.id.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005001, -- 'user.username.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005002, -- 'user.email_address.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005003, -- 'user.enabled.read'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005012, -- 'applied-automatically-by-default-for-client'
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005017, -- 'not-required',
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003002, -- 'test-client-type-password-public-valid'
        1005018, -- 'not-required-has-been-rejected-before'
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    -- test-client-type-auth-code-open-id-connect
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcProfileScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcEmailScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcAddressScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcPhoneScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003006, -- 'test-client-type-auth-code-open-id-connect'
        current_setting('Yii2Oauth2Server.PostMigration.oidcOfflineAccessScopeId')::int,
        null,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    -- test-client-type-auth-code-open-id-connect-skip-authorization ,
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcScopeId')::int,
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcProfileScopeId')::int,
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcEmailScopeId')::int,
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcAddressScopeId')::int,
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcPhoneScopeId')::int,
        2,
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        current_setting('Yii2Oauth2Server.PostMigration.oidcOfflineAccessScopeId')::int,
        0, -- Note: for testing the offline access scope is not auto applied
        null,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO "oauth2_access_token"
    (
        id,
        identifier,
        client_id,
        user_id,
        type,
        expiry_date_time,
        enabled,
        created_at,
        updated_at
    )
    VALUES
    (
        1001000,
        'test-access-token-bearer-active',
        1003000,
        123,
        1, -- Bearer
        '2222-01-01',
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1001001,
        'test-access-token-bearer-expired',
        1003000,
        123,
        1, -- Bearer
        '2021-01-01',
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1001002,
        'test-access-token-bearer-disabled',
        1003000,
        123,
        1, -- Bearer
        '2222-01-01',
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO "oauth2_auth_code"
    (
        id,
        identifier,
        redirect_uri,
        expiry_date_time,
        client_id,
        user_id,
        enabled,
        created_at,
        updated_at
    )
    VALUES
    (
        1002000,
        'test-auth-code-valid',
        'http://localhost/redirect_uri/',
        '2222-01-01',
        1003000,
        123,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1002001,
        'test-auth-code-expired',
        'http://localhost/redirect_uri/',
        '2021-01-01',
        1003000,
        123,
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1002002,
        'test-auth-code-disabled',
        'http://localhost/redirect_uri/',
        '2222-01-01',
        1003000,
        123,
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO "oauth2_refresh_token"
    (
        id,
        access_token_id,
        identifier,
        expiry_date_time,
        enabled,
        created_at,
        updated_at
    )
    VALUES
    (
        1004000,
        1001000,
        'test-refresh-token-valid',
        '2222-01-01',
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1004001,
        1001000,
        'test-refresh-token-expired',
        '2021-01-01',
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        1004002,
        1001000,
        'test-refresh-token-disabled',
        '2222-01-01',
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO oauth2_user_client VALUES
    (
        123, -- test.user
        1003001, -- 'test-client-type-client-credentials-valid'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003000, -- 'test-client-type-auth-code-valid'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003002, -- 'test-client-type-password-public-valid'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003007, -- 'test-client-type-auth-code-open-id-connect-skip-authorization'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );

INSERT INTO oauth2_user_client_scope VALUES
    (
        123, -- test.user
        1003001, -- 'test-client-type-client-credentials-valid'
        1005001, -- 'user.username.read'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003000, -- 'test-client-type-auth-code-valid'
        1005016, -- 'pre-assigned-for-user-test'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003002, -- 'test-client-type-password-public-valid'
        1005000, -- 'user.id.read'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003002, -- 'test-client-type-password-public-valid'
        1005001, -- 'user.username.read'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003002, -- 'test-client-type-password-public-valid'
        1005002, -- 'user.email_address.read'
        true,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    ),
    (
        124, -- test.user2
        1003002, -- 'test-client-type-password-public-valid'
        1005018, -- 'not-required-has-been-rejected-before',
        false,
        EXTRACT(EPOCH FROM NOW()),
        EXTRACT(EPOCH FROM NOW())
    );
