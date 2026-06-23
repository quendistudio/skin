<?php
// Alias so formMakePartial('toolbar') resolves the skin toolbar via controller->makePartial('form_update_toolbar')
$formContext = 'update';
include __DIR__ . '/../partials/_toolbar.php';
