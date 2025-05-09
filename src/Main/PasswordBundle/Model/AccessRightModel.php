<?php

namespace Main\PasswordBundle\Model;

final class AccessRightModel
{
    const RIGHT_ADMIN = 3;
    const RIGHT_MODERATOR = 2;
    const RIGHT_READ_ONLY = 1;

    /**
     * @param $right
     *
     * @return bool
     */
    public static function isRight($right)
    {
        if (!is_int($right)) {
            return false;
        }

        if ($right == self::RIGHT_ADMIN || $right == self::RIGHT_MODERATOR || $right == self::RIGHT_READ_ONLY) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public static function getRights()
    {
        return [
            'Read Only' => self::RIGHT_READ_ONLY,
            'Moderator' => self::RIGHT_MODERATOR,
            'Administrator' => self::RIGHT_ADMIN,
        ];
    }
}
