<?php

namespace App\Http\Controllers\API\Master;

use App\Models\MessageTemplate;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Helpers\ResponseFormatter;

class MessageTemplateController extends Controller
{
    public function index(Request $request)
    {
        $search   = $request->input('search');
        $perPage  = $request->input('per_page', 10);

        $templates = MessageTemplate::when($search, function ($query, $search) {
                $query->where('name', 'ilike', "%{$search}%")
                      ->orWhere('body', 'ilike', "%{$search}%");
            })
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return ResponseFormatter::success(
            data: $templates,
            message: 'List Message Templates'
        );
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:message_templates,name',
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $template = MessageTemplate::create($validator->validated());

        return ResponseFormatter::success(
            data: $template,
            message: 'Template berhasil dibuat'
        );
    }

    public function show($id)
    {
        $template = MessageTemplate::findOrFail($id);

        return ResponseFormatter::success(
            data: $template,
            message: 'Detail Template'
        );
    }

    public function update(Request $request, $id)
    {
        $template = MessageTemplate::findOrFail($id);

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|unique:message_templates,name,' . $id,
            'body' => 'required|string',
        ]);

        if ($validator->fails()) {
            return ResponseFormatter::error(
                data: null,
                message: $validator->errors(),
                code: 422
            );
        }

        $template->update($validator->validated());

        return ResponseFormatter::success(
            data: $template,
            message: 'Template berhasil diupdate'
        );
    }

    public function destroy($id)
    {
        $template = MessageTemplate::findOrFail($id);
        $template->delete();

        return ResponseFormatter::success(
            data: null,
            message: 'Template berhasil dihapus'
        );
    }
}
