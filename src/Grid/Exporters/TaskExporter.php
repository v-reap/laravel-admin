<?php

namespace Encore\Admin\Grid\Exporters;

use Encore\Admin\Facades\Admin;
use Encore\Admin\Models\Task\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class TaskExporter extends AbstractExporter
{
    /**
     * {@inheritdoc}
     */
    public function export($encode='UTF-8')
    {
        $filename = $this->getTable().'.csv';

        $headers = [
            'Expires'             => '0',
            'Cache-control'       => 'private',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Description' => 'File Transfer',
            'Content-Encoding'    => $encode,
            'Content-Type'        => 'text/csv;charset='.$encode,
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        response()->stream(function () {
            $handle = fopen('php://output', 'w');

            $titles = [];
            $this->chunk(function ($records) use ($handle, &$titles) {
                if (empty($titles)) {
                    $titles = $this->getHeaderRowFromRecords($records);

                    // Add CSV headers
                    fputcsv($handle, $titles);
                }

                foreach ($records as $record) {
                    fputcsv($handle, $this->getFormattedRecord($record));
                }
            });

            // Close the output stream
            fclose($handle);
        }, 200, $headers)->send();

        exit;
    }

    /**
     * @param Collection $records
     *
     * @return array
     */
    public function getHeaderRowFromRecords(Collection $records)
    {
        $titles = [];
        foreach ($records->first()->toArray() as $key=>$item) {
            if (is_array($item)){
                continue;
            }
            if (substr($key,0,4)=='attr'){
                $attr = Attribute::find(substr($key,4));
                $attrValue = $attr ? $attr->frontend_label : '';
                $titles[]=$this->convTo($attrValue);
            }else{
                $titles[]=$this->convTo(trans('task.'.$key));
            }
        };
        return $titles;
    }

    /**
     * @param Model $record
     *
     * @return array
     */
    public function getFormattedRecord(Model $record)
    {
        $datas = [];
        foreach ($record->getAttributes() as $key=>$attribute) {
            if ($key=='user_id'){
                $datas[$key]=$this->convTo($record->user->name);
            } elseif($key=='status_id') {
                $datas[$key]=$this->convTo($record->status->name);
            } elseif($key=='type_id') {
                $datas[$key]=$this->convTo($record->type->name);
            } else{
                $datas[$key]=$this->convTo($attribute);
            }
        }
        return $datas;
    }

    public function convTo($value, $from = 'UTF-8', $to = 'GB2312//IGNORE')
    {
        return iconv($from, $to, $value);
    }
}
