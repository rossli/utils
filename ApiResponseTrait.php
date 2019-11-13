<?php

namespace App\Utils;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;

trait ApiResponseTrait
{

    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param      $statusCode
     *
     * @param null $httpCode
     *
     * @return $this
     */
    public function setStatusCode($statusCode, $httpCode = NULL)
    : self {
        $httpCode = $httpCode ?? $statusCode;
        $this->statusCode = $statusCode;

        return $this;
    }

    /**
     * @param       $data
     *
     * @return mixed
     */
    public function respond($data)
    {
        return response()->json($data);
    }

    /**
     * @param       $status
     * @param array $data
     * @param null  $code
     *
     * @return mixed
     */
    public function status($status, array $data, $code = NULL)
    {

        if ($code) {
            $this->setStatusCode($code);
        }
        $status = [
            'status' => $status,
            'code'   => $this->statusCode,
        ];

        $data = array_merge($status, $data);

        return $this->respond($data);

    }

    /**
     * @param        $message
     * @param int    $code
     * @param string $status
     *
     * @return mixed
     */
    /*
     * 格式
     * data:
     *  code:422
     *  message:xxx
     *  status:'error'
     */
    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error')
    {

        return $this->setStatusCode($code)->message($message, $status);
    }

    /**
     * @param        $message
     * @param string $status
     *
     * @return mixed
     */
    public function message($message, $status = 'success')
    {

        return $this->status($status, [
            'message' => $message,
        ]);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function internalError($message = 'Internal Error!')
    {

        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function created($message = 'created')
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)->message($message);

    }

    /**
     * @param array  $data
     * @param string $status
     *
     * @param array  $links
     * @param array  $meta
     *
     * @return mixed
     */
    public function success($data = [], $status = 'success', $links = [], $meta = [])
    {
        return $this->status($status, array_filter(compact('data', 'links', 'meta')));
    }

    /**
     * @param string $message
     *
     * @return mixed
     */
    public function notFond($message = 'Not Fond!')
    {
        return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
    }
}
