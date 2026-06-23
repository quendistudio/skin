<?php namespace Quendistudio\Skin\Models;

use Model;

class Settings extends Model
{
    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'quendistudio_skin_settings';

    public $settingsFields = 'fields.yaml';

    public function initSettingsData()
    {
        $this->skin = 'enhanced';
        $this->theme = 'light';
    }

    public static function allowedSkins(): array
    {
        return ['enhanced', 'modern'];
    }

    public static function allowedThemes(): array
    {
        return ['light', 'dark', 'cupcake'];
    }

    public static function resolveSkin(?string $skin): string
    {
        return in_array($skin, self::allowedSkins(), true) ? $skin : 'enhanced';
    }

    public static function resolveTheme(?string $theme): string
    {
        return in_array($theme, self::allowedThemes(), true) ? $theme : 'light';
    }

    public static function skinClassName(?string $skin): string
    {
        return 'Quendistudio\Skin\Skins\\' . ucfirst(self::resolveSkin($skin));
    }

    public function beforeValidate()
    {
        $this->skin = self::resolveSkin($this->skin ?? null);
        $this->theme = self::resolveTheme($this->theme ?? null);
    }
}
