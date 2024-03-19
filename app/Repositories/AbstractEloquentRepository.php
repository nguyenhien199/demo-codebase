<?php

namespace App\Repositories;

use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Auth;

/**
 * Class AbstractEloquentRepository
 *
 * @package App\Repositories
 */
class AbstractEloquentRepository extends BaseRepository
{
    protected $_model;

    /**
     * @var string[] Danh sách các cột trong DB có thể dùng để sắp xếp
     */
    public $supportedSortingColumns = ['created_at'];

    /**
     * @var array Danh sách các cột trong DB sẽ dùng để tìm kiếm
     */
    public $columnsToSearch = [];

    /**
     * @var array Danh sách các cột trong DB sẽ dùng để filter
     */
    public $columnsToFilter = [];

    /**
     * @var string[] Danh sách các cột hỗ trợ filter theo khoảng thời gian
     */
    public $columnsToDateRangeFilter = ['created_at'];

    /**
     * @var array Danh sách các cột lưu kiểu JSON trong DB sẽ dùng để filter
     */
    public $jsonColumnsToFilter = [];

    public const DEFAULT_LIMIT = 25;

    public const DEFAULT_CURRENT_PAGE = 1;

    public const DEFAULT_ORDER_BY_FIELD = 'lastest_update_at';

    public const DEFAULT_ORDER_BY_DIRECTION = 'DESC';

    /**
     * Get supported record per page
     *
     * @return array
     */
    protected function _getAvailableLimits(): array
    {
        return [25, 40, 80, 100];
    }

    /**
     * Find entity by ID
     *
     * @param            $id
     * @param  string[]  $columns
     *
     * @return mixed
     */
    public function find($id, $columns = ['*'])
    {
        return $this->_model::whereNull('deleted_at')->find($id, $columns);
    }

    protected function setWhereOrConditions($collection, $conditions = [])
    {
        $collection->where(function ($query) use ($conditions) {
            $counter = 1;
            foreach ($conditions as $key => $value) {
                if ($counter > 1) {
                    $query->orWhere($key, $value);
                } else {
                    $query->where($key, $value);
                }
                $counter++;
            }
        });
        return $collection;
    }

    /**
     * Get records by conditions
     *
     * @param  array  $conditions
     *
     * @return mixed
     */
    public function getByConditions(array $conditions = [])
    {
        $collection = $this->_model;
        if (!empty($conditions)) {
            foreach ($conditions as $key => $value) {
                if (is_array($value)) {
                    if ($key === 'or') {
                        $collection = $this->setWhereOrConditions($collection, $value);
                    } else {
                        $collection = $collection->whereIn($key, $value);
                    }
                } else {
                    $collection = $collection->where($key, $value);
                }
            }
        }
        return $collection->get();
    }

    /**
     * Trả về tất cả bản ghi chưa bị xóa
     *
     * @param  string[]  $columns
     *
     * @return mixed
     */
    public function findAll($columns = ['*'])
    {
        return $this->_model::select($columns)->where(['deleted_at' => null])->get();
    }

    /**
     * Update
     *
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->_model->create($attributes);
    }

    /**
     * Update
     *
     * @param         $id
     * @param  array  $attributes
     *
     * @return mixed
     */
    public function update($id, array $attributes)
    {
        $object = $this->_model->findOrFail($id);
        $object->fill($attributes);
        $object->save();

        return $object;
    }

    /**
     * Delete
     *
     * @param         $id
     * @param  array  $softDeleteData
     * @param  bool   $isSoftDelete
     *
     * @return bool
     * @throws Exception
     */
    public function delete($id, array $softDeleteData = [], $isSoftDelete = true): bool
    {
        if ($isSoftDelete) {
            $object = $this->_model->findOrFail($id);
            $user = Auth::user();
            $deletedById = $user ? $user->id : 0;
            $deletedByName = $user ? $user->fullname : 'SYSTEM';

            $object->fill(
                [
                    'deleted_by_id'   => $deletedById,
                    'deleted_by_name' => $deletedByName,
                    'deleted_at'      => Carbon::now(),
                ]
            );
            $object->save();

            return true;
        }

        return $this->_model->destroy($id);
    }

    /**
     * Phân trang theo điều kiện lọc với key 'page_size' và 'page' trong mảng conditions
     *
     * @param         $collection
     * @param  array  $conditions
     * @param  array  $columns
     *
     * @return mixed
     */
    public function applyPagination($collection, array $conditions = [], array $columns = ['*'])
    {
        if (!empty($conditions['is_get_all']) && $conditions['is_get_all'] == 1) {
            $pageSize = $collection->count();
        } else {
            $pageSize = isset($conditions['page_size']) ? intval($conditions['page_size']) : self::DEFAULT_RECORDS_PER_PAGE;
        }
        $page = isset($conditions['page']) ? intval($conditions['page']) : 1;

        return $collection->paginate($pageSize, $columns, 'page', $page);
    }

