<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\client;

use rhertogh\Yii2Oauth2Server\controllers\console\Oauth2ClientController;
use rhertogh\Yii2Oauth2Server\interfaces\controllers\console\client\Oauth2SetClientSecretActionInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use Yii;
use yii\base\Action;
use yii\base\InvalidArgumentException;
use yii\base\InvalidCallException;
use yii\base\InvalidConfigException;
use yii\console\ExitCode;
use yii\helpers\Console;
use yii\helpers\StringHelper;

/**
 * @property Oauth2ClientController $controller
 */
class Oauth2SetClientSecretAction extends Action implements Oauth2SetClientSecretActionInterface
{
    /**
     * Update the "client secret" of an Oauth2 Client.
     *
     * @throws InvalidConfigException
     */
    public function run()
    {
        $module = $this->controller->module;

        $identifier = $this->controller->identifier;
        $secret = $this->controller->secret;
        $oldSecretValidUntilInput = $this->controller->oldSecretValidUntil;

        if (empty($identifier)) {
            throw new InvalidCallException('The `identifier` option must be specified.');
        }

        /** @var Oauth2ClientInterface $client */
        $client = $module->getClientRepository()->findModelByIdentifier($identifier);
        if (!$client) {
            throw new InvalidCallException('No client with identifier "' . $identifier . '" found.');
        }

        if (empty($secret)) {
            $secret = $this->controller->prompt('Client Secret?', [
                'required' => true,
                'validator' => [$client, 'validateNewSecret'],
            ]);
        }

        $oldSecretValidUntil = null;
        if ($oldSecretValidUntilInput) {
            if (StringHelper::startsWith($oldSecretValidUntilInput, 'P')) {
                try {
                    $oldSecretValidUntil = new \DateInterval($oldSecretValidUntilInput);
                } catch (\Exception $e) {
                    $oldSecretValidUntil = false;
                }
            } else {
                try {
                    $oldSecretValidUntil = new \DateTimeImmutable($oldSecretValidUntilInput);
                } catch (\Exception $e) {
                    $oldSecretValidUntil = false;
                }
            }
            if (empty($oldSecretValidUntil)) {
                throw new InvalidArgumentException('Unable to parse "' . $oldSecretValidUntilInput
                    . '" as a DateTime or DateInterval.');
            }
        }

        $client->setSecret($secret, $module->getCryptographer(), $oldSecretValidUntil);
        $client->persist();

        if ($oldSecretValidUntil) {
            $oldSecretValidUntilInfo = ' The previous secret will be valid until '
                . Yii::$app->formatter->asDatetime($client->getOldSecretValidUntil(), 'long');
        } else {
            $oldSecretValidUntilInfo = ' Any previous secret is cleared.';
        }
        $this->controller->stdout('Successfully updated client secret.' . $oldSecretValidUntilInfo
            . PHP_EOL, Console::FG_GREEN);

        return ExitCode::OK;
    }
}
