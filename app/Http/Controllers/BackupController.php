<?php

namespace App\Http\Controllers;

use App\Services\Backup\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Throwable;
use Illuminate\Support\Facades\Log;

class BackupController extends Controller
{
    public function export(BackupService $backupService): BinaryFileResponse|RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }

        try {
            $backupPath = $backupService->getDriver()->backup();

            return response()
                ->download($backupPath)
                ->deleteFileAfterSend(true);
        } catch (Throwable $exception) {
            Log::error("Backup Exception: " . $exception->getMessage());
            return redirect()
                ->route('settings.index')
                ->with('error', 'Gagal membuat backup database: ' . $exception->getMessage());
        }
    }

    public function import(Request $request, BackupService $backupService): RedirectResponse
    {
        if (auth()->user()->role !== 'admin') {
            return redirect()->route('profile.edit');
        }

        $validated = $request->validate([
            'backup_file' => ['required', 'file', 'max:51200'],
        ]);

        $extension = $request->file('backup_file')->getClientOriginalExtension();
        if (!in_array($extension, ['sql', 'sqlite', 'db'])) {
            return redirect()->route('settings.index')->with('error', 'Ekstensi file harus berupa .sql, .sqlite, atau .db');
        }

        try {
            $uploadedFile = $validated['backup_file'];
            $temporaryPath = $uploadedFile->store('backup-imports');
            $absoluteTemporaryPath = \Illuminate\Support\Facades\Storage::path($temporaryPath);

            // Buat backup cadangan dulu sebelum melakukan restore
            $backupService->getDriver()->backup();
            $restored = $backupService->getDriver()->restore($absoluteTemporaryPath);

            if (! $restored) {
                 Log::error("Restore process failed silently.");
                 return redirect()
                    ->route('settings.index')
                    ->with('error', 'Gagal mengimpor database.');
            }

            return redirect()
                ->route('settings.index')
                ->with('success', 'Database berhasil diimpor.');
        } catch (Throwable $exception) {
            Log::error("Restore Exception: " . $exception->getMessage());
            return redirect()
                ->route('settings.index')
                ->with('error', 'Gagal mengimpor database: ' . $exception->getMessage());
        }
    }
}
