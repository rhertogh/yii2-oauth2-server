Client Secret Rotation
======================

Similar to changing passwords, regularly rotating the secrets that your client applications use is a security best 
practice. When rotating the client secret a seamless transition to the new secret is crucial to avoid downtime.

In order to support this the Yii2Oauth2Server supports two secrets per client:
1. The primary secret
2. An "old" secret which has an expiry date

When setting a new secret for a client an expiry date for the previous secret may be specified. If this is the case
the previously primary secret will be stored as the "old" secret with the specified expiry date. This functions 
as a grace period during which the client can use both the new and old secret to authenticate. In this way a client can 
be seamlessly transitioned to a new secret.
After the expiry date has passed, only the new secret can be used.

> Warning: For security, if no expiry date is specified when updating the client secret the old secret will be cleared
  (regardless if it was expired or not).
  Any authentication attempt by a client relying on that "old" secret will fail.

> Warning: If there was still an "old" secret active when a new secret is set and an expiry date is specified
  it will overwrite the previous "old" secret.
  Any authentication attempt by a client relying on that previous "old" secret will fail.

Updating a client secret
------------------------

A client secret can be rotated in the following ways:

* Manually via the console via the `yii oauth2/client/set-secret` command.  
  E.g. Set the secret for "my-client" to "my-new-secret" and expire the previous secret after one month:  
  `yii oauth2/client/set-secret --identifier=my-client --secret=my-new-secret --old-secret-valid-until=P1M`
  
* Programmatically by calling `\rhertogh\Yii2Oauth2Server\models\Oauth2Client::setSecret()`
  and specifying the `$oldSecretValidUntil` parameter.
