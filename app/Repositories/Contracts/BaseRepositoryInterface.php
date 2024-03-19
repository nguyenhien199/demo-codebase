<?php

namespace App\Repositories\Contracts;

/**
 * Interface BaseRepositoryInterface
 */
interface BaseRepositoryInterface
{
    public function find($id, $columns = ['*']);

    public function exists($id): bool;

    public function findAll($columns = ['*']);

    public function paginate($limit = null, $columns = ['*']);

    public function create(array $attributes);

    public function update($id, array $attributes);

    public function delete($id, array $softDeleteData = [], $isSoftDelete = true);

    public function permanentlyDelete($id);
}
