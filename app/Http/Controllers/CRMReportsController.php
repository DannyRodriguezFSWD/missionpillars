<?php

namespace App\Http\Controllers;

use App\Classes\Shared\Reports\Givers;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
// TODO update code to version 3 and
// - uncomment the next two lines
// - implment With Events and uncomment and revise registerEvents
// use Maatwebsite\Excel\Concerns\WithEvents;
// use Maatwebsite\Excel\Events\AfterSheet;

use function GuzzleHttp\json_encode;

use App\Constants;

class CRMReportsController extends Controller 
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            if (!auth()->user()->can('reports-view')) abort(403);
            return $next($request);
        });
    }

    public function index()
    {
        $data = [
            'in_tags' => '',
            'out_tags' => '',
            'amount_ranges' => json_encode([]),
            'list' => 0
        ];
        return view('reports.crm.index', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $data = Constants::REPORTS;
        return response()->json($data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id, Request $request)
    {
        if($request->ajax()){
            return response()->json(true);
        }
        $filename = substr(implode('_', [
            str_replace('/', '-', array_get($request, 'from')), 
            str_replace('/', '-', array_get($request, 'to'))
        ]), 0, 28);

        $orientation = 'portrait';
        $givers = new Givers();
        switch ($id) {
            case 0://new givers
                $result = $givers->newGivers($request);
                break;
            case 1:
                $result = $givers->latentGivers($request);
                break;
            case 2:
                $result = $givers->giversStatistics($request);
                break;
            case 3:
                $orientation = 'landscape';
                $result = $givers->purposesStatistics($request);
                break;
            case 4:
                $orientation = 'landscape';
                $result = $givers->campaignsStatistics($request);
                break;
            default:
                $result = $result = [];
                break;
        }

        $sum = null;
        if($id == 3){
            $sum = array_get($result, 'sum');
            $result = array_get($result, 'purposes', []);
        }
        
        if($id == 4){
            $sum = array_get($result, 'sum');
            $result = array_get($result, 'campaigns', []);
        }
        
        $data = [
            'report' => array_get(Constants::REPORTS, $id),
            'givers' => $result,
            'from' => array_get($request, 'from'),
            'to' => array_get($request, 'to'),
            'from2' => array_get($request, 'from2'),
            'to2' => array_get($request, 'to2'),
            'filename' => $filename,
            'format' => array_get($request, 'format'),
            'sum' => $sum,
            'amount_ranges' => array_get($request, 'amount_ranges'),
            'in_tags' => array_get($request, 'in_tags'),
            'out_tags' => array_get($request, 'out_tags'),
            'list' => array_get($request, 'list', 0)
        ];
        
        if(in_array($id, [0, 1, 2, 3, 4])){
            if(is_null(array_get($request, 'download'))){
                return view('reports.crm.show', $data);
            }
            else if(array_get($request, 'format') == 'excel'){
                Excel::create($filename, function($excel) use ($data, $orientation) {
                    $excel->sheet('Sheetname' . time(), function($sheet) use ($data, $orientation) {
                        $sheet->setOrientation($orientation);
                        $sheet->loadView('reports.crm.includes.export', $data);
                    });
                })->download('xlsx');
            }
            else if(array_get($request, 'format') == 'pdf'){
                $filename .= '.pdf';
                $pdf = PDF::loadView('reports.crm.includes.export', $data)->setPaper('letter', $orientation);;
                return $pdf->download($filename);
            }
        }
    }
    
    /**
     * Registers the AfterSheet Event for Excel, so that any column widths that are missing ore lower than threshold get set to 8.43 (seems to be the default column width in Excel)
     * This will not be in use unless Excel class is updated to version >= 3 from vendero
     * @return array 
     */
    // public function registerEvents(): array
    // {
    //     return [
    //         AfterSheet::class => function (AfterSheet $event) {
    //             foreach (range('A','Z') as $letter) {
    //                 $column = $event->sheet->getDelegate()->getColumDimension($letter);
    //                 if (!$column->getWidth() || $column->getWidth() < 1) {
    //                     $column->setWidth(8.43);
    //                 }
    //             }
    //         }
    //     ];
    // }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
