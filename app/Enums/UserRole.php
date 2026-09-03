<?php

namespace App\Enums;

enum UserRole: string
{
    case SuperAdmin = 'super_admin';
    case ViewOnly = 'view_only';
    case BankDataAdmin = 'bank_data_admin';
    case ContentCreator = 'content_creator';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::ViewOnly => 'All View-Only',
            self::BankDataAdmin => 'Bank Data Admin',
            self::ContentCreator => 'Content Creator',
        };
    }

    /**
     * Roles allowed to access the Dashboard and Master Data menus.
     *
     * @return array<int, self>
     */
    public static function masterDataRoles(): array
    {
        return [self::SuperAdmin, self::ViewOnly, self::BankDataAdmin];
    }

    /**
     * Roles allowed to access the Pages (Content Management) menu.
     *
     * @return array<int, self>
     */
    public static function pagesRoles(): array
    {
        return [self::SuperAdmin, self::ViewOnly, self::ContentCreator];
    }
}
