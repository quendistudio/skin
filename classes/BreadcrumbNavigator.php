<?php

namespace Quendistudio\Skin\Classes;

use Schema;
use Exception;
use Backend\Classes\Controller;
use Backend\Behaviors\ListController;
use Backend\Behaviors\FormController;

/**
 * Skin override of the native FormController record navigation (Winter 1.2.14).
 *
 * Produces the same descriptor as FormController::formGetRecordNavigation() and
 * renders the native "record_navigation" partial, with two improvements over
 * the core implementation:
 *
 * - soft-deleted records are navigable (siblings restricted with onlyTrashed);
 * - the sibling query is optimized: eager loads dropped, SELECT reduced to the
 *   primary key and sort column (keeping computed sort columns and
 *   relation-count subqueries intact), read with a single cursor pass that
 *   exits early right after the next neighbour, plus a separate COUNT for the
 *   total.
 */
class BreadcrumbNavigator
{
    /**
     * Renders the native record navigation partial from the optimized
     * descriptor. Returns an empty string when navigation is not applicable,
     * matching the native formRenderRecordNavigation() contract.
     */
    public static function renderRecordNavigation(Controller $controller): string
    {
        $navigation = self::makeRecordNavigation($controller);
        if ($navigation === null || $navigation['current'] === null) {
            return '';
        }

        return $controller->formMakePartial('record_navigation', [
            'navigation' => $navigation,
            'navigationContext' => $controller->formGetContext(),
        ]);
    }

    /**
     * Builds the same descriptor as FormController::formGetRecordNavigation(),
     * respecting the current list sorting and active filters.
     *
     * @return array{previous: mixed, next: mixed, current: int|null, total: int}|null
     */
    public static function makeRecordNavigation(Controller $controller): ?array
    {
        if (!$controller->isClassExtendedWith(FormController::class)
            || !$controller->isClassExtendedWith(ListController::class)
        ) {
            return null;
        }

        if (!$controller->asExtension('FormController')->getConfig('recordNavigation', true)) {
            return null;
        }

        $action = $controller->formGetContext();
        if (!in_array($action, ['update', 'preview'], true)) {
            return null;
        }

        $model = $controller->formGetModel();
        if (!$model || !$model->exists) {
            return null;
        }

        if (!$controller->listWidgets || !count($controller->listWidgets)) {
            $controller->makeLists();
        }

        if (!$listWidget = $controller->listGetWidget()) {
            return null;
        }

        $primaryKey = $model->getKeyName();
        $sortColumn = $listWidget->getSortColumn() ?: $primaryKey;

        $columnDefinition = $listWidget->getColumn($sortColumn);
        $useRelationCount = $columnDefinition->config['useRelationCount'] ?? false;

        $listQuery = $listWidget->prepareQuery();

        // Navigate among trashed siblings when viewing a soft-deleted record
        if ($model->deleted_at ?? null) {
            $listQuery = $listQuery->onlyTrashed();
        }

        $query = self::buildOptimizedQuery($listQuery, $sortColumn, $primaryKey, $model, $useRelationCount);

        [$previous, $next, $position] = self::resolveNeighbors($query, $primaryKey, $model->getKey());

        if ($position === null) {
            return ['previous' => null, 'next' => null, 'current' => null, 'total' => 0];
        }

        return [
            'previous' => $previous,
            'next' => $next,
            'current' => $position,
            'total' => (clone $query)->toBase()->getCountForPagination(),
        ];
    }

    protected static function buildOptimizedQuery($listQueryFull, $sortColumn, $primaryKey, $model, $useRelationCount)
    {
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

    /**
     * Single cursor pass over the ordered siblings: records preceding the
     * current one only update the previous candidate, and iteration stops
     * right after capturing the next neighbour.
     *
     * @return array{0: mixed, 1: mixed, 2: int|null} [previous, next, 1-based position]
     */
    protected static function resolveNeighbors($query, string $primaryKey, $currentKey): array
    {
        $previous = null;
        $next = null;
        $position = null;
        $index = 0;

        foreach ($query->cursor() as $record) {
            $recordKey = $record->{$primaryKey} ?? null;

            if ($recordKey === null) {
                continue;
            }

            $index++;

            if ($position !== null) {
                $next = $recordKey;
                break;
            }

            if ((string) $recordKey === (string) $currentKey) {
                $position = $index;
            } else {
                $previous = $recordKey;
            }
        }

        return [$previous, $next, $position];
    }
}
