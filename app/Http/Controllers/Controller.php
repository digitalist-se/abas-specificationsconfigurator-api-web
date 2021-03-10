<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests;
    use DispatchesJobs;
    use ValidatesRequests;

    /**
     * @param Request $request
     * @param         $map
     * @param         $target
     */
    protected function mapToInputData($request, $map, &$target)
    {
        foreach ($map as $key => $field) {
            if ($request->has($key)) {
                $newFieldValue = $request->input($key);
                if (is_array($target)) {
                    $target[$field] = $newFieldValue;
                } else {
                    $target->$field = $newFieldValue;
                }
            }
        }
    }
}
