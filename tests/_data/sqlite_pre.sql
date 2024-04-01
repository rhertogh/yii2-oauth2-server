/**
 * This is the database schema for testing SQLite support.
 * The database setup in config.php is required to perform then relevant tests:
 */

DROP TABLE IF EXISTS `migration`;

DROP TABLE IF EXISTS `oauth2_user_client_scope`;
DROP TABLE IF EXISTS `oauth2_user_client`;
DROP TABLE IF EXISTS `oauth2_access_token_scope`;
DROP TABLE IF EXISTS `oauth2_auth_code_scope`;
DROP TABLE IF EXISTS `oauth2_auth_code`;
DROP TABLE IF EXISTS `oauth2_client_scope`;
DROP TABLE IF EXISTS `oauth2_refresh_token`;
DROP TABLE IF EXISTS `oauth2_access_token`;
DROP TABLE IF EXISTS `oauth2_client`;
DROP TABLE IF EXISTS `oauth2_scope`;

DROP TABLE IF EXISTS `user_identity_link`; -- in case the sample migrations were run against the test database
DROP TABLE IF EXISTS `user`;

CREATE TABLE `user`
(
    `id` INTEGER PRIMARY KEY AUTOINCREMENT,
    `username` VARCHAR(255) NOT NULL,
    `password_hash` VARCHAR(255) NOT NULL,
    `email_address` VARCHAR(255) NOT NULL,
    `latest_authenticated_at` INT,
    `enabled` TINYINT(1) DEFAULT 1 NOT NULL,
    `created_at` INT NOT NULL,
    `updated_at` INT NOT NULL
);

UPDATE sqlite_sequence SET seq = 1000 WHERE name = 'user';

INSERT INTO `user` VALUES
(
    123,
    'test.user',
    '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', -- "password"
    'test.user@test.test',
    strftime('%s', 'now') - 3600,
    1,
    strftime('%s', 'now'),
    strftime('%s', 'now')
),
(
    124,
    'test.user2',
    '$2y$10$PtIeyOB1.rPPVHjgTzCO5eSNPS1vdOCNp4nk1IvA2FKYu6jslFVNK', -- "password"
    'test.user2@test.test',
    strftime('%s', 'now') - 3600,
    1,
    strftime('%s', 'now'),
    strftime('%s', 'now')
);
