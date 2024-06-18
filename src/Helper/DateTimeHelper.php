<?php

/*
 *     This file is part of Loteria.
 *
 *     (c) Leonardo Rodrigues Marques <leonardo@rodriguesmarques.com.br>
 *
 *     This source file is subject to the MIT license that is bundled
 *     with this source code in the file LICENSE.
 */

namespace App\Helper;

class DateTimeHelper
{
    public static function currentDateTime(?string $dateTimeZoneName = null): \DateTimeInterface
    {
        $dateTime = new \DateTime();

        if (null != $dateTimeZoneName) {
            $dateTime->setTimezone(new \DateTimeZone($dateTimeZoneName));
        }

        return $dateTime;
    }

    public static function currentDateTimeImmutable(?string $dateTimeZoneName = null): \DateTimeImmutable
    {
        return \DateTimeImmutable::createFromInterface(self::currentDateTime($dateTimeZoneName));
    }
}
