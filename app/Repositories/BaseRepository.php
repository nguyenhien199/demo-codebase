<?php

namespace App\Repositories;

use Illuminate\Database\Eloquent\Model;
use App\Repositories\Contracts\BaseRepositoryInterface;

/**
 * Class BaseRepository
 * @package App\Repositories
 */
abstract class BaseRepository implements BaseRepositoryInterface
{
    public const DEFAULT_RECORDS_PER_PAGE = 25;

    /**
     * @var Model
     */
    protected $_model;

    public function __construct()
    {
        $this->_model = $this->setModel();
    }

    public function setModel()
    {
        return $this->_model = app()->make($this->_model);
    }

    public function find($id, $columns = ['*'])
    {
        return $this->_model->findOrFail($id);
    }

    public function exists($id):bool
    {
        return $this->_model->where('id', $id)->exists();
    }

    public function all()
    {
        return $this->_model->all();
    }

    public function paginate($limit = null, $columns = ['*'])
    {
        $limit = is_null($limit) ? self::DEFAULT_RECORDS_PER_PAGE : intval($limit);

        return $this->_model->paginate($limit);
    }

    public function create(array $attributes)
    {
        $attributes = $this->stripAllFields($attributes);

        return $this->_model->create($attributes);
    }

    public function update($id, array $attributes)
    {
        $object = $this->_model->findOrFail($id);
        $attributes = $this->stripAllFields($attributes);
        $object->fill($attributes);
        $object->save();

        return $object;
    }

    public function delete($id, array $softDeleteData = [], $isSoftDelete = true)
    {
    }

    public function stripAllFields($fields)
    {
        foreach ($fields as $key => $value) {
            if (is_array($fields[$key])) {
                $fields[$key] = $this->stripAllFields($fields[$key]);
            } else {
                if (is_string($value)) {
                    $fields[$key] = strip_tags($value);
                }
            }
        }

        return $fields;
    }

    public function findAll($columns = ['*'])
    {
    }

    public function permanentlyDelete($id): bool
    {
        $object = $this->_model->findOrFail($id);
        $object->forceDelete();
        return true;
    }
}
