<?php

namespace rhertogh\Yii2Oauth2Server\controllers\console\base\traits;

use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2ClientInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\console\widgets\Table;

trait GenerateClientsTableTrait
{
    /**
     * @param Oauth2ClientInterface[] $clients
     * @return string
     * @throws \Throwable
     */
    public function generateClientsTable($clients)
    {
        $rows = [];
        foreach ($clients as $client) {
            $redirectUris = $client->getRedirectUri();
            $rows[] = [
                'id' => $client->getPrimaryKey(),
                'identifier' => $client->getIdentifier(),
                'type' => $client->isConfidential() ? 'Confidential' : 'Public',
                'redirect_uris' => $redirectUris
                    ? (
                        $redirectUris[0]
                        . (count($redirectUris) > 1
                            ? ' +' . (count($redirectUris) - 1) . ' more'
                            : '')
                    )
                    : '',
                'grant_types' => implode(', ', Oauth2Module::getGrantTypeIdentifiers($client->getGrantTypes())),
            ];
        }

        return Table::widget([
            'headers' => [
                'ID',
                'Identifier',
                'Type',
                'Redirect URIs',
                'Grant Types',
            ],
            'rows' => $rows,
        ]);
    }
}
