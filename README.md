<p align="center">
    <a href="https://github.com/yiisoft" target="_blank">
        <img src="https://avatars0.githubusercontent.com/u/993323" height="100px" alt="Yii2">
    </a>
    &nbsp;
    <a href="https://oauth.net/2/" target="_blank">
        <img src="https://oauth.net/images/oauth-2-sm.png" height="90px" alt="Oauth 2">
    </a>
    &nbsp;&nbsp;&nbsp;
    <a href="https://openid.net/" target="_blank">
        <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/a/a2/OpenID_logo_2.svg/640px-OpenID_logo_2.svg.png" height="90px" alt="OpenID Connect">
    </a>
    <h1 align="center">Oauth2 + OpenID Connect Extension for Yii 2</h1>
    <br>
</p>

[![Latest Stable Version](https://img.shields.io/packagist/v/rhertogh/yii2-oauth2-server.svg)](https://packagist.org/packages/rhertogh/yii2-oauth2-server)
[![build Status](https://github.com/rhertogh/yii2-oauth2-server/actions/workflows/build.yml/badge.svg)](https://github.com/rhertogh/yii2-oauth2-server/actions/workflows/build.yml)
[![Code Coverage](https://scrutinizer-ci.com/g/rhertogh/yii2-oauth2-server/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/rhertogh/yii2-oauth2-server/?branch=master)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/rhertogh/yii2-oauth2-server/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/rhertogh/yii2-oauth2-server/?branch=master)
[![GitHub](https://img.shields.io/github/license/rhertogh/yii2-oauth2-server?color=brightgreen)](https://github.com/rhertogh/yii2-oauth2-server/blob/master/LICENSE.md)

The Yii2-Oauth2-Server is an extension for [Yii framework 2.0](http://www.yiiframework.com) applications and provides 
an [Oauth2](https://oauth.net/2/) server based on the [League OAuth2 server](https://github.com/thephpleague/oauth2-server).
The server also supports [OpenID Connect Core](https://openid.net/specs/openid-connect-core-1_0.html).


### 📑 Contents

- [Getting started](#-getting-started)
- [Test Drive](#%EF%B8%8F-test-drive)
- [Documentation](#-documentation)
- [FAQ](#-faq)
- [Implemented Standards](#-implemented-standards)
- [Importing/Migrating](#-importingmigrating)
- [Versioning & Change Log](#-versioning--change-log)
- [Reporting Security issues](#-reporting-security-issues)
- [Directory Structure](#-directory-structure)
- [Contributing](#-contributing)
- [Credits](#-credits)
- [License](#-license)

---

🐣 Getting started
------------------
If you're not yet familiar with Oauth 2 we recommend to check out
[An Illustrated Guide to OAuth and OpenID Connect](https://developer.okta.com/blog/2019/10/21/illustrated-guide-to-oauth-and-oidc)

#### Requirements:
* The minimum required PHP version is 7.4 (compatibility tested up till and including PHP 8.1).
* The minimum required Yii version is 2.0.49 (2.0.50 when using SQLite).

#### Installation:
The preferred way to install this extension is through [composer](https://getcomposer.org/download/).
```bash
composer require rhertogh/yii2-oauth2-server
```

Full installation and configuration details can be found in the docs under
[Installing the Yii2-Oauth2-Server](docs/guide/start-installation.md)


🏎️ Test Drive
----------
You can run a local preview instance using [Docker](https://docker.com/):
```bash
docker run --rm -p 82:80 --name Yii2Oauth2Server ghcr.io/rhertogh/yii2-oauth2-server:master
```
After the container is started you can access the Oauth2 server on [localhost:82](http://localhost:82).
> Hint: The port number on the host machine is specified by the first part of the `-p` argument.
> This can be changed if desired (e.g. `-p 88:80`).

To access the CLI of the Docker container you can run:
```bash
docker container exec -it Yii2Oauth2Server bash
```


📖 Documentation
----------------
There are two main sections in the documentation:
* [Usage Guide](docs/guide/README.md) for using the Yii2-Oauth2-Server in your own project.
* [Development Guide](docs/internals/README.md) for contributing to the Yii2-Oauth2-Server.


🔮 FAQ
------
This is a quick FAQ, the full version can be found [here](docs/guide/faq.md).

#### *Where can I find the Oauth2 endpoints?*  
To see an overview of the endpoints and other configuration you can run: `./yii oauth2/debug/config`

#### *How can I see the configured clients?*  
To see an overview of the clients you can run: `./yii oauth2/client/list`

#### *The Oauth2 server throws an error, what should I do?*  
Please check out the [full FAQ](docs/guide/faq.md#error-messages) first.
If that doesn't solve the problem, please [report an issue](docs/internals/report-an-issue.md).


📒 Implemented Standards
-----------------------

| Name                                                       | RFC / Specs                                                                      | Since  |
|------------------------------------------------------------|----------------------------------------------------------------------------------|--------|
| OAuth 2.0                                                  | [RFC 6749](https://datatracker.ietf.org/doc/html/rfc6749)                        | 1.0.0¹ |
| The OAuth 2.0 Authorization Framework: Bearer Token Usage  | [RFC 6750](https://datatracker.ietf.org/doc/html/rfc6750)                        | 1.0.0¹ |
| Proof Key for Code Exchange by OAuth Public Clients (PKCE) | [RFC 7636](https://datatracker.ietf.org/doc/html/rfc7636)                        | 1.0.0¹ |
| OAuth 2.0 Token Revocation                                 | [RFC 7009](https://datatracker.ietf.org/doc/html/rfc7009)                        | 1.0.0  |
| OpenID Connect Core 1.0                                    | [Specifications](https://openid.net/specs/openid-connect-core-1_0.html)          | 1.0.0  |
| OpenID Connect Discovery 1.0                               | [Specifications](https://openid.net/specs/openid-connect-discovery-1_0.html)     | 1.0.0  |
| OpenID Connect RP-Initiated Logout                         | [Specifications](https://openid.net/specs/openid-connect-rpinitiated-1_0.html)   | 1.0.0  |
| Initiating User Registration via OpenID Connect            | [Specifications](https://openid.net/specs/openid-connect-prompt-create-1_0.html) | 1.0.0  |

¹ Provided via [PHP OAuth 2.0 Server](https://oauth2.thephpleague.com/).


↘️ Importing/Migrating
----------------------
To ease migrating from another project, the Yii2-Oauth2-Server supports importing data from other projects.
For example from the [filsh/yii2-oauth2-server](https://github.com/filsh/yii2-oauth2-server)

Please see [Importing/Migrating from other servers](docs/guide/importing-migrating.md) for more information.


📜 Versioning & Change Log
--------------------------
The Yii2-Oauth2-Server follows [Semantic Versioning 2.0](https://semver.org/spec/v2.0.0.html)  
Please see the [Change Log](CHANGELOG.md) for more information on version history
and the [Upgrading Instructions](UPGRADE.md) when upgrading to a newer version.


🔎 Reporting Security issues
----------------------------
In case you found a security issue please [contact us directly](
https://forms.gle/8aEGxmN51Hvb7oLJ7)
DO NOT use the issue tracker or discuss it in public as it will cause more damage than help.

Please note that as a non-commercial OpenSource project we are not able to pay bounties.


📂 Directory Structure
----------------------
```
docker/     Docker container definition
docs/       Documentation (for both usage and development)
sample/     Sample app for the server
src/        Yii2-Oauth2-Server source
tests/      Codeception unit and functional tests
```


🚀 Contributing
---------------
The Yii2-Oauth2-Server is [Open Source](LICENSE.md). You can help by:

- [Report an issue](docs/internals/report-an-issue.md)
- [Contribute with new features or bug fixes](docs/internals/pull-request-qa.md)

Thanks in advance for your contribution!


🎉 Credits
----------
- [Rutger Hertogh](https://github.com/rhertogh)
- [All Contributors](https://github.com/rhertogh/yii2-oauth2-server/graphs/contributors)


✒️ License
----------
The Yii2-Oauth2-Server is free software. It is released under the terms of the Apache License.
Please see [`LICENSE.md`](LICENSE.md) for more information.
