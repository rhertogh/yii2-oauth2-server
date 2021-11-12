/**
 * This is the database schema for testing MySQL support of Yii DAO and Active Record.
 * The database setup in config.php is required to perform then relevant tests:
 */

ALTER TABLE `oauth2_access_token` AUTO_INCREMENT = 1000; # oauth2_access_token fixture range: 1001000
ALTER TABLE `oauth2_auth_code` AUTO_INCREMENT = 2000; # oauth2_auth_code fixture range: 1002000
ALTER TABLE `oauth2_client` AUTO_INCREMENT = 3000; # oauth2_client fixture range: 1003000
ALTER TABLE `oauth2_refresh_token` AUTO_INCREMENT = 4000; # oauth2_refresh_token fixture range: 1004000
ALTER TABLE `oauth2_scope` AUTO_INCREMENT = 5000; # oauth2_scope fixture range: 1005000

SELECT @oidcScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'openid';
SELECT @oidcProfileScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'profile';
SELECT @oidcEmailScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'email';
SELECT @oidcAddressScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'address';
SELECT @oidcPhoneScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'phone';
SELECT @oidcOfflineAccessScopeId := `id` FROM `oauth2_scope` WHERE `identifier` = 'offline_access';

INSERT INTO `oauth2_client`
    (
        id,
        identifier,
        type,
        secret,
        name,
        redirect_uris,
        token_types,
        grant_types,
        skip_authorization_if_scope_is_allowed,
        client_credentials_grant_user_id,
        enabled,
        created_at,
        updated_at
    )
    VALUES
    (
        1003000,
        'test-client-type-auth-code-valid',
        1, # Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
        'Valid client with Grant Type Auth Code',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        5, # AUTH_CODE | REFRESH_TOKEN
        1,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003001,
        'test-client-type-client-credentials-valid',
        1, # Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
        'Valid client with Grant Type client credentials',
        null,
        1, # Bearer
        2, # CLIENT_CREDENTIALS // refresh token SHOULD NOT be included: https://datatracker.ietf.org/doc/html/rfc6749#section-4.4.3
        1,
        123,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002,
        'test-client-type-password-public-valid',
        2, # Public
        NULL,
        'Valid client with Grant Type password',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        1028, # PASSWORD | REFRESH_TOKEN
        1,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003003,
        'test-client-type-implicit-valid',
        1, # Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret",
        'Valid client with Grant Type Implicit',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        2052, # IMPLICIT // The authorization server MUST NOT issue a refresh token: https://datatracker.ietf.org/doc/html/rfc6749#section-4.2.2
        1,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003004,
        'test-client-type-auth-code-disabled',
        1, # Confidential
        '2021-01-01::3vUCADtKx59NPQl3/1fJXmppRbiug3iccJc1S9XY6TPvLE02/+ggB8GtIc24J5oMTj38NIPIpNt8ClNDS7ZBI4+ykNxYOuEHQfdkDiUf5WVKtLegx43gLXfq', # "secret"
        'Disabled client with Grant Type Auth Code',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        5, # AUTH_CODE | REFRESH_TOKEN
        1,
        null,
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003005,
        'test-client-type-auth-code-no-scopes',
        2, # Public
        null,
        'Valid public client with Grant Type Auth Code without scopes',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        5, # AUTH_CODE | REFRESH_TOKEN
        1,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006,
        'test-client-type-auth-code-open-id-connect',
        2, # Public
        null,
        'Valid client with Grant Type Auth Code and OpenID connect',
        '["http://localhost/redirect_uri/"]',
        1, # Bearer
        5, # AUTH_CODE | REFRESH_TOKEN
        1,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO `oauth2_scope` VALUES
    (
        1005000,
        'user.id.read',
        'Read user id',
        'See your user id.',
        1,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005001,
        'user.username.read',
        'Read username',
        'See your username.',
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005002,
        'user.email_address.read',
        'Read user email address',
        'See your email address.',
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005003,
        'user.enabled.read',
        'Read enabled status',
        'See if your account is enabled.',
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005004,
        'user.created_at.read',
        'Read created_at timestamp',
        'See when your account was created.',
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005005,
        'user.updated_at.read',
        'Read updated_at timestamp',
        'See when your account was last changed.',
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005006,
        'disabled-scope',
        'disabled scope',
        null,
        0,
        1,
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005007,
        'disabled-scope-for-client',
        'disabled scope for client (enabled itself, but disabled in oauth2_client_scope)',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005008,
        'defined-but-not-assigned',
        'defined, but not assigned to any client',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005009,
        'applied-by-default',
        'applied by default based on client.scope_access',
        null,
        1,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005010,
        'applied-by-default-for-client',
        'applied by default based for client (not applied itself, but applied in oauth2_client_scope)',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005011,
        'applied-automatically-by-default',
        'applied automatically (no user confirmation) by default based on client',
        null,
        2,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005012,
        'applied-automatically-by-default-for-client',
        'applied automatically (no user confirmation) by default for client (not applied itself, but applied in oauth2_client_scope)',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005013,
        'applied-automatically-by-default-not-assigned',
        'applied automatically (no user confirmation) by default not assigned to client',
        null,
        2,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005014,
        'applied-automatically-by-default-not-assigned-not-required',
        'applied automatically (no user confirmation) by default not assigned to client and not required',
        null,
        2,
        0,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005015,
        'applied-by-default-by-client-not-required-for-client',
        'required by default, but not required for client',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005016,
        'pre-assigned-for-user-test',
        'previously assigned for user test',
        null,
        0,
        1,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005017,
        'not-required',
        'not required',
        null,
        0,
        0,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1005018,
        'not-required-has-been-rejected-before',
        'not required, rejected before for some test users',
        null,
        0,
        0,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );
;

INSERT INTO `oauth2_client_scope` VALUES
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005000, # 'user.id.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005001, # 'user.username.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005002, # 'user.email_address.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005003, # 'user.enabled.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005004, # 'user.created_at.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005005, # 'user.updated_at.read'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005006, # 'disabled-scope'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005007, # 'disabled-scope-for-client'
       null,
       null,
       0,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005009, # 'applied-by-default'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005010, # 'applied-by-default-for-client'
       1,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005011, # 'applied-automatically-by-default'
       null,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005012, # 'applied-automatically-by-default-for-client'
       2,
       null,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
       1003000, # 'test-client-type-auth-code-valid'
       1005015, # 'applied-automatically-by-client-not-required-for-client'
       1,
       0,
       1,
       UNIX_TIMESTAMP(),
       UNIX_TIMESTAMP()
    ),
    (
        1003000, # 'test-client-type-auth-code-valid'
        1005016, # 'pre-assigned-for-user-test'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003000, # 'test-client-type-auth-code-valid'
        1005017, # 'not-required',
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    # test-client-type-client-credentials-valid
    (
        1003001, # 'test-client-type-client-credentials-valid'
        1005000, # 'user.id.read'
        2,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003001, # 'test-client-type-client-credentials-valid'
        1005001, # 'user.username.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003001, # 'test-client-type-client-credentials-valid'
        1005002, # 'user.email_address.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003001, # 'test-client-type-client-credentials-valid'
        1005003, # 'user.enabled.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    # test-client-type-password-public-valid
    (
        1003002, # 'test-client-type-password-public-valid'
        1005000, # 'user.id.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005001, # 'user.username.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005002, # 'user.email_address.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005003, # 'user.enabled.read'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005012, # 'applied-automatically-by-default-for-client'
        2,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005017, # 'not-required',
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003002, # 'test-client-type-password-public-valid'
        1005018, # 'not-required-has-been-rejected-before'
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    # test-client-type-auth-code-open-id-connect
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcProfileScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcEmailScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcAddressScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcPhoneScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1003006, # 'test-client-type-auth-code-open-id-connect'
        @oidcOfflineAccessScopeId,
        null,
        null,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO `oauth2_access_token`
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
        1, # Bearer
        '2222-01-01',
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1001001,
        'test-access-token-bearer-expired',
        1003000,
        123,
        1, # Bearer
        '2021-01-01',
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1001002,
        'test-access-token-bearer-disabled',
        1003000,
        123,
        1, # Bearer
        '2222-01-01',
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO `oauth2_auth_code`
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
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1002001,
        'test-auth-code-expired',
        'http://localhost/redirect_uri/',
        '2021-01-01',
        1003000,
        123,
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1002002,
        'test-auth-code-disabled',
        'http://localhost/redirect_uri/',
        '2222-01-01',
        1003000,
        123,
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO `oauth2_refresh_token`
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
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1004001,
        1001000,
        'test-refresh-token-expired',
        '2021-01-01',
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        1004002,
        1001000,
        'test-refresh-token-disabled',
        '2222-01-01',
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO oauth2_user_client VALUES
    (
        123, # test.user
        1003001, # 'test-client-type-client-credentials-valid'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003000, # 'test-client-type-auth-code-valid'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003002, # 'test-client-type-password-public-valid'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );

INSERT INTO oauth2_user_client_scope VALUES
    (
        123, # test.user
        1003001, # 'test-client-type-client-credentials-valid'
        1005001, # 'user.username.read'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003000, # 'test-client-type-auth-code-valid'
        1005016, # 'pre-assigned-for-user-test'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003002, # 'test-client-type-password-public-valid'
        1005000, # 'user.id.read'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003002, # 'test-client-type-password-public-valid'
        1005001, # 'user.username.read'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003002, # 'test-client-type-password-public-valid'
        1005002, # 'user.email_address.read'
        1,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    ),
    (
        124, # test.user2
        1003002, # 'test-client-type-password-public-valid'
        1005018, # 'not-required-has-been-rejected-before',
        0,
        UNIX_TIMESTAMP(),
        UNIX_TIMESTAMP()
    );
