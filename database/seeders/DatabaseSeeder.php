<?php

namespace Database\Seeders;

use App\Models\ActionItem;
use App\Models\Agenda;
use App\Models\AgendaTopic;
use App\Models\Comment;
use App\Models\Feature;
use App\Models\Meeting;
use App\Models\MomEntry;
use App\Models\Notification;
use App\Models\Room;
use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Roles table exists (even if the app doesn't currently model it).
        $roles = [
            [
                'name' => 'admin',
                'display_name' => 'Admin',
                'description' => 'Full access to manage users, rooms, and features.',
            ],
            [
                'name' => 'scheduler',
                'display_name' => 'Scheduler',
                'description' => 'Can create and manage their meetings and attendees.',
            ],
            [
                'name' => 'attendee',
                'display_name' => 'Attendee',
                'description' => 'Can join/leave meetings and participate.',
            ],
        ];

        foreach ($roles as $role) {
            DB::table('roles')->updateOrInsert(
                ['name' => $role['name']],
                $role + ['updated_at' => now(), 'created_at' => now()]
            );
        }

        // Create deterministic accounts (safe to re-run).
        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password',
                'is_admin' => true,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        $testUser = User::query()->updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => 'password',
                'is_admin' => false,
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // Extra users
        $users = User::factory()
            ->count(18)
            ->state(fn () => [
                // Helps avoid collisions across repeated seeding runs
                'email' => 'user+'.Str::uuid().'@example.com',
                'is_admin' => false,
                'is_active' => true,
            ])
            ->create();

        $allUsers = $users->concat([$admin, $testUser]);

        // Features (seed some known ones, then random ones)
        $knownFeatures = [
            ['name' => 'Projector', 'slug' => 'projector', 'description' => 'Ceiling-mounted projector'],
            ['name' => 'Whiteboard', 'slug' => 'whiteboard', 'description' => 'Large whiteboard'],
            ['name' => 'Video Conferencing', 'slug' => 'video-conferencing', 'description' => 'Camera + mic + display'],
            ['name' => 'Speakerphone', 'slug' => 'speakerphone', 'description' => 'Conference speakerphone'],
            ['name' => 'HDMI Input', 'slug' => 'hdmi-input', 'description' => 'HDMI input available'],
        ];

        foreach ($knownFeatures as $feature) {
            Feature::query()->updateOrCreate(
                ['slug' => $feature['slug']],
                ['name' => $feature['name'], 'description' => $feature['description']]
            );
        }

        Feature::factory()->count(8)->create();

        $features = Feature::all();

        // Rooms + attach features
        $rooms = Room::factory()->count(8)->create();
        foreach ($rooms as $room) {
            $room->features()->sync(
                $features->random(fake()->numberBetween(2, min(6, $features->count())))->pluck('id')->all()
            );
        }

        // Meetings + attendees + agenda/topics + mom/action items + comments/notifications
        $meetings = Meeting::factory()->count(20)->create();

        foreach ($meetings as $meeting) {
            // Ensure scheduler/room set (factory may have created new ones if empty)
            if (! $meeting->scheduled_by) {
                $meeting->scheduled_by = $allUsers->random()->id;
            }
            if (! $meeting->room_id) {
                $meeting->room_id = $rooms->random()->id;
            }
            $meeting->save();

            // Attendees (include scheduler)
            $attendeePool = $allUsers->shuffle();
            $attendeeCount = min(fake()->numberBetween(3, 7), $attendeePool->count());
            $attendees = $attendeePool->take($attendeeCount)->pluck('id')->unique()->values();
            if (! $attendees->contains($meeting->scheduled_by)) {
                $attendees->prepend($meeting->scheduled_by);
            }

            $attachPayload = [];
            foreach ($attendees as $userId) {
                $attachPayload[$userId] = [
                    'status' => fake()->randomElement(['invited', 'accepted', 'declined']),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            $meeting->attendees()->sync($attachPayload);

            // Agenda + topics
            $agenda = Agenda::factory()->create([
                'meeting_id' => $meeting->id,
                'title' => 'Agenda: '.Str::limit($meeting->title, 40, ''),
                'description' => fake()->boolean(60) ? fake()->sentence(14) : null,
            ]);

            $topicCount = fake()->numberBetween(3, 8);
            for ($order = 1; $order <= $topicCount; $order++) {
                AgendaTopic::factory()->create([
                    'agenda_id' => $agenda->id,
                    'owner_id' => $attendees->random(),
                    'order' => $order,
                ]);
            }

            // MoM entries + action items
            $momCount = fake()->numberBetween(1, 3);
            $momEntries = MomEntry::factory()->count($momCount)->create([
                'meeting_id' => $meeting->id,
                'title' => 'MoM: '.Str::limit($meeting->title, 40, ''),
            ]);

            foreach ($momEntries as $momEntry) {
                $actionCount = fake()->numberBetween(1, 6);
                ActionItem::factory()->count($actionCount)->create([
                    'mom_entry_id' => $momEntry->id,
                    'assigned_to' => fake()->boolean(75) ? $attendees->random() : null,
                ]);
            }

            // Comments
            $commentCount = fake()->numberBetween(2, 10);
            Comment::factory()->count($commentCount)->create([
                'meeting_id' => $meeting->id,
                'user_id' => $attendees->random(),
            ]);

            // Notifications for a subset of attendees
            $notifUsers = $attendees->shuffle()->take(fake()->numberBetween(1, min(4, $attendees->count())));
            foreach ($notifUsers as $userId) {
                Notification::factory()->create([
                    'user_id' => $userId,
                    'text' => 'Update on meeting: '.$meeting->title,
                    'link' => '/meetings/'.$meeting->id,
                ]);
            }
        }
    }
}
