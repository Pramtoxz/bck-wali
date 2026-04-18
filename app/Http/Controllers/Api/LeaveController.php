<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Helpers\ImageCompressor;
use App\Http\Controllers\Controller;
use App\Models\Leave;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $limit = $request->query('limit', 3);

        $leaves = Leave::where('user_id', $request->user()->id)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->map(function ($leave) {
                return [
                    'id' => $leave->id,
                    'date' => Carbon::parse($leave->created_at)->locale('id')->isoFormat('DD MMM YYYY'),
                    'start_date' => $leave->start_date->format('Y-m-d'),
                    'end_date' => $leave->end_date->format('Y-m-d'),
                    'total_days' => $leave->total_days,
                    'type' => $leave->type,
                    'reason' => $leave->reason,
                    'status' => $leave->status,
                    'submitted_at' => $leave->created_at->toIso8601String(),
                ];
            });

        return ApiResponse::success($leaves);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'type' => 'required|in:sakit,izin,cuti',
            'reason' => 'required|string',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        $documentPath = null;
        if ($request->hasFile('document')) {
            $document = $request->file('document');
            
            if (str_starts_with($document->getMimeType(), 'image/')) {
                $compressor = new ImageCompressor(maxSizeMB: 1.9);
                $document = $compressor->compress($document);
            }
            
            $documentPath = $document->store('documents/leave', 'public');
        }

        $startDate = Carbon::parse($validated['start_date']);
        $endDate = Carbon::parse($validated['end_date']);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        $leave = Leave::create([
            'user_id' => $request->user()->id,
            'start_date' => $validated['start_date'],
            'end_date' => $validated['end_date'],
            'total_days' => $totalDays,
            'type' => $validated['type'],
            'reason' => $validated['reason'],
            'document_path' => $documentPath,
            'status' => 'pending',
        ]);

        return ApiResponse::success([
            'id' => $leave->id,
            'start_date' => $leave->start_date->format('Y-m-d'),
            'end_date' => $leave->end_date->format('Y-m-d'),
            'total_days' => $leave->total_days,
            'type' => $leave->type,
            'reason' => $leave->reason,
            'document_url' => $documentPath ? asset('storage/' . $documentPath) : null,
            'status' => $leave->status,
            'submitted_at' => $leave->created_at->toIso8601String(),
        ], 'Pengajuan izin/cuti berhasil dikirim', 201);
    }
}
