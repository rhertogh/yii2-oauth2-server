<?php

namespace Yii2Oauth2ServerTests\unit\helpers;

use rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper;
use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotAllowedException;
use rhertogh\Yii2Oauth2Server\helpers\exceptions\EnvironmentVariableNotSetException;
use Yii2Oauth2ServerTests\unit\TestCase;

/**
 * @covers \rhertogh\Yii2Oauth2Server\helpers\EnvironmentHelper
 */
class EnvironmentHelperTest extends TestCase
{
    /**
     * @param string $string
     * @param string[] $allowList
     * @param string[]|null $denyList
     * @param bool $parseNested
     * @param bool $exceptionWhenNotSet
     * @param bool $exceptionWhenNotAllowed
     * @param string $expected
     *
     * @dataProvider parseEnvVarsProvider
     */
    public function testParseEnvVars(
        string $string,
        ?array $allowList,
        ?array $denyList,
        bool $parseNested,
        bool $exceptionWhenNotSet,
        bool $exceptionWhenNotAllowed,
        string $expected
    )
    {
        if (is_a($expected, \Exception::class, true)) {
            $this->expectException($expected);
        }
        $result = EnvironmentHelper::parseEnvVars(
            $string,
            $allowList,
            $denyList,
            $parseNested,
            $exceptionWhenNotSet,
            $exceptionWhenNotAllowed,
        );

        $this->assertEquals($expected, $result);
    }

