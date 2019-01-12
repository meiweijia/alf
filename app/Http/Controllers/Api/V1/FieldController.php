<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Api\ApiController;
use App\Models\Field;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class FieldController extends ApiController
{
    public function getFields(Request $request)
    {
        $this->checkPar($request,[
            'weekday' => 'required',
            'type' => 'required',
        ]);

        $weekday = $request->input('weekday') ?? date("w");

        $data = Field::query()->select('id', 'no', 'name')->with(['profile' => function ($query) use ($weekday) {
            $query->select('id', 'field_id', 'weekday', 'time', 'fees', 'amount')
                ->where('weekday', $weekday);
        }])->where('type', $request->input('type'))
            ->get();
        return $this->success($data);
    }
}
