<?php

namespace App\Http\Controllers;

use App\Http\Resources\Answer as AnswerResource;
use App\Models\Answer;
use App\Models\Element;
use Illuminate\Http\Request;

class AnswerController extends Controller
{
    public function list(Request $request)
    {
        $answers = $request->user()->answers()->get();

        return AnswerResource::collection($answers);
    }

    protected function getAnswer(Request $request, Element $element)
    {
        return Answer::where('user_id', '=', $request->user()->id)
            ->where('element_id', '=', $element->id)
            ->first();
    }

    public function get(Request $request, Element $element)
    {
        $answer = $this->getAnswer($request, $element);
        if (!$answer) {
            abort(404);
        }

        return new AnswerResource($answer);
    }

    public function update(Request $request, Element $element)
    {
        $this->validate($request, [
            'value' => 'required',
        ]);
        $value     = $request->input('value');
        $oldAnswer = $this->getAnswer($request, $element);
        if ($oldAnswer) {
            $this->authorize('update', $oldAnswer);
            $oldAnswer->value = $value;
            $oldAnswer->saveOrFail();
        } else {
            $newAnswer = Answer::make([
                'user_id'    => $request->user()->id,
                'element_id' => $element->id,
                'value'      => $value,
            ]);
            $this->authorize('create', $newAnswer);
            $newAnswer->saveOrFail();
        }

        return response('', 204);
    }

    public function reset(Request $request)
    {
        Answer::where('user_id', '=', $request->user()->id)->delete();

        return response('', 204);
    }
}
