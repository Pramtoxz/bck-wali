<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\ImageCompressor;
use App\Http\Controllers\Controller;
use App\Models\FieldDuty;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FieldDutyController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 3);

        $duties = FieldDuty::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($duty) {
                return [
                    'id' => $duty->id,
                    'date' => Carbon::parse($duty->created_at)->locale('id')->isoFormat('DD MMM YYYY'),
                    'start_date' => $duty->start_date->format('Y-m-d'),
                    'end_date' => $duty->end_date->format('Y-m-d'),
                    'total_days' => $duty->total_days,
                    'destination' => $duty->destination,
                    'purpose' => $duty->purpose,
                    'status' => $duty->status,
                    'submitted_at' => $duty->created_at->toIso8601String(),
                ];
            });

        return ApiResponse::success($duties);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date|after:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'destination' => 'required|string|max:255',
            'purpose' => 'required|string',
            'document' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $document = $request->file('document');
        
        if (str_starts_with($document->getMimeType(), 'image/')) {
            $compressor = new ImageCompressor(maxSizeMB: 1.9);
            $document = $compressor->compress($document);
        }
        
        $documentPath = $document->store('documents/field-duty', 'public');

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $fieldDuty = FieldDuty::create([
            'user_id' => $request->user()->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'destination' => $validated['destination'],
            'purpose' => $validated['purpose'],
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return ApiResponse::success([
            'id' => $fieldDuty->id,
            'start_date' => $fieldDuty->start_date->format('Y-m-d'),
            'end_date' => $fieldDuty->end_date->format('Y-m-d'),
            'total_days' => $fieldDuty->total_days,
            'destination' => $fieldDuty->destination,
            'purpose' => $fieldDuty->purpose,
            'document_url' => asset('storage/' . $fieldDuty->document_path),
            'status' => $fieldDuty->status,
            'submitted_at' => $fieldDuty->created_at->toIso8601String(),
        ], 'Pengajuan dinas berhasil dikirim', 201);
    }
}
