<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Api\BaseApiController;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Services\UserService;
use App\Transformers\UserTransformer;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class UserController extends BaseApiController
{
    /**
     * @var UserRepositoryInterface
     */
    protected $_userRepository;

    /**
     * @var UserService
     */
    protected $_userService;

    /**
     * UserController constructor.
     *
     * @param  UserRepositoryInterface  $userRepository
     * @param  UserService     $userService
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        UserService             $userService
    ) {
        $this->_userRepository   = $userRepository;
        $this->_userService      = $userService;
    }

    /**
     * Lấy danh sách user
     *
     * @param  Request  $request
     *
     * @return JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        try {
            $conditions = $request->all();
            $collection = $this->_userRepository->findByConditions($conditions);

            return $this->throwSuccessResponsePagination('OK', $collection, new UserTransformer());
        } catch (Exception $e) {
            Log::error(
                'API - Lỗi khi lấy danh sách user',
                [
                    'line'          => __LINE__,
                    'method'        => __METHOD__,
                    'error_message' => $e->getMessage(),
                ]
            );

            return $this->throwErrorResponse(__('api/errors.error_500'));
        }
    }
}
