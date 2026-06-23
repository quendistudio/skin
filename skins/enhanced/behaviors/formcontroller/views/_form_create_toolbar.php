<?php
// Alias so formMakePartial('toolbar') resolves the skin toolbar via controller->makePartial('form_create_toolbar')
$formContext = 'create';
include __DIR__ . '/../partials/_toolbar.php';
