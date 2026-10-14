<?php

namespace App\Http\Controllers\Web\Admin\SystemLog;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Inertia\Inertia;

class SystemLogController extends Controller
{
    private $logPath;

    public function __construct()
    {
        $this->logPath = storage_path('logs/laravel.log');
    }

    public function index(Request $request)
    {
        $logs = $this->parseLogs();

        $perPage = $request->input('per_page', 10);
        $page = $request->input('page', 1);
        $search = $request->input('search', '');

        // Filter by search
        if (! empty($search)) {
            $logs = array_filter($logs, function ($log) use ($search) {
                return stripos($log['message'], $search) !== false ||
                       stripos($log['env'], $search) !== false ||
                       stripos($log['level'], $search) !== false;
            });
            // Re-index array after filter
            $logs = array_values($logs);
        }

        $total = count($logs);
        $offset = ($page - 1) * $perPage;

        $paginatedLogs = array_slice($logs, $offset, $perPage);

        return Inertia::render('system-log/index', [
            'logs' => [
                'data' => $paginatedLogs,
                'current_page' => (int) $page,
                'last_page' => max(1, ceil($total / $perPage)),
                'per_page' => (int) $perPage,
                'total' => $total,
            ],
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $this->deleteLogs([$id]);

        return redirect()->back()->with('success', 'Log deleted successfully!');
    }

    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
        ]);

        $this->deleteLogs($request->ids);

        return redirect()->back()->with('success', 'Logs deleted successfully!');
    }

    public function clearAll(Request $request)
    {
        if (File::exists($this->logPath)) {
            File::put($this->logPath, '');
        }

        return redirect()->back()->with('success', 'All logs cleared successfully!');
    }

    private function parseLogs()
    {
        if (! File::exists($this->logPath)) {
            return [];
        }

        $content = File::get($this->logPath);
        $logEntries = [];

        // Regex to match log entries. Matches: [YYYY-MM-DD HH:MM:SS] ENV.LEVEL: Message
        preg_match_all("/^\[(?P<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<env>[a-zA-Z0-9_-]+)\.(?P<level>[a-zA-Z0-9_-]+): (?P<message>.*?)(?=(^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\z))/ms", $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $datetime = $match['datetime'];
            $env = $match['env'];
            $level = $match['level'];
            $message = trim($match['message']);
            // Generate unique ID based on content
            $id = md5($datetime.$env.$level.$message);

            $logEntries[] = [
                'id' => $id,
                'datetime' => $datetime,
                'env' => $env,
                'level' => $level,
                'message' => $message,
                'short_message' => mb_strimwidth($message, 0, 100, '...'),
            ];
        }

        // Reverse to show newest first
        return array_reverse($logEntries);
    }

    private function deleteLogs(array $idsToDelete)
    {
        if (! File::exists($this->logPath)) {
            return;
        }

        $content = File::get($this->logPath);
        $newContent = '';

        $pattern = "/(^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\] [a-zA-Z0-9_-]+\.[a-zA-Z0-9_-]+: .*?(?=(^\[\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}\]|\z)))/ms";

        preg_match_all($pattern, $content, $matches, PREG_SET_ORDER);

        foreach ($matches as $match) {
            $fullLogBlock = $match[0];
            if (preg_match("/^\[(?P<datetime>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?P<env>[a-zA-Z0-9_-]+)\.(?P<level>[a-zA-Z0-9_-]+): (?P<message>.*)/ms", $fullLogBlock, $parts)) {
                $datetime = $parts['datetime'];
                $env = $parts['env'];
                $level = $parts['level'];
                $message = trim($parts['message']);
                $id = md5($datetime.$env.$level.$message);

                if (! in_array($id, $idsToDelete)) {
                    $newContent .= $fullLogBlock;
                }
            } else {
                $newContent .= $fullLogBlock;
            }
        }

        File::put($this->logPath, $newContent);
    }
}
