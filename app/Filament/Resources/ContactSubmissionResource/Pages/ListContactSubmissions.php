<?php

namespace App\Filament\Resources\ContactSubmissionResource\Pages;

use App\Filament\Resources\ContactSubmissionResource;
use App\Models\ContactSubmission;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ListContactSubmissions extends ListRecords
{
    protected static string $resource = ContactSubmissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export')
                ->label('تصدير CSV')
                ->icon('heroicon-m-arrow-down-tray')
                ->color('success')
                ->action(fn () => $this->exportCsv()),
        ];
    }

    private function exportCsv(): StreamedResponse
    {
        $filename = 'رسائل-التواصل-' . now()->format('Y-m-d') . '.csv';

        return response()->streamDownload(function () {
            $handle = fopen('php://output', 'w');
            // UTF-8 BOM so Excel reads Arabic correctly
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['الاسم', 'الإيميل', 'الهاتف', 'الموضوع', 'الرسالة', 'اللغة', 'مقروءة', 'التاريخ']);

            ContactSubmission::orderByDesc('created_at')->chunk(200, function ($rows) use ($handle) {
                foreach ($rows as $row) {
                    fputcsv($handle, [
                        $row->name,
                        $row->email,
                        $row->phone,
                        $row->subject,
                        $row->message,
                        $row->locale,
                        $row->is_read ? 'نعم' : 'لا',
                        $row->created_at?->format('Y-m-d H:i'),
                    ]);
                }
            });

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
