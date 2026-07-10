Identity, RBAC Permissions and Scopes
=====================================

This chapter describes how the Yii2-Oauth2-Server integrates with the 
[Yii2 user component](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication), 
[RBAC permissions](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization#rbac) 
and the usage of OAuth 2.0 scopes.

OAuth 2.0 flows (3-legged vs 2-legged)
--------------------------------------
The OAuth 2.0 specification defines different authorization flows, called "grant types", which sometimes are grouped in
two types: "3-legged" and "vs "2-legged". The "legs" refer to the number of parties involved.

A typical OAuth2 flow involves three parties:
- the Resource Owner (often the end-user).
- the Client (often a web application or third-party application).
- and the Authorization and/or Resource server(s) (often the same server but can be different servers).
  These roles are handled by the Yii2-Oauth2-Server  

The grant types that follow this 3-legged flow are:
- Authorization Code Grant (recommended)
- Implicit Grant (legacy type, usage is discouraged)
- Password Credentials Grant (legacy type, usage is discouraged)

OAuth 2.0 can also be used without an end-user being involved, typically server-to-server, where the client is acting on
its own behalf (in this case the client is also the resource owner).

Currently, the Oauth 2.0 specification only defines one grant type for the 2-legged flow:
- Client Credentials Grant

### 3-legged OAuth 2.0 flow

The recommended grant type for 3-legged authentication is the Authorization Code Grant with PKCE enabled.

Authorization Code Flow:
```
    +----------+
    | Resource |
    |   Owner  |
    |          |
    +----------+
         ^
         |
        (2)
    +----|-----+          Client Identifier      +---------------+
    |         -+----(1)-- & Redirection URI ---->|               |
    |  User-   |                                 | Authorization |
    |  Agent  -+----(2)-- User authenticates --->|     Server    |
    |          |                                 | (Yii2-Oauth2- |
    |         -+----(3)-- Authorization Code ---<|    Server)    |
    +-|----|---+                                 +---------------+
      |    |                                         ^      v
     (1)  (3)                                        |      |
      |    |                                         |      |
      ^    v                                         |      |
    +---------+                                      |      |
    |         |>---(4)-- Authorization Code ---------'      |
    |  Client |          & Redirection URI                  |
    |         |                                             |
    |         |<---(5)----- Access Token -------------------'
    +---------+       (w/ Optional Refresh Token)

    Note: The lines illustrating steps (1), (2), and (3) are broken into two parts as they pass through the user-agent
    (often the web browser).
```
1. The Client initiates the flow by directing the Resource Owner's user-agent to the authorization endpoint 
   (in this case the Yii2-Oauth2-Server). 
   The Client includes its client identifier, requested scope, local state, and a redirection URI to which the 
   Authorization Server (Yii2-Oauth2-Server) will send the user-agent back once access is granted (or denied).

2. The Authorization Server (Yii2-Oauth2-Server) authenticates the Resource Owner (end user) via the user-agent
   and establishes whether the Resource Owner grants or denies the Client's access request.

3. Assuming the Resource Owner grants access, the Authorization Server (Yii2-Oauth2-Server) redirects the user-agent 
   back to the Client using the redirection URI provided earlier (in the request or during client registration).
   The redirection URI includes an authorization code and any local state provided by the Client earlier.

4. The Client requests an access token from the Authorization Server's (Yii2-Oauth2-Server) token endpoint by
   including the authorization code received in the previous step.  When making the request, the Client authenticates
   with the Authorization Server (Yii2-Oauth2-Server). The Client includes the redirection URI used to obtain the
   authorization code for verification.

5. The Authorization Server (Yii2-Oauth2-Server) authenticates the Client, validates the authorization code, and ensures
   that the redirection URI received matches the URI used to redirect the Client in step (3).  
   If valid, the Authorization Server (Yii2-Oauth2-Server) responds back with an access token and, optionally, a 
   refresh token.

### 2-legged OAuth 2.0 flow

The Client credentials Grant is used typically when the client is acting on its own behalf (server-to-server).

Client credentials flow:
```
     +---------+                                  +---------------+
     |         |                                  |               |
     |         |>--(1)- Client Authentication --->| Authorization |
     | Client  |                                  |     Server    |
     |         |<--(2)---- Access Token ---------<| (Yii2-Oauth2- |
     |         |                                  |    Server)    |
     +---------+                                  +---------------+
```

1. The Client authenticates (via its 'identifier' and 'secret') with the Authorization Server (Yii2-Oauth2-Server) and
   requests an access token from the token endpoint.

2. The Authorization Server (Yii2-Oauth2-Server) authenticates the Client, and if valid, issues an access token.

Since the client authentication is used as the authorization grant, no additional authorization request is needed.


Yii2-Oauth2-Server Integration
------------------------------

This section requires knowledge about
[Authentication](https://www.yiiframework.com/doc/guide/2.0/en/security-authentication)
and [Authorization](https://www.yiiframework.com/doc/guide/2.0/en/security-authorization)
within the Yii framework. Please read these Yii2 Guide chapters first if you're not fully familiar with these concepts.

The Yii2-Oauth2-Server integrates with the Yii2 application by taking care of the entire Oauth 2.0 flow.
After successful authorization, when the Client makes server requests with a valid access token in the
["Bearer Token" authorization header](https://datatracker.ietf.org/doc/html/rfc6750)
the Yii2-Oauth2-Server will set the Yii2 "user identity class" (`Yii::$app->user->identity`) for the "user component"
(`Yii::$app->user`).

### Identity and RBAC usage

Let's say that in our example there is a backend application (from now on abbreviated as BEA) which has 2 roles:
"user" and "admin", and has two users: Alice is "admin" and user Bob is a regular "user".

When a User authorizes a Client, let's say a frontend application(from now on abbreviated as FEA), to use the BEA,
all calls that are made by the FEA will be automatically done with that user's identity
(assuming the FEA correctly sends the "authorization" header).

If Alice authorizes the FEA via the Authorization Code Grand the BEA will issue an access token for Alice which the FEA 
can use in the authorization header to make API calls against the BEA.
For all these calls the `Yii::$app->user->identity` will be set to the user identity of Alice. When Bob would authorize 
the FEA all calls by the FEA with his bearer token will set the `Yii::$app->user->identity` to Bob's identity.

Since the identity is set, you can check if the user is allowed to perform actions or access certain 
resources like you do in any Yii application, e.g. `Yii::$app->user->can('role_you_want_to_check')`.  
In our example `Yii::$app->user->can('admin')` returns `true` for Alice and `false` for Bob, since Alice has the "admin"
role while Bob have just has the "user" role.

> Note: In our sample app we had no need for scopes so far since our FEA is trusted to do all Bob and Alice are allowed 
  to do respectively. Now assume Bob and Alice can use another Client.
  For example a third-party mobile app which requires a different level of trust for the *same* user.  
  Let us say FEA is trusted in such a way it can be allowed to do things that a third-party app is not allowed to do. 
  We need to scope these different Clients so that each will be limited to what it is entrusted to do.
  That is where scopes comes into play!

### Scope usage

> Scopes only come into play in delegation scenarios, and always *limit* what a Client can do on *behalf* of a User:
  a scope cannot allow an application to do more than what the user can do.  
  If your Client(s) are allowed to do everything the User can do you probably don't need Scopes.

Let's say (continuing from our sample above) that Bob (with the "user" role) wants to authorize two different clients:
- Your own front-end application (FEA) to read, create and delete emails on his behalf.
- A third-party app (from now on abbreviated as 3rdPA) to only create emails on his behalf.

In that case you would need to define the scopes 'read_email', 'create_email' and 'delete_email' and assign them to the
different Clients. Then Bob can specify which scopes the Client may use on his behalf during the authorization of the 
Client.

Note that another user, let's say Charles (who also has the role "user"), can allow different scopes for the same
clients, e.g.:
- FEA1 may read and create emails on his behalf.
- 3rdPA may create and also delete emails on his behalf.

When scopes are used you can use `Yii::$app->getModule('oauth2')->requestHasScope('scope_you_want_to_check')` to see if
the client that makes the request has the required scope.
Think of it as access control, but now it applies to the Client not the User account!

So in the case of Bob and Charles:
- `Yii::$app->user->can('admin')` would be `false` for both.
- `Yii::$app->user->can('user')` would be `true` for both.
- And when checking the scopes (`Yii::$app->getModule('oauth2')->requestHasScope('scope_you_want_to_check')`)
  would yield the following (B = Bob, C = Charles):
  |        |     'read_email'     |   'create_email'   |   'delete_email'    |
  |--------|:--------------------:|:------------------:|:-------------------:|
  | FEA1   |  B:`true`, C:`true`  | B:`true`, C:`true` | B:`true`, C:`false` |
  | 3rdPA  | B:`false`, C:`false` | B:`true`, C:`true` | B:`false`, C:`true` |

To summarize:
- Roles define what the User may do.
- Scopes *limit* what a Client may do on *behalf* of the User.
- If you don't need to limit what a Client may do on behalf of a User, you probably don't need scopes.
- If you do need scopes, you most likely need to check both the user Role and Scope. E.g.
   ```php
   class EmailController extends Controller
   {
       public function actionCreate()
       {
           if (
               !Yii::$app->user->can('user')
               || !Yii::$app->getModule('oauth2')->requestHasScope('create_email')
           ) {
               throw new UnauthorizedHttpException();
           }
           // ...
   ```

### Identity for 2-legged flow

When using 2-legged flow (Client Credentials Grant) for server-to-server communication there is no end-user involved.
In order to leverage the Yii2 identity and RBAC system you can assign a User identity to the Client.  
This can be done via the `Oauth2ClientInterface::setClientCredentialsGrantUserId()` or in the database in the
`public.oauth2_client`.`client_credentials_grant_user_id` field.

It's recommended to create a dedicated User for the client and assign the required role(s) and/or permission(s)
to that User.

When configured, requests from Client using the Client Credentials Grant will have the User identity set to the
specified user. This way you can easily use the Yii2 RBAC system
(`Yii::$app->user->can('other_server_role_or_permission')`).    
