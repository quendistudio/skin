# Quendistudio.Skin

Enhances the Winter CMS admin interface with dropdown menus, breadcrumbs that handle subpages, previous and next buttons on forms, and optional shortcut buttons on the right side of the breadcrumb.

> French documentation: [README.fr.md](README.fr.md)

Quendistudio plugins work **without** Skin (secondary controllers stay in the side menu). Skin is **recommended** for the streamlined navigation experience.

## Installation

### Composer (recommended)

```bash
composer require quendistudio/skin
php artisan winter:up
```

The skin is applied automatically to the backend when the plugin is enabled.

Backend → **Settings** → **Skin** (QuendiStudio Plugins category). Grant the `quendistudio.skin.administrate` permission to the relevant administrators.

### Manual

Clone into `plugins/quendistudio/skin`, then:

```bash
php artisan winter:up
```

## Breadcrumb buttons (`_breadcrumb_buttons.htm`)

On **list** controllers (`index` action), Skin renders a partial named `breadcrumb_buttons` on the right side of the breadcrumb. This keeps rarely used subpages out of the side menu while remaining one click away.

### How to implement (in your plugin, not in Skin)

1. Create `controllers/<controller>/_breadcrumb_buttons.htm` in **your** plugin.
2. Guard with `$this->action === 'index'` so create/update/preview actions keep the default breadcrumb buttons.
3. Wrap links in `<div class="breadcrumb-buttons">` (see examples below).

Skin discovers the partial via the active controller's view path — **no registration in Skin is required**.

Without Skin installed, the file is simply unused; your side menu entries remain the fallback.

### Example — settings shortcut (SmallContactForm)

Path: `plugins/quendistudio/smallcontactform/controllers/messages/_breadcrumb_buttons.htm`

```php
<?php if ($this->action === 'index'): ?>
    <?php if ($this->user->hasAccess('quendistudio.smallcontactform.access_settings')): ?>
        <div class="breadcrumb-buttons">
            <a href="<?= Backend::url('system/settings/update/quendistudio/smallcontactform/settings') ?>" class="btn btn-sm btn-info wn-icon-cogs">
                <?= e(trans('quendistudio.smallcontactform::lang.controller.scoreboard.settings_btn')) ?>
            </a>
        </div>
    <?php endif ?>
<?php endif ?>
```

### Example — secondary controllers (Support)

Path: `plugins/quendistudio/support/controllers/tickets/_breadcrumb_buttons.htm`

Links to ticket types and statuses on the ticket list. When Skin is installed, `Quendistudio.Skin` also hides those items from the Support side menu and keeps the menu context on the tickets section when browsing types/statuses.

## Compatibility

- **Winter CMS**: 1.2.x
- **PHP**: 8.1+
- **Quendistudio.Support** (optional): ships `_breadcrumb_buttons.htm` on the Tickets controller; Skin applies the UX orchestration described above

## TODO

- **Dark theme i18n**: `window.skinI18n` is injected from `skins/enhanced/layouts/_mainmenu.php`. If you add a custom `modern/layouts/_mainmenu.php` (or override the enhanced layout), include the same snippet so the dark-theme label follows the backend locale:

  ```html
  <script>window.skinI18n = { darkTheme: <?= json_encode(trans('quendistudio.skin::lang.general.dark_theme')) ?> };</script>
  ```

## Support

- Website: [https://quendistudio.com](https://quendistudio.com)
- Issues: [https://github.com/quendistudio/skin/issues](https://github.com/quendistudio/skin/issues)

## License

See the `LICENCE` file for license details.