    /**
     * Paginate
     *
     * @param  null      $limit  number of item per page
     *
     * @param  string[]  $columns
     *
     * @return mixed
     */
    public function paginate($limit = null, $columns = ['*'])
    {
        $limit = (in_array((int) $limit, $this->_getAvailableLimits())) ? self::DEFAULT_LIMIT : (int) $limit;

        return $this->_model::select($columns)->whereNull('deleted_at')->paginate($limit);
    }

    /**
     * Sắp xếp theo điều kiện với key 'sorts' trong mảng conditions
     *
     * @param         $collection
     * @param  array  $conditions
     *
     * @return mixed
     */
    public function applySorts($collection, array $conditions = [])
    {
        if (!empty($conditions['sort'])) {
            $sorts = explode(',', $conditions['sort']);
            foreach ($sorts as $sortData) {
                $order = ($sortData[0] == '-') ? 'desc' : 'asc';
                $column = str_replace(['-', '+', ' '], '', $sortData);
                if (in_array($column, $this->supportedSortingColumns)) {
                    $collection = $collection->orderBy("${column}", $order);
                }
            }
        } else {
            $collection = $collection->orderBy('created_at', 'desc');
        }

        return $collection;
    }

    /**
     * Tìm kiếm
     *
     * @param         $collection
     * @param  array  $conditions
     *
     * @return mixed
     */
    public function applySearch($collection, array $conditions = [])
    {
        $columnsToSearch = $this->columnsToSearch;
        if (empty($columnsToSearch)) {
            return $collection;
        }

        if (!empty($conditions['query'])) {
            $search = trim($conditions['query']);
            $collection = $collection->where(
                function ($query) use ($search, $columnsToSearch) {
                    foreach ($columnsToSearch as $index => $column) {
                        if ($index === 0) {
                            $query->where($column, 'like', '%'.$search.'%');
                        } else {
                            $query->orWhere($column, 'like', '%'.$search.'%');
                        }
                    }
                }
            );
        }

        return $collection;
    }

    /**
     * Filter theo các field
     *
     * @param         $collection
     * @param  array  $conditions
     *
     * @return mixed
     */
    public function applyFilters($collection, array $conditions = [])
    {
        $columnsToFilter = $this->columnsToFilter;
        foreach ($columnsToFilter as $column) {
            if (array_key_exists($column, $conditions)) {
                if (is_null($conditions[$column])) {
                    continue;
                }
                $filterValues = array_map('trim', explode(',', $conditions[$column]));
                $collection = $collection->whereIn($column, $filterValues);
            }
        }

        $jsonColumnsToFilter = $this->jsonColumnsToFilter;
        foreach ($jsonColumnsToFilter as $column) {
            if (array_key_exists($column, $conditions)) {
                if (is_null($conditions[$column])) {
                    continue;
                }
                $filterValues = array_map('trim', explode(',', $conditions[$column]));
                foreach ($filterValues as $index => $value) {
                    // FIXME:
                    // TODO: Đang fix tạm với giả định lưu các giá trị vào DB đều là số nguyên
                    $value = intval($value);
                    if ($index === 0) {
                        $collection = $collection->whereJsonContains($column, $value);
                    } else {
                        $collection = $collection->orWhereJsonContains($column, $value);
                    }
                }
            }
        }

        return $collection;
    }

    /**
     * Filter theo các field theo khoảng thời gian
     *
     * @param         $collection
     * @param  array  $conditions
     */
    public function applyDateRangeFilters($collection, array $conditions = [])
    {
        $supportedDateTypeFilters = $this->columnsToDateRangeFilter;
        if (empty($supportedDateTypeFilters)) {
            return $collection;
        }
        foreach ($supportedDateTypeFilters as $dateType) {
            if (!empty($conditions[$dateType])) {
                $dateRange = explode(',', $conditions[$dateType]);
                if (empty($dateRange)) {
                    continue;
                }

                $fromDate = $dateRange[0];
                if ($fromDate) {
                    $fromDate = date('Y-m-d', strtotime($fromDate));
                }

                $toDate = $dateRange[1];
                if ($toDate) {
                    $toDate = date('Y-m-d', strtotime("+1 day", strtotime($toDate)));
                }

                if ($fromDate) {
                    $collection->where($dateType, '>=', $fromDate);
                }
                if ($toDate) {
                    $collection->where($dateType, '<', $toDate);
                }
            }
        }

        return $collection;
    }

    /**
     * Cập nhật theo danh sách id tương ứng
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

    /**
     * Áp dụng tham số $relations
     *
     * @param         $collection
     * @param  array  $relations
     *
     * @return mixed
     */
    public function applyRelations($collection, array $relations = [])
    {
        if (!empty($relations)) {
            $collection->with($relations);
        }
        return $collection;
    }
}
