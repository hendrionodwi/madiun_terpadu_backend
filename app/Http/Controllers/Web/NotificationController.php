<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;

class NotificationController extends Controller
{
    public function index(Request $request): View
    {
        /** @var User $user */
        $user = $request->user();
        $view = $user->isWarga() ? 'notifications.index-public' : 'notifications.index-dashboard';

        return view($view, [
            'notifications' => $user->notifications()->paginate(20),
            'unreadCount' => $user->unreadNotifications()->count(),
        ]);
    }

    public function markAsRead(Request $request, string $notification): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        /** @var DatabaseNotification $databaseNotification */
        $databaseNotification = $user->notifications()->whereKey($notification)->firstOrFail();
        $databaseNotification->markAsRead();

        if ($request->boolean('open')) {
            return redirect()->to($this->actionUrl($databaseNotification));
        }

        return back()->with('success', 'Notifikasi ditandai sudah dibaca.');
    }

    public function markAllAsRead(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $user->unreadNotifications()->update(['read_at' => now()]);

        return back()->with('success', 'Semua notifikasi ditandai sudah dibaca.');
    }

    private function actionUrl(DatabaseNotification $notification): string
    {
        $orderNumber = (string) ($notification->data['order_number'] ?? '');
        if ($orderNumber === '') {
            return route('notifications.index');
        }

        return ($notification->data['audience'] ?? 'buyer') === 'seller'
            ? route('dashboard.tps3r.orders.show', ['order' => $orderNumber])
            : route('marketplace.orders.show', ['order' => $orderNumber]);
    }
}
