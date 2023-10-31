<?php

namespace App\Models;
use Nette;

final class ReportModel
{
    public function __construct(private Nette\Database\Explorer $database) {}

    public function getReport(int $id): ?Nette\Database\Table\ActiveRow
    {
        return $this->database->table('user_reports')->where('id', $id)->fetch();
    }

    public function getPaginatedReports(int $offset, int $limit)
    {
        $query = $this->database->query("
            SELECT *
            FROM user_reports
            ORDER BY report_time DESC
            LIMIT ? OFFSET ?", $limit, $offset);

        return $query->fetchAll();
    }

    public function getTotalCount()
    {
        return $this->database->table('user_reports')->count('id');
    }


}