<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\Copy;
use App\Models\Edition;
use App\Models\Loan;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::factory(5)->create()->each(function ($u) {
            $u->update(['api_token' => Str::random(60), 'role' => 'patron']);
        });

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'role' => 'patron',
            'api_token' => Str::random(60),
        ]);

        Book::factory(20)->create()->each(function ($book) {
            $author = Author::factory()->create();
            $edition = Edition::factory()->create([
                'book_id' => $book->id,
                'author_id' => $author->id,
            ]);

            Copy::factory(3)->create([
                'edition_id' => $edition->id,
            ]);
        });

        Loan::factory(10)->create([
            'user_id' => User::where('email', 'test@example.com')->value('id'),
            'copy_id' => Copy::inRandomOrder()->first()->id,
        ]);
    }
}
