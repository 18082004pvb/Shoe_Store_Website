<?php

namespace App\Console\Commands;

use App\Models\ChatConversation;
use Carbon\Carbon;
use Illuminate\Console\Command;

class CleanupOldChatbotConversations extends Command
{
    protected $signature = 'chatbot:cleanup {--days=30} {--keep=0}';
    protected $description = 'Xóa hội thoại chatbot cũ khỏi hệ thống';

    public function handle()
    {
        $days = (int)$this->option('days');
        $keep = (int)$this->option('keep');

        $query = ChatConversation::query()
            ->where('created_at', '<', Carbon::now()->subDays($days));

        if ($keep > 0) {
            $idsToKeep = ChatConversation::orderByDesc('id')
                ->limit($keep)
                ->pluck('id')
                ->toArray();

            if (!empty($idsToKeep)) {
                $query->whereNotIn('id', $idsToKeep);
            }
        }

        $count = $query->count();
        $query->delete();

        $this->info("Đã xóa {$count} hội thoại cũ.");
        return Command::SUCCESS;
    }
}