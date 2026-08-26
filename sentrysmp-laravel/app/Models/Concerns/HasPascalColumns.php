<?php

namespace App\Models\Concerns;

/**
 * Maps snake_case attribute access to PascalCase DB columns.
 * The DB columns from EF Core are PascalCase (e.g. "ServerId", "GlobalMaxOrder").
 * This trait lets Eloquent read/write them transparently via snake_case aliases.
 */
trait HasPascalColumns
{
    /**
     * Convert a snake_case key to PascalCase for the actual DB column.
     * Eloquent calls this when building queries.
     */
    public function qualifyColumn($column): string
    {
        return parent::qualifyColumn($this->toPascal($column));
    }

    /** Intercept attribute get so snake_case keys map to PascalCase columns */
    public function getAttribute($key)
    {
        // Try the key as-is first (covers PascalCase direct access)
        $value = parent::getAttribute($key);
        if ($value !== null) {
            return $value;
        }

        // Try PascalCase variant
        $pascal = $this->toPascal($key);
        if ($pascal !== $key) {
            return parent::getAttribute($pascal);
        }

        return $value;
    }

    private function toPascal(string $key): string
    {
        return str_replace('_', '', ucwords($key, '_'));
    }
}
