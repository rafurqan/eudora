<?php
namespace App\Helpers;

/**
 * Format response.
 */
class ResponseFormatter
{
    /**
     * API Response
     *
     * @var array
     */
    protected static $response = [
        'meta' => [
            'code' => 200,
            'status' => 'success',
            'message' => null,
            'total' => 0,  // Menambahkan total untuk informasi paging
            'page' => 1,   // Halaman pertama
            'per_page' => 10,  // Jumlah item per halaman
            'last_page' => 1,  // Halaman terakhir
        ],
        'data' => null,
    ];

    /**
     * Give success response with pagination support.
     */
    public static function success($data = null, $message = null, $total = 0, $page = 1, $perPage = 10)
    {
        // Mengupdate meta data pagination
        self::$response['meta']['message'] = $message;
        if ($total > 0) {
            self::$response['meta']['total'] = $total;
            self::$response['meta']['page'] = $page;
            self::$response['meta']['per_page'] = $perPage;
            self::$response['meta']['last_page'] = ceil($total / $perPage); // Menghitung halaman terakhir berdasarkan total dan per_page
        }

        // Jika data kosong, bisa mengirimkan array kosong
        self::$response['data'] = $data ?? [];

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    /**
     * Give error response.
     */
    public static function error($data = null, $message = null, $code = 400)
    {
        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = $code;
        self::$response['meta']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }

    /**
     * Error response when data not found.
     */
    public static function errorNotFound($data = null, $message = "Data Not Found", $code = 404)
    {
        self::$response['meta']['status'] = 'error';
        self::$response['meta']['code'] = $code;
        self::$response['meta']['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, self::$response['meta']['code']);
    }
}
