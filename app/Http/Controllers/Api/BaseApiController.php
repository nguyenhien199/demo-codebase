<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Transformers\Serializer\CustomSerializer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\MessageBag;
use League\Fractal\Manager;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;

/**
 * Class BaseApiController
 *
 * @package App\Http\Controllers\Api
 */
class BaseApiController extends Controller
{
    public const MESSAGE_SUCCESS = 'SUCCESS';

    public const MESSAGE_ERROR = "Có lỗi xảy ra trên server";

    public const MULTIPLE_VALUE_DELIMITER = ',';

    private $_errors = [];

    /**
     * Validate request
     *
     * @param  Request  $request
     * @param  array    $rule
     *
     * @return MessageBag
     */
    public function validateRequest(Request $request, array $rule): MessageBag
    {
        $validator = Validator::make($request->all(), $rule);
        $isJsonRequest = $request->expectsJson();
        if (!$isJsonRequest) {
            $this->_errors[] = __('api/errors.request_accept_type_json_not_set');
        }
        if ($validator->fails()) {
            $this->_errors = $validator->errors();
        }

        return $this->_errors;
    }

    /**
     * Trả về response trường hợp lỗi
     *
     * @param  string  $message
     * @param  string  $errorCode
     *
     * @param  array   $data
     *
     * @return JsonResponse
     */
    public function throwErrorResponse(string $message = self::MESSAGE_ERROR, string $errorCode = Response::HTTP_INTERNAL_SERVER_ERROR, array $data = []): JsonResponse
    {
        $data = !empty($data) ? $data : [];

        return response()->json(
            [
                'data'   => $data,
                'msg'    => $message,
                'status' => false,
            ],
            $errorCode
        );
    }

    /**
     * Trả về response trường hợp thành công
     *
     * @param  string                    $message
     * @param  array                     $data
     * @param  TransformerAbstract|null  $transformer
     *
     * @return JsonResponse
     */
    public function throwSuccessResponse(string $message = self::MESSAGE_SUCCESS, $data = [], TransformerAbstract $transformer = null): JsonResponse
    {
        $data = !empty($data) ? $data : [];
        $data = $this->transform($data, $transformer);

        return response()->json(
            [
                'data'   => $data,
                'msg'    => $message,
                'status' => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Trả về response có phân trang trường hợp thành công
     *
     * @param  string                    $message
     * @param  LengthAwarePaginator      $pagination
     * @param  TransformerAbstract|null  $transformer
     *
     * @return JsonResponse
     */
    public function throwSuccessResponsePagination(string $message = self::MESSAGE_SUCCESS, $pagination = [], TransformerAbstract $transformer = null): JsonResponse
    {
        if (empty($pagination)) {
            return response()->json(
                [
                    'data'            => [],
                    'last_page'       => 0,
                    'total'           => 0,
                    'total_by_filter' => 0,
                    'msg'             => $message ?: self::MESSAGE_SUCCESS,
                    'status'          => true,
                ],
                Response::HTTP_OK
            );
        }

        $data = !empty($pagination->getCollection()) ? $pagination->getCollection() : [];
        $data = $this->transform($data, $transformer);

        return response()->json(
            [
                'data'            => $data,
                'per_page'        => $pagination->perPage(),
                'last_page'       => $pagination->lastPage(),
                'total'           => $pagination->total(),
                'total_by_filter' => $pagination->total(),
                'msg'             => $message ?: self::MESSAGE_SUCCESS,
                'status'          => true,
            ],
            Response::HTTP_OK
        );
    }

    /**
     * Transform data theo transformer
     *
     * @param      $data
     * @param      $transformer
     *
     * @return array|mixed|null
     */
    public function transform($data, $transformer)
    {
        if ($transformer && $transformer instanceof TransformerAbstract) {
            $fractal = new Manager();
            if ($requestedEmbeds = request()->input('include')) {
                $fractal->parseIncludes($requestedEmbeds);
            }

            $fractal->setSerializer(new CustomSerializer());
            if ($data instanceof \Illuminate\Database\Eloquent\Collection || is_array($data)) {
                $data = new Collection($data, $transformer);
            }

            if ($data instanceof Model) {
                $data = new Item($data, $transformer);
            }

            $data = $fractal->createData($data)->toArray();
        }

        return $data;
    }
}
