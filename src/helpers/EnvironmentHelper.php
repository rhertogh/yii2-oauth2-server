<?php

namespace rhertogh\Yii2Oauth2Server\helpers;

use InvalidArgumentException;
use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotAllowedException;
use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotSetException;

class EnvironmentHelper
{
    /**
     * Replace environment variables in a string with their respective value.
     * The format for env vars is '${ENV_VAR_NAME}', e.g.: 'Hello ${NAME}' will return 'Hello world' if the `NAME`
     * environment variable is set to 'world'. Nesting is, when enabled, also possible, e.g.:
     * Let's assume whe have the following environment variables set `NAMES=${NAME1} and ${NAME2},NAME1=Alice,NAME2=Bob`
     * the string 'Hello ${NAMES}' would return 'Hello Alice and Bob'.
     *
     * For security at least the $allowList must be set.
     * When the $allowList is set the variable(s) must match at least 1 pattern in the list to be allowed
     * (others will be denied).
     * When both the $allowList and the $denyList are set the variable(s) must match at least 1 pattern in the
     * allowList and not match any pattern in the denyList in order to be allowed.
     *
     * Both the $allowList and $denyList can take 3 different types of patterns:
     * 1. Exact match, e.g.: 'MY_ENV_VAR'.
     * 2. Wildcard where `*` would match zero or more characters, e.g.: 'MY_ENV_*'.
     * 3. A regular expression, e.g.: '/^MY_[ABC]{1,3}_VAR$/'.
     *
     * By default an `EnvironmentVariableNotSetException` is thrown when a specified environment variable is not set.
     * Similarly, an `EnvironmentVariableNotAllowedException` is thrown when access to a specified environment variable
     * is not allowed by the $allowList and/or $denyList.
     * This behavior can be disabled by setting the $exceptionWhenNotSet or $exceptionWhenNotAllowed respectively
     * to `false`. In that case, instead of an exception being thrown, the specified environment variable will be
     * replaced with an empty string.
     *
     * @param string $string The input string containing the environment variable(s).
     * @param string[] $allowList List of patterns of which at least 1 has to match to allow replacement.
     * @param string[]|null $denyList List of patterns of which any match will deny replacement.
     * @param bool $parseNested Should nested (a.k.a. recursive) environment variables be parsed.
     * @param bool $exceptionWhenNotSet Throw an exception when an environment variable is not set (default behavior),
     * silently use an empty string as the value otherwise.
     * @param bool $exceptionWhenNotAllowed Throw an exception when the usage of an environment variable is not allowed
     * (default behavior), silently use an empty string as the value otherwise.
     * @return string The input string where the environment variables are replaced with their respective value.
     * @throws InvalidArgumentException When a parameter has an invalid value.
     * @throws EnvironmentVariableNotSetException When the $string references an environment variable that is not set.
     * @throws EnvironmentVariableNotAllowedException When the $string references an environment variable which usage
     * is not allowed by the $allowList or $denyList.
     */
    public static function parseEnvVars(
        string $string,
        array $allowList,
        ?array $denyList = null,
        bool $parseNested = false,
        bool $exceptionWhenNotSet = true,
        bool $exceptionWhenNotAllowed = true
    ): string
    {
        if (!$allowList) {
            throw new InvalidArgumentException('$allowList cannot be empty.');
        }

        return preg_replace_callback(
            '/\${(?<name>[a-zA-Z0-9_]+)}/',
            function(array $matches) use (
                $allowList,
                $denyList,
                $parseNested,
                $exceptionWhenNotSet,
                $exceptionWhenNotAllowed
            ) {
                $envVarName = $matches['name']; /** @var string $envVarName */
                if (!static::matchList($allowList, $envVarName)) {
                    return static::handleEnvVarNotAllowed($envVarName, $exceptionWhenNotAllowed);
                }
                if (!empty($denyList) && static::matchList($denyList, $envVarName)) {
                    return static::handleEnvVarNotAllowed($envVarName, $exceptionWhenNotAllowed);
                }

                $value = getenv($envVarName); /** @var string|false $value */
                if ($value === false) {
                    if ($exceptionWhenNotSet) {
                        throw new EnvironmentVariableNotSetException($envVarName);
                    }
                    $value = '';
                } elseif ($parseNested && (mb_strlen($value) > 3)) {
                    $value = static::parseEnvVars(
                        $value,
                        $allowList,
                        $denyList,
                        $parseNested,
                        $exceptionWhenNotSet,
                        $exceptionWhenNotAllowed,
                    );
                }
                return $value;
            },
            $string,
        );
    }

    protected static function matchList($list, $item)
    {
        foreach ($list as $pattern) {
            if ($pattern === '*' || $item === $pattern) {
                return true;
            }
            $pregMatch = @preg_match($pattern, $item);
            if ($pregMatch === 1) { // Regex match.
                return true;
            } elseif ($pregMatch === false) { // Not a Regex.
                if (mb_strpos($pattern, '*') !== false) {
                    $regex = '/^' . str_replace('*', '[a-zA-Z0-9_]*', $pattern) . '$/';
                    if (preg_match($regex, $item)) {
                        return true;
                    }
                }
            }
        }

        return false;
    }

    /**
     * @param string $envVarName
     * @param bool $exceptionWhenNotAllowed
     * @return string
     * @throws EnvironmentVariableNotAllowedException
     */
    protected static function handleEnvVarNotAllowed(string $envVarName, bool $exceptionWhenNotAllowed): string
    {
        if ($exceptionWhenNotAllowed) {
            throw new EnvironmentVariableNotAllowedException($envVarName);
        } else {
            return '';
        }
    }
}
