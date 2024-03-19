<?php

namespace App\Repositories\Contracts;

/**
 * Interface UserRepositoryInterface
 * @package App\Repositories\Contracts
 */
interface UserRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Trả về danh sách user
     *
     * @return mixed
     */
    public function findByLoggedInUser();

    /**
     * Trả về danh sách user theo điều kiện
     *
     * @param  array     $conditions
     * @param  string[]  $columns
     *
     * @return mixed
     */
    public function findByConditions(array $conditions = [], array $columns = ['*']);

}
