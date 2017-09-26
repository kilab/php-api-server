<?php

namespace Kilab\Api;

class Resource
{

    /**
     * Get entity with all fields and values.
     *
     * @return array
     */
    public function getWholeEntity(): array
    {
        $fields = get_object_vars($this);
        $fieldsWithValues = [];

        foreach ($fields as $field => $value) {
            $fieldsWithValues[$field] = $value;
        }

        return $fieldsWithValues;
    }

    /**
     * Set values for all given fields. Excluding id field.
     *
     * @param array $fields
     */
    public function setWholeEntity(array $fields): void
    {
        foreach ($fields as $field => $value) {
            if ($field === 'id') {
                continue;
            }

            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
    }
}
