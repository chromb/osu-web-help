<?php

/**
 *    Copyright (c) ppy Pty Ltd <contact@ppy.sh>.
 *
 *    This file is part of osu!web. osu!web is distributed with the hope of
 *    attracting more community contributions to the core ecosystem of osu!.
 *
 *    osu!web is free software: you can redistribute it and/or modify
 *    it under the terms of the Affero GNU General Public License version 3
 *    as published by the Free Software Foundation.
 *
 *    osu!web is distributed WITHOUT ANY WARRANTY; without even the implied
 *    warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *    See the GNU Affero General Public License for more details.
 *
 *    You should have received a copy of the GNU Affero General Public License
 *    along with osu!web.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace App\Http\Controllers;

use App\Events\NotificationReadEvent;
use App\Libraries\MorphMap;
use App\Models\Notification;
use App\Models\UserNotification;
use Carbon\Carbon;
use DB;

/**
 * @group Notification
 */
class NotificationsController extends Controller
{
    const LIMIT = 51;
    const STACK_LIMIT = 5;

    protected $section = 'community';
    protected $actionPrefix = 'notifications_';

    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function endpoint()
    {
        return ['url' => $this->endpointUrl()];
    }

    /**
     * Get Notifications
     *
     * This endpoint returns a list of the user's unread notifications. Sorted descending by `id` with limit of 50.
     *
     * ---
     *
     * ### Response Format
     *
     * Returns an object containing [Notification](#notification) and other related attributes.
     *
     * Field                 | Type
     * --------------------- | ---------------------------------------------------
     * has_more              | boolean whether or not there are more notifications
     * notifications         | array of [Notification](#notification)
     * unread_count          | total unread notifications
     * notification_endpoint | url to connect to websocket server
     *
     * @authenticated
     *
     * @queryParam max_id Maximum `id` fetched. Can be used to load earlier notifications. Defaults to no limit (fetch latest notifications)
     *
     * @response {
     *   "has_more": true,
     *   "notifications": [
     *     {
     *       "id": 1,
     *       "name": "forum_topic_reply",
     *       "created_at": "2019-04-24T07:12:43+00:00",
     *       "object_type": "forum_topic",
     *       "object_id": 1,
     *       "source_user_id": 1,
     *       "is_read": false,
     *       "details": {
     *           "title": "A topic",
     *           "post_id": 2,
     *           "username": "User",
     *           "cover_url": "https://..."
     *       }
     *     }
     *   ],
     *   "unread_count": 100,
     *   "notification_endpoint": "wss://notify.ppy.sh"
     * }
     */
    public function unread()
    {
        $this->unread = true;
        $response = $this->index();
        $response['notification_endpoint'] = $this->endpointUrl();
        $response['unread_count'] = auth()->user()->userNotifications()->where('is_read', false)->count();

        return response($response)->header('Cache-Control', 'no-store');
    }

    public function index()
    {
        $unread = $this->unread ?? get_bool(request('unread'));
        $type = presence(request('type'));
        $objectId = get_int(presence(request('cursor.object_id')));
        $objectType = presence(request('cursor.object_type'));
        $name = presence(request('cursor.name'));
        $cursor = get_int(request('cursor.id'));

        if ($objectId && $objectType && $name) {
            [$stack, $total] = $this->getNotificationStack($objectType, $objectId, $name, $cursor, $unread);
            $stacks = $this->stackToResponse($stack, $total);

            return [
                'notifications' => json_collection($stack, 'Notification'),
                'stacks' => $stacks != null ? [$stacks] : [],
            ];
        }

        [$types, $stacks, $notifications] = $this->getNotificationsByType($type, $cursor, $unread);

        $bundleJson = [
            'notifications' => $notifications,
            'stacks' => $stacks,
            'types' => $types,
        ];

        if (is_json_request()) {
            return $bundleJson;
        }

        return view('notifications.index', compact('bundleJson'));
    }

    /**
     * Mark Notifications as Read
     *
     * This endpoint allows you to mark notifications read.
     *
     * ---
     *
     * ### Response Format
     *
     * _empty response_
     *
     * @authenticated
     *
     * @bodyParam ids integer[] required `id` of notifications to be marked as read  Example: [1, 2, 3]
     *
     * @response 204
     */
    public function markRead()
    {
        $user = auth()->user();
        $params = get_params(request()->all(), null, [
            'stack.name:string',
            'stack.object_id:int',
            'stack.object_type:string',
            'type:string',
            'ids:int[]'
        ]);

        $type = $params['type'] ?? null;
        $stack = $params['stack'] ?? [];
        $ids = get_params($params, null, ['ids:int[]'])['ids'] ?? [];

        if (present($type)) {;
            $itemsQuery = $user->userNotifications()->whereHas('notification', function ($query) use ($type) {
                $query->where('notifiable_type', $type);
            });
        } else if (!empty($stack)) {
            $itemsQuery = $user->userNotifications()->whereHas('notification', function ($query) use ($stack) {
                $query
                    ->where('notifiable_type', $stack['object_type'])
                    ->where('notifiable_id', $stack['object_id'])
                    ->where('name', $stack['name']);
            });
        } else {
            $itemsQuery = $user->userNotifications()->whereIn('notification_id', $ids);
        }

        if ($itemsQuery->update(['is_read' => true])) {
            event(new NotificationReadEvent($user->getKey(), $ids));

            return response(null, 204);
        } else {
            return response(null, 422);
        }
    }

