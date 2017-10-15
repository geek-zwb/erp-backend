<?php
/**
 * Created by PhpStorm.
 * User: geekzwb
 * Date: 2017/10/11
 * Time: 下午5:32
 */

namespace App\Api\Helpers;
use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Response;

trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @return int
     */
    public function getStatusCode() {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode) {
        $this->$statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $header = []) {
        return Response::json($data, $this->getStatusCode(), $header);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, array $data, $code = null) {
        if($code) {
            $this->setStatusCode($code);
        }

        $status = [
          'status' => $status,
          'code' => $this->statusCode
        ];

        $data = array_merge($status, $data);

        return $this->respond($data);
    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error') {
        return $this->setStatusCode($code)->message($message, $status);
    }

    /**
     * @param $message
     * @param string $status
     * @return mixed
     */
    public function message($message, $status = 'success') {
        return $this->status($status, [
           'message' => $message
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function internalError($message = 'Internal Error!') {
        return $this->setStatusCode(FoundationResponse::HTTP_INTERNAL_SERVER_ERROR)
            ->message($message);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function created($message = 'created') {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)
            ->message($message);
    }

    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data, $status = 'success') {
        return $this->status($status, compact('data'));
    }

    public function successWithCollection($response, $status = 'success') {
        $response->content();
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function notFound($message = 'Not Found!') {
        return $this->failed($message, FoundationResponse::HTTP_NOT_FOUND);
    }
}