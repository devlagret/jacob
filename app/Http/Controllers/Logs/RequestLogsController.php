<?php

namespace App\Http\Controllers\Logs;

use App\Http\Controllers\Controller;
use App\DataTables\RequestLogsDataTable;
use Illuminate\Http\Request;

class RequestLogController extends Controller
{
    public function index(RequestLogsDataTable $dataTable)
    {
        return 'foo';
        return $dataTable->render('pages.log.request.index');
    }
}
