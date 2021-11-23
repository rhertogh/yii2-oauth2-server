<?php

namespace rhertogh\Yii2Oauth2Server\traits\models;

use rhertogh\Yii2Oauth2Server\interfaces\components\openidconnect\scope\Oauth2OidcClaimInterface;
use rhertogh\Yii2Oauth2Server\interfaces\models\Oauth2OidcUserInterface;
use rhertogh\Yii2Oauth2Server\Oauth2Module;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * @var Oauth2OidcUserInterface $this
 */
trait Oauth2OidcUserIdentityTrait
{
    # region yii\base\Component functions
    /**
     * Returns a value indicating whether a property can be read.
     *
     * A property can be read if:
     *
     * - the class has a getter method associated with the specified name
     *   (in this case, property name is case-insensitive);
     * - the class has a member variable with the specified name (when `$checkVars` is true);
     * - an attached behavior has a readable property of the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkVars whether to treat member variables as properties
     * @param bool $checkBehaviors whether to treat behaviors' properties as properties of this component
     * @return bool whether the property can be read
     * @see canSetProperty()
     */
    abstract public function canGetProperty($name, $checkVars = true, $checkBehaviors = true);

    /**
     * Returns a value indicating whether a method is defined.
     *
     * A method is defined if:
     *
     * - the class has a method with the specified name
     * - an attached behavior has a method with the given name (when `$checkBehaviors` is true).
     *
     * @param string $name the property name
     * @param bool $checkBehaviors whether to treat behaviors' methods as methods of this component
     * @return bool whether the method is defined
     */
    abstract public function hasMethod($name, $checkBehaviors = true);
    # endregion yii\base\Component functions

    /**
     * Get the value for a claim.
     * @param Oauth2OidcClaimInterface $claim
     * @param Oauth2Module $module
     * @return mixed
     * @since 1.0.0
     */
    public function getOpenIdConnectClaimValue($claim, $module)
    {
        $determiner = $claim->getDeterminer();

        if (is_callable($determiner)) {
            return call_user_func($determiner, $this, $claim, $module);
        }

        if (is_string($determiner)) {
            if ($this->hasMethod($determiner)) {
                return call_user_func([$this, $determiner], $claim, $module);
            } elseif ($this->canGetProperty($determiner)) {
                return $this->$determiner;
            }
        }

        if (is_string($determiner) || is_array($determiner)) {
            return ArrayHelper::getValue($this, $determiner, $claim->getDefaultValue());
        }

        throw new InvalidArgumentException('Invalid determiner '
            . '"' . (is_object($determiner) ? get_class($determiner) : gettype($determiner)) . '"'
            . ' for claim "' . $claim->getIdentifier() . '".');
    }
}
