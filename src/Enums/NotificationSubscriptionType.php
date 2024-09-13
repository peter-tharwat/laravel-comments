<?php

namespace Spatie\Comments\Enums;

enum NotificationSubscriptionType: string
{
    case Participating = 'participating';
    case All = 'all';
    case None = 'none';

    public function description(): string
    {
        return match ($this) {
            self::Participating => __('comments::notifications.enum_description_participating'),
            self::All => __('comments::notifications.enum_description_all'),
            self::None => __('comments::notifications.enum_description_none'),
        };
    }

    public function longDescription(): string
    {
        return match ($this) {
            self::Participating => __('comments::notifications.enum_longdescription_participating'),
            self::All => __('comments::notifications.enum_longdescription_all'),
            self::None => __('comments::notifications.enum_longdescription_none'),
        };
    }
}
