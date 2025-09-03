<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\GenericReportExport;
use Maatwebsite\Excel\Facades\Excel;

class ReportExportController extends Controller
{
    public function export(Request $request)
    {
        $data = $request->validate([
            'filename' => 'required|string',
            'title' => 'required|string',
            'headings' => 'required|array',
            'rows' => 'required|array',
            'formats' => 'sometimes|array',
        ]);

        $export = new GenericReportExport(
            $data['title'],
            $data['headings'],
            $data['rows'],
            $data['formats'] ?? []
        );

        return Excel::download($export, $data['filename'] . '.xlsx');
    }
    public function downloadExcel(array $headings, array $rows, string $filename, string $title, array $formats = [])
    {
        $export = new GenericReportExport($title, $headings, $rows, $formats);
        return Excel::download($export, $filename . '.xlsx');
    }
}
