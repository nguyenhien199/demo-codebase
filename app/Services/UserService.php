<?php

namespace App\Services;

use App\Models\User;

use App\Repositories\Contracts\UserRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

/**
 * Class UserService
 * @package App\Services
 */
class UserService
{
    public const LIMIT_LOAD_MORE_CUSTOMER = 10;

    /**
     * @var UserRepositoryInterface
     */
    protected $_userRepository;
    /**
     * UserService constructor.
     *
     * @param UserRepositoryInterface $userRepositoryInterface
     */

    public function __construct(
        UserRepositoryInterface $userRepositoryInterface
    ) {
        $this->_userRepository = $userRepositoryInterface;

    }


    /**
     * Chuẩn hóa dữ liệu trước khi tạo mới user
     *
     * @param $data
     *
     * @return array
     */
    protected function _normalizeData($data): array
    {
        return $data;
    }

    /**
     * Chuẩn hóa dữ liệu trước khi cập nhật user
     *
     * @param $data
     *
     * @return array
     */
    protected function _normalizeUpdate($data): array
    {

        return $data;
    }


    /**
     * Thêm user mới
     *
     * @param array $data
     *
     * @return User
     */
    public function create(array $data): User
    {
        $normalizedData = $this->_normalizeData($data);

        return $this->_userRepository->create($normalizedData);
    }

    /**
     * Cập nhật user
     *
     * @param int   $id
     * @param array $data
     *
     * @return User
     */
    public function update($id, array $data): User
    {
        $normalizedData = $this->_normalizeUpdate($data);

        return $this->_userRepository->update($id, $normalizedData);
    }

    public function delete($id): bool
    {
        $contract = $this->_userRepository->find($id);
        $contract->update(
            [
                'deleted_by_id'   => Auth::id(),
                'deleted_by_name' => Auth::user()->fullname,
                'deleted_at'      => Carbon::now(),
            ]
        );

        return true;
    }
}