    /**
     * @return array{
     *      string,
     *      array{
     *          string: string,
     *          allowList: string[],
     *          denyList: string[]|null,
     *          parseNested: bool,
     *          exceptionWhenNotSet: bool,
     *          exceptionWhenNotAllowed: bool,
     *          expected: string,
     *      }
     * }
     * @see testParseEnvVars()
     */
    public function parseEnvVarsProvider()
    {
        putenv('test_env_var=test');
        putenv('test_env_var_other=other');
        putenv('test_env_var_nested=nested ${test_env_var} var');
        putenv('test_env_var_opening=${');
        putenv('test_env_var_closing=}');
        putenv('test_env_var_nested_not_set=nested ${test_not_set} var');

        return [
            // successful parsing
            'only' => [
                '${test_env_var}',
                ['*'],
                null,
                true,
                false,
                true,
                'test',
            ],
            'end' => [
                'end ${test_env_var}',
                ['*'],
                null,
                true,
                false,
                true,
                'end test',
            ],
            'beginning' => [
                '${test_env_var} beginning',
                ['*'],
                null,
                true,
                false,
                true,
                'test beginning',
            ],
            'beginning and end' => [
                '${test_env_var} beginning and end ${test_env_var}',
                ['*'],
                null,
                true,
                false,
                true,
                'test beginning and end test',
            ],
            'center' => [
                'a ${test_env_var} case',
                ['*'],
                null,
                true,
                false,
                true,
                'a test case',
            ],
            'multiple' => [
                '${test_env_var_other} ${test_env_var} case',
                ['*'],
                null,
                true,
                false,
                true,
                'other test case',
            ],
            'connected' => [
                'an${test_env_var_other}${test_env_var}case',
                ['*'],
                null,
                true,
                false,
                true,
                'anothertestcase',
            ],
            'nested' => [
                'a ${test_env_var_nested} case',
                ['*'],
                null,
                true,
                false,
                true,
                'a nested test var case',
            ],
            'nested multiple' => [
                'a ${test_env_var_nested} ${test_env_var_other} case',
                ['*'],
                null,
                true,
                false,
                true,
                'a nested test var other case',
            ],
            'parseNested - parseNested disabled' => [
                'a ${test_env_var_nested} case',
                ['*'],
                null,
                false,
                false,
                true,
                'a nested ${test_env_var} var case',
            ],
            'nested multiple - parseNested disabled' => [
                'a ${test_env_var_nested} ${test_env_var_other} case',
                ['*'],
                null,
                false,
                false,
                true,
                'a nested ${test_env_var} var other case',
            ],
            'nested not set - parseNested disabled' => [
                'a ${test_env_var_nested_not_set} case',
                ['*'],
                null,
                false,
                false,
                true,
                'a nested ${test_not_set} var case',
            ],
            'not set' => [
                'a ${test_not_set} case',
                ['*'],
                null,
                true,
                false,
                true,
                'a  case',
            ],
            'opening and closing' => [
                'a ${test_env_var_opening}test_env_var${test_env_var_closing} case',
                ['*'],
                null,
                true,
                false,
                true,
                'a ${test_env_var} case',
            ],

            // Invalid arguments
            'empty allow lists' => [
                '',
                [],
                null,
                true,
                true,
                true,
                \InvalidArgumentException::class,
            ],

            // Not set exceptions
            'not set exception' => [
                '${test_not_set}',
                ['*'],
                null,
                true,
                true,
                true,
                EnvironmentVariableNotSetException::class,
            ],
            'nested not set' => [
                '${test_env_var_nested_not_set}',
                ['*'],
                null,
                true,
                true,
                true,
                EnvironmentVariableNotSetException::class,
            ],

            // Allowed
            'allowList exact match' => [
                '${test_env_var}',
                ['test_mismatch', 'test_env_var', 'test_another_mismatch'],
                null,
                true,
                true,
                true,
                'test',
            ],
            'allowList wildcard match' => [
                '${test_env_var}',
                ['test_mismatch', 'test_*_var', 'test_another_mismatch'],
                null,
                true,
                true,
                true,
                'test',
            ],
            'allowList regex match' => [
                '${test_env_var}',
                ['test_mismatch', '/test_[env]+_var/', 'test_another_mismatch'],
                null,
                true,
                true,
                true,
                'test',
            ],
            'denyList no match' => [
                '${test_env_var}',
                ['*'],
                ['test_mismatch', 'wildcard_mismatch*', '/regex_mismatch/'],
                true,
                true,
                true,
                'test',
            ],
            'whiteList match denyList no match' => [
                '${test_env_var}',
                ['test_env_var'],
                ['test_mismatch', 'wildcard_mismatch*', '/regex_mismatch/'],
                true,
                true,
                true,
                'test',
            ],

            // Not allowed
            'not allowed allowList - exception' => [
                '${test_env_var}',
                ['test_mismatch', 'wildcard_mismatch*', '/regex_mismatch/'],
                null,
                true,
                true,
                true,
                EnvironmentVariableNotAllowedException::class,
            ],

            'not allowed allowList empty' => [
                'a ${test_env_var} var',
                ['test_mismatch', 'wildcard_mismatch*', '/regex_mismatch/'],
                null,
                true,
                true,
                false,
                'a  var',
            ],
            'not allowed denyList exact match - exception' => [
                '${test_env_var}',
                ['*'],
                ['test_mismatch', 'test_env_var', 'test_another_mismatch'],
                true,
                true,
                true,
                EnvironmentVariableNotAllowedException::class,
            ],
            'not allowed denyList wildcard match - exception' => [
                '${test_env_var}',
                ['*'],
                ['test_mismatch', 'test_*_var', 'test_another_mismatch'],
                true,
                true,
                true,
                EnvironmentVariableNotAllowedException::class,
            ],
            'not allowed denyList regex match - exception' => [
                '${test_env_var}',
                ['*'],
                ['test_mismatch', '/test_[env]+_var/', 'test_another_mismatch'],
                true,
                true,
                true,
                EnvironmentVariableNotAllowedException::class,
            ],
            'allowed on allowList denied on denyList - exception' => [
                '${test_env_var}',
                ['test_env_var'],
                ['/test_[env]+_var/'],
                true,
                true,
                true,
                EnvironmentVariableNotAllowedException::class,
            ],

            'not allowed denyList exact match - no exception' => [
                'a ${test_env_var} case',
                ['*'],
                ['test_mismatch', 'test_env_var', 'test_another_mismatch'],
                true,
                true,
                false,
                'a  case',
            ],
            'not allowed denyList wildcard match - no exception' => [
                'a ${test_env_var} case',
                ['*'],
                ['test_mismatch', 'test_*_var', 'test_another_mismatch'],
                true,
                true,
                false,
                'a  case',
            ],
            'not allowed denyList regex match - no exception' => [
                'a ${test_env_var} case',
                ['*'],
                ['test_mismatch', '/test_[env]+_var/', 'test_another_mismatch'],
                true,
                true,
                false,
                'a  case',
            ],
            'allowed on allowList denied on denyList - no exception' => [
                'a ${test_env_var} case',
                ['test_env_var'],
                ['/test_[env]+_var/'],
                true,
                true,
                false,
                'a  case',
            ],
        ];
    }
}
