<?php

declare(strict_types=1);

namespace App\Modules\Reports\Resources;

class ReportResource
{
    public static function dashboard(array $summary): array
    {
        if (isset($summary['recent_audit_logs']) && is_array($summary['recent_audit_logs'])) {
            $summary['recent_audit_logs'] = AuditLogResource::collection($summary['recent_audit_logs']);
        }

        if (isset($summary['recent_activity_logs']) && is_array($summary['recent_activity_logs'])) {
            $summary['recent_activity_logs'] = ActivityLogResource::collection($summary['recent_activity_logs']);
        }

        return $summary;
    }

    public static function report(array $report): array
    {
        return $report;
    }

    public static function export(array $export): array
    {
        return $export;
    }
}
