<?php

namespace App\Repositories;

use App\Models\User;
use App\Repositories\Contracts\UserRepositoryInterface;
use Exception;

/**
 * Class UserRepository
 *
 * @package App\Repositories
 */
class UserRepository extends AbstractEloquentRepository implements UserRepositoryInterface
{
    /**
     * UserRepository constructor.
     *
     * @param  User  $model
     */
    public function __construct(User $model)
    {
        $this->_model = $model;
    }

    /**
     * Trả về danh sách user
     *
     * @return mixed
     * @throws Exception
     */
    public function findByLoggedInUser()
    {
        return $this->_model->whereNull('deleted_at');
    }

    /**
     * Trả về danh sách user theo điều kiện
     *
     * @param  array  $conditions
     * @param  array  $columns
     *
     * @return mixed
     * @throws Exception
     */
    public function findByConditions(array $conditions = [], array $columns = ['*'])
    {
        $collection = $this->findByLoggedInUser();

        // Apply search condition
        $collection = $this->_applySearch($collection, $conditions);

        // Apply filter by condition
        $collection = $this->_applyFilters($collection, $conditions);

        // Apply sort by condition
        $collection = $this->applySorts($collection, $conditions);

        // Apply pagination by condition
        return $this->applyPagination($collection, $conditions, $columns);
    }

    /**
     * Áp dụng lọc theo điều kiện với key 'filters' trong mảng conditions
     *
     * @param         $collection
     * @param  array  $conditions
     *
     * @return mixed
     */
    protected function _applyFilters($collection, array $conditions = [])
    {
        $supportedJsonColumns = [];
        $supportedFilteringColumns = ['status'];

        foreach ($conditions as $field => $values) {
            if (!isset($values)) {
                continue;
            }

            $values = array_map('trim', explode(',', $values));
            if (in_array($field, $supportedFilteringColumns)) {
                $collection->whereIn("${field}", $values);
            }

            if (in_array($field, $supportedJsonColumns)) {
                $counter = 1;
                foreach ($values as $value) {
                    $value = "${value}";
                    if ($counter > 1) {
                        $collection->orWhereJsonContains("${field}", $value);
                    } else {
                        $collection->whereJsonContains("${field}", $value);
                    }

                    $counter++;
                }
            }
        }

        return $collection;
    }

    /**
     * Áp dụng tìm kiếm theo điều kiện với key 'query' trong mảng conditions
     *
     * @param         $collection
     * @param  array  $conditions
     *
     * @return mixed
     */
    protected function _applySearch($collection, array $conditions = [])
    {
        if (!empty($conditions['query'])) {
            $query = trim($conditions['query']);
            $collection = $collection->where(function ($data) use ($query) {
                $data->where('name', 'like', '%'.$query.'%');
            });
        }

        return $collection;
    }

    /**
     * Cập nhật loại user theo danh sách id tương ứng
     *
     * @param  array  $ids
     * @param  array  $data
     *
     * @return mixed
     */
    public function updateByIds(array $ids, array $data)
    {
        return $this->_model->whereIn('id', $ids)->update($data);
    }
}
