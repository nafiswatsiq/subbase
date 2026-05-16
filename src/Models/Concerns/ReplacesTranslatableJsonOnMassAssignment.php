<?php

namespace Nafiswatsiq\Subbase\Models\Concerns;

/**
 * Spatie's setTranslations() merges each locale into existing JSON, so locales removed
 * in the UI (e.g. Filament repeater) never disappear. Mass-assigning an associative
 * locale map should replace the stored translations entirely.
 */
trait ReplacesTranslatableJsonOnMassAssignment
{
    public function setAttribute($key, $value)
    {
        if (
            $this->isTranslatableAttribute($key)
            && is_array($value)
            && (! array_is_list($value) || count($value) === 0)
        ) {
            $this->attributes[$key] = $this->asJson($value);

            return $this;
        }

        return parent::setAttribute($key, $value);
    }
}
