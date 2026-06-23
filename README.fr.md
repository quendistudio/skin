# Quendistudio.Skin

Améliore l'interface d'administration Winter CMS avec des menus déroulants, des fils d'Ariane gérant les sous-pages, des boutons précédent/suivant sur les formulaires, et des boutons optionnels à droite du fil d'Ariane.

Les plugins Quendistudio fonctionnent **sans** Skin (les contrôleurs secondaires restent dans le menu latéral). Skin est **recommandé** pour une navigation optimale.

> Documentation en anglais : [README.md](README.md)

## Installation

### Composer (recommandé)

```bash
composer require quendistudio/skin
php artisan winter:up
```

Le skin est appliqué automatiquement au backend lorsque le plugin est activé.

Backend → **Réglages** → **Skin** (catégorie QuendiStudio Plugins). Accordez la permission `quendistudio.skin.administrate` aux administrateurs concernés.

### Manuel

Clonez dans `plugins/quendistudio/skin`, puis :

```bash
php artisan winter:up
```

## Boutons du fil d'Ariane (`_breadcrumb_buttons.htm`)

Sur les contrôleurs de **liste** (action `index`), Skin affiche un partial nommé `breadcrumb_buttons` à droite du fil d'Ariane. Cela permet de garder les sous-pages peu utilisées hors du menu latéral tout en restant à un clic.

### Comment implémenter (dans votre plugin, pas dans Skin)

1. Créez `controllers/<controller>/_breadcrumb_buttons.htm` dans **votre** plugin.
2. Protégez avec `$this->action === 'index'` pour que les actions create/update/preview conservent les boutons par défaut.
3. Encapsulez les liens dans `<div class="breadcrumb-buttons">` (voir exemples ci-dessous).

Skin découvre le partial via le chemin de vue du contrôleur actif — **aucune inscription dans Skin n'est requise**.

Sans Skin installé, le fichier est simplement ignoré ; les entrées du menu latéral restent le repli.

### Exemple — raccourci réglages (SmallContactForm)

Chemin : `plugins/quendistudio/smallcontactform/controllers/messages/_breadcrumb_buttons.htm`

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

### Exemple — contrôleurs secondaires (Support)

Chemin : `plugins/quendistudio/support/controllers/tickets/_breadcrumb_buttons.htm`

Liens vers les types et statuts de tickets depuis la liste. Lorsque Skin est installé, `Quendistudio.Skin` masque aussi ces entrées du menu latéral Support et conserve le contexte menu sur la section tickets lors de la navigation types/statuts.

## Compatibilité

- **Winter CMS** : 1.2.x
- **PHP** : 8.1+
- **Quendistudio.Support** (optionnel) : fournit `_breadcrumb_buttons.htm` sur le contrôleur Tickets ; Skin applique l'orchestration UX décrite ci-dessus

## TODO

- **i18n thème sombre** : `window.skinI18n` est injecté depuis `skins/enhanced/layouts/_mainmenu.php`. Si vous ajoutez un `modern/layouts/_mainmenu.php` personnalisé (ou surchargez le layout enhanced), incluez le même snippet pour que le libellé du thème sombre suive la locale backend :

  ```html
  <script>window.skinI18n = { darkTheme: <?= json_encode(trans('quendistudio.skin::lang.general.dark_theme')) ?> };</script>
  ```

## Support

- Site web : [https://quendistudio.com](https://quendistudio.com)
- Issues : [https://github.com/quendistudio/skin/issues](https://github.com/quendistudio/skin/issues)

## Licence

Voir le fichier `LICENCE` pour les détails de licence.
