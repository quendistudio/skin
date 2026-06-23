<?php

namespace Quendistudio\Skin\Classes;

use Schema;
use Exception;
use Backend\Classes\Controller;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;
use Backend;

class BreadcrumbNavigator
{
    /**
     * Computes previous and next record identifiers for the current model,
     * respecting the current list sorting and active filters.
     *
     * The method is intentionally decoupled from the backend controller. It only
     * relies on the prepared list widget (with its query and sorting already applied)
     * and the current model instance.
     *
     * @param Controller $controller Controller instance
     *
     * @return string|void The rendered HTML for the navigation buttons, or void if not applicable
     */
    public static function makeBreadcrumbNavigationButtons($controller)
    {
        if (!$controller->isClassExtendedWith(ListController::class)) {
            return;
        }

        if (!$controller->isClassExtendedWith(FormController::class)) {
            return;
        }

        $action = $controller->formGetContext();
        if (!in_array($action, ['update', 'preview'], true)) {
            return;
        }

        if (!$model = $controller->formGetModel()) {
            return;
        }

        if (!$controller->listWidgets || !count($controller->listWidgets)) {
            $controller->makeLists();
        }

        if (!$listWidget = $controller->listGetWidget()) {
            return;
        }

        $primaryKey = $model->getKeyName();
        $sortColumn = $listWidget->getSortColumn() ?: $primaryKey;

        $columnDefinition = $listWidget->getColumn($sortColumn);
        $useRelationCount = $columnDefinition->config['useRelationCount'] ?? false;

        $listQueryFull = $listWidget->prepareQuery();

        if($model->deleted_at) {
            $listQueryFull = $listQueryFull->onlyTrashed();
        }
        
        $optimizedQuery = self::buildOptimizedQuery($listQueryFull, $sortColumn, $primaryKey, $model, $useRelationCount);

        $previousId = self::resolveNeighbor('previous', $model, $primaryKey, $optimizedQuery);
        $nextId = self::resolveNeighbor('next', $model, $primaryKey, $optimizedQuery);

        if (!$previousId && !$nextId) {
            return;
        }

        if (!$parentUrl = $controller->formGetRedirectUrl($action, $model)) {
            return;
        }

        return $controller->makeLayoutPartial('breadcrumb_navigation_buttons', [
            'prevHref'     => $previousId ? Backend::url($parentUrl . '/' . $action . '/' . $previousId) : '',
            'nextHref'  => $nextId ? Backend::url($parentUrl . '/' . $action . '/' . $nextId) : '',
        ]);
    }

    protected static function buildOptimizedQuery($listQueryFull, $sortColumn, $primaryKey, $model, $useRelationCount) {
        $optimizedQuery = clone $listQueryFull;
        $query = $optimizedQuery->getQuery();

        // Remove eager loaded relations to keep the query lightweight
        $query->eagerLoads = [];
        // Drop join bindings that are no longer needed
        $query->bindings['join'] = [];

        // For columns based on useRelationCount (e.g. withCount on relations),
        // do not touch the SELECT to keep subquery bindings (placeholders) intact.
        if ($useRelationCount) {
            return $optimizedQuery;
        }

        $isCalculatedColumn = false;

        try {
            $isCalculatedColumn = !Schema::hasColumn($model->getTable(), $sortColumn);
        } catch (Exception $e) {
            // If the schema lookup fails, assume it is a real column
        }

        if ($isCalculatedColumn) {
            // For calculated columns, find and keep only the calculated sort column
            $originalColumns = $listQueryFull->getQuery()->columns ?? [];
            $selectColumns = [];

            foreach ($originalColumns as $column) {
                $columnStr = '';
                if (is_string($column)) {
                    $columnStr = $column;
                } elseif (is_object($column)) {
                    if (method_exists($column, 'getValue')) {
                        $columnStr = $column->getValue();
                    } elseif (method_exists($column, '__toString')) {
                        $columnStr = (string) $column;
                    }
                }

                if ($columnStr && preg_match('/\s+as\s+[`"]?' . preg_quote($sortColumn, '/') . '[`"]?$/i', $columnStr)) {
                    $selectColumns[] = $column;
                    break;
                }
            }

            $tableName = $model->getTable();
            $selectColumns[] = $tableName . '.' . $primaryKey;

            $optimizedQuery->select($selectColumns);
        } else {
            // Real columns: select only primary key and sort column
            $optimizedQuery->select([$primaryKey, $sortColumn]);
        }

        return $optimizedQuery;
    }

    protected static function resolveNeighbor(string $direction, $model, $primaryKey, $optimizedQuery) {
        $isPrev = $direction === 'previous';
        $currentKey = $model->getKey();
        $previousKey = null;
        $foundCurrent = false;

        foreach ($optimizedQuery->cursor() as $record) {
            $recordKey = $record->{$primaryKey} ?? null;

            if ($recordKey === null) {
                continue;
            }

            if ($recordKey == $currentKey) {
                $foundCurrent = true;

                if ($isPrev) {
                    return $previousKey ?? 0;
                }

                continue;
            }

            if ($foundCurrent && !$isPrev) {
                return (int) $recordKey;
            }

            if (!$foundCurrent && $isPrev) {
                $previousKey = $recordKey;
            }
        }

        return 0;
    }
}