    private function endpointUrl()
    {
        $url = config('osu.notification.endpoint');

        if (($url[0] ?? null) === '/') {
            $host = request()->getHttpHost();
            $protocol = request()->secure() ? 'wss' : 'ws';
            $url = "{$protocol}://{$host}{$url}";
        }

        return $url;
    }

    private function getTotalNotificationCount(string $type, ?bool $unread = false)
    {
        return Notification::whereHas('userNotifications', function ($q) use ($unread) {
            $q->where('user_id', auth()->user()->getKey());
            if ($unread) {
                $q->where('is_read', false);
            }
        })
        ->where('notifiable_type', $type)
        ->count();
    }

    private function getNotificationStack(string $objectType, int $objectId, string $name, ?int $cursor = null, ?bool $unread = false)
    {
        $stack = auth()->user()->userNotifications()->with('notification')->whereHas('notification', function ($q) use ($objectId, $objectType, $name) {
            $q->where('notifiable_id', $objectId)
                ->where('notifiable_type', $objectType)
                ->where('name', $name);
        });

        if ($unread) {
            $stack->where('is_read', false);
        }

        $total = $stack->count();

        $stack = $stack->orderBy('id', 'desc')->limit(static::STACK_LIMIT);

        if ($cursor !== null) {
            $stack->where('id', '<', $cursor);
        }

        return [$stack->get(), $total];
    }

    private function stackToResponse($stack, $total) {
        $last = $stack->last();
        if ($last === null) {
            return;
        }

        $last = $last instanceof UserNotification ? $last->notification : $last;
        $cursor = [
            'id' => $last->id,
            'object_type' => $last->notifiable_type,
            'object_id' => $last->notifiable_id,
            'name' => $last->name,
        ];

        return [
            'cursor' => $stack->count() < static::STACK_LIMIT ? null : $cursor,
            'name' => $last->name,
            'object_type' => $last->notifiable_type,
            'object_id' => $last->notifiable_id,
            'total' => $total,
        ];
    }

    private function getNotificationsByType(?string $type = null, ?int $cursor = null, ?bool $unread = false)
    {
        $types = [];
        $stacks = [];
        $notifications = collect();

        foreach (MorphMap::MAP as $_key => $value) {
            if ($type !== null && $type !== $value) {
                continue;
            }

            $typeCursor = null;

            $topLevel = Notification::whereHas('userNotifications', function ($q) use ($unread) {
                $q->where('user_id', auth()->user()->getKey());
                if ($unread) {
                    $q->where('is_read', false);
                }
            })
            ->where('notifiable_type', $value)
            ->groupBy('name', 'notifiable_id')
            ->orderBy('id', 'DESC')
            ->select(DB::raw('MAX(id) as id'), 'name', 'notifiable_id');

            if ($cursor !== null) {
                $topLevel->where('id', '<', $cursor);
            }

            $topLevel = $topLevel->limit(static::STACK_LIMIT)->get();

            $notificationStacks = $topLevel->map(function ($row) use ($cursor, $value, $unread) {
                // pass cursor in as all the notifications in the stack should be older.
                return $this->getNotificationStack($value, $row->notifiable_id, $row->name, $cursor, $unread);
            });

            foreach ($notificationStacks as [$stack, $total]) {
                // ordering means this gets overriden to the smallest value when not null.
                if ($stack->last() !== null) {
                    $typeCursor = [
                        'id' => $stack->last()->id,
                        'type' => $value,
                    ];
                }

                $response = $this->stackToResponse($stack, $total);
                if ($response !== null) {
                    $stacks[] = $response;
                }

                $notifications = $notifications->concat(json_collection($stack, 'Notification'));
            }

            $types[] = [
                'cursor' => $typeCursor,
                'name' => $value,
                'total' => $this->getTotalNotificationCount($value, $unread),
            ];
        }

        return [$types, $stacks, $notifications];
    }

    private function getUserNotifications($after = null)
    {
        $notifications = Notification::whereHas('userNotifications', function ($q) {
            $q->where('user_id', auth()->user()->getKey());
        })
        ->with('notifiable')
        ->with('source')
        ->orderBy('id', 'DESC')
        ->limit(10);

        if ($after !== null) {
            $notifications->where('id', '<', $after);
        }

        return $notifications->get();
    }
}
